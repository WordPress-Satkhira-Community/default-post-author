<?php
/**
 * Plugin Name: Default Post Author
 * Plugin URI:  https://wordpress.org/plugins/default-post-author/
 * Description: The easiest way to set default post author in your WordPress Site.
 * Version:     2.2
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
define( 'DPAP_VERSION', '2.2' );
define( 'DPAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DPAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DPAP_OPTIONS', 'dpap_options' );

// Include admin class.
require_once DPAP_PLUGIN_DIR . 'includes/class-dpap-admin.php';

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
 * Set default author for new posts during creation only.
 *
 * @param array $data    Array of slashed post data.
 * @param array $postarr Original post data.
 * @return array Modified post data.
 */
function dpap_force_post_author( $data, $postarr ) {
    // Check if feature is enabled.
    if ( ! dpap_get_option( 'enable_default_author', true ) ) {
        return $data;
    }

    $default_author_id = (int) dpap_get_option( 'default_author', 1 );

    // Only for new posts (not updates).
    if ( empty( $postarr['ID'] ) && 'post' === $data['post_type'] ) {
        $data['post_author'] = $default_author_id;
    }

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
    // Check if feature is enabled.
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

    if ( is_wp_error( $result ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( 'dpap: Failed to update revision author for post ID ' . $post->ID . ': ' . $result->get_error_message() );
        }
    }
}
add_action( 'wp_save_post_revision', 'dpap_set_revision_author', 10, 2 );

/**
 * Redirect to settings page on activation.
 *
 * @param string $plugin Plugin basename.
 * @return void
 */
function dpap_activation_redirect( $plugin ) {
    if ( plugin_basename( __FILE__ ) === $plugin ) {
        // Set default options on first activation.
        if ( ! get_option( DPAP_OPTIONS ) ) {
            update_option(
                DPAP_OPTIONS,
                array(
                    'default_author'        => 1,
                    'enable_default_author' => true,
                    'sync_revisions'        => true,
                )
            );
        }
        wp_safe_redirect( admin_url( 'options-general.php?page=default-post-author' ) );
        exit;
    }
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