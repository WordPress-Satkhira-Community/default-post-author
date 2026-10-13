<?php
/**
 * Plugin Name: Default Post Author
 * Plugin URI:  https://wordpress.org/plugins/default-post-author/
 * Description: The easiest way to set a default post author in your WordPress site. Supports multiple post types, capability-aware assignment, bulk updates and more.
 * Version:     2.3.0
 * Requires at least: 6.1
 * Requires PHP: 8.0
 * Author:      MonarchWP
 * Author URI:  https://www.monarchwp.com
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: default-post-author
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'DPAP_VERSION', '2.3.0' );
define( 'DPAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DPAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DPAP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'DPAP_OPTIONS', 'dpap_options' );

// Include admin class.
require_once DPAP_PLUGIN_DIR . 'includes/class-dpap-admin.php';

// Include WP-CLI commands if available.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once DPAP_PLUGIN_DIR . 'includes/class-dpap-cli.php';
}

/**
 * Initialize admin.
 *
 * @return void
 */
function dpap_init() {
	if ( is_admin() ) {
		new DPAP_Admin();
	}
}
add_action( 'plugins_loaded', 'dpap_init' );

/**
 * Get plugin option.
 *
 * @param string $key     Option key.
 * @param mixed  $default Default value.
 * @return mixed Option value.
 */
function dpap_get_option( $key, $default = '' ) {
	$options = get_option( DPAP_OPTIONS, array() );
	return isset( $options[ $key ] ) ? $options[ $key ] : $default;
}

/**
 * Get the list of post types the plugin should act on.
 *
 * @return array
 */
function dpap_get_enabled_post_types() {
	$enabled = dpap_get_option( 'enabled_post_types', array( 'post' ) );

	if ( ! is_array( $enabled ) || empty( $enabled ) ) {
		$enabled = array( 'post' );
	}

	/**
	 * Filter the post types that receive the default author.
	 *
	 * @param array $enabled List of post type names.
	 */
	return apply_filters( 'dpap_enabled_post_types', $enabled );
}

/**
 * Check whether the current user is allowed to force a different author.
 *
 * @return bool
 */
function dpap_current_user_can_force_author() {
	$force_only_capable = (bool) dpap_get_option( 'force_only_capable', true );

	if ( ! $force_only_capable ) {
		return true; // Always force (old behaviour).
	}

	return current_user_can( 'edit_others_posts' );
}

/**
 * Set default author for new posts during creation.
 *
 * @param array $data    Array of slashed post data.
 * @param array $postarr Original post data.
 * @return array Modified post data.
 */
function dpap_force_post_author( $data, $postarr ) {
	// Feature disabled?
	if ( ! dpap_get_option( 'enable_default_author', true ) ) {
		return $data;
	}

	// Capability check (fixes Author-role permission error).
	if ( ! dpap_current_user_can_force_author() ) {
		return $data;
	}

	$post_type = isset( $data['post_type'] ) ? $data['post_type'] : 'post';

	// Only act on enabled post types.
	if ( ! in_array( $post_type, dpap_get_enabled_post_types(), true ) ) {
		return $data;
	}

	// Only for brand-new posts (not updates).
	if ( ! empty( $postarr['ID'] ) ) {
		return $data;
	}

	$default_author_id = (int) dpap_get_option( 'default_author', 1 );

	/**
	 * Filter the default author ID before it is applied.
	 *
	 * @param int   $default_author_id Author user ID.
	 * @param array $data              Post data.
	 * @param array $postarr           Original postarr.
	 */
	$default_author_id = (int) apply_filters( 'dpap_before_set_author', $default_author_id, $data, $postarr );

	if ( $default_author_id > 0 ) {
		$data['post_author'] = $default_author_id;
	}

	/**
	 * Fires after the default author has been set on a new post.
	 *
	 * @param int   $default_author_id Author user ID.
	 * @param array $data              Modified post data.
	 */
	do_action( 'dpap_after_set_author', $default_author_id, $data );

	return $data;
}
add_filter( 'wp_insert_post_data', 'dpap_force_post_author', 10, 2 );

/**
 * Ensure revision author matches original post author.
 *
 * @param int     $revision_id Revision post ID.
 * @param WP_Post $post        The original post object.
 * @return void
 */
function dpap_set_revision_author( $revision_id, $post ) {
	if ( ! dpap_get_option( 'sync_revisions', true ) ) {
		return;
	}

	$revision_id = (int) $revision_id;
	$post_author = (int) $post->post_author;

	$result = wp_update_post(
		array(
			'ID'          => $revision_id,
			'post_author' => $post_author,
		),
		true
	);

	if ( is_wp_error( $result ) && defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'dpap: Failed to update revision author for post ID ' . $post->ID . ': ' . $result->get_error_message() );
	}
}
add_action( 'wp_save_post_revision', 'dpap_set_revision_author', 10, 2 );

/**
 * Redirect to settings page on activation + set defaults.
 *
 * @param string $plugin Plugin basename.
 * @return void
 */
function dpap_activation_redirect( $plugin ) {
	if ( plugin_basename( __FILE__ ) !== $plugin ) {
		return;
	}

	// Set default options on first activation / upgrade.
	$existing = get_option( DPAP_OPTIONS );
	if ( false === $existing ) {
		update_option(
			DPAP_OPTIONS,
			array(
				'default_author'        => 1,
				'enable_default_author' => true,
				'sync_revisions'        => true,
				'force_only_capable'    => true, // New in 2.3.0 – fixes Author role issue.
				'enabled_post_types'    => array( 'post' ),
			)
		);
	} else {
		// Soft migration for existing installs.
		$options = (array) $existing;
		if ( ! isset( $options['force_only_capable'] ) ) {
			$options['force_only_capable'] = true;
		}
		if ( ! isset( $options['enabled_post_types'] ) ) {
			$options['enabled_post_types'] = array( 'post' );
		}
		update_option( DPAP_OPTIONS, $options );
	}

	wp_safe_redirect( admin_url( 'options-general.php?page=default-post-author' ) );
	exit;
}
add_action( 'activated_plugin', 'dpap_activation_redirect' );

/**
 * Uninstall cleanup.
 *
 * @return void
 */
function dpap_uninstall() {
	delete_option( DPAP_OPTIONS );
}
register_uninstall_hook( __FILE__, 'dpap_uninstall' );
