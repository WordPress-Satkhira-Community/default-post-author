<?php
/**
 * Admin functionality for Default Post Author.
 *
 * @package DefaultPostAuthor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DPAP Admin Class.
 */
class DPAP_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'wp_ajax_dpap_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_dpap_update_posts', array( $this, 'ajax_update_posts' ) );
		add_action( 'wp_ajax_dpap_get_post_count', array( $this, 'ajax_get_post_count' ) );
	}

	/**
	 * Add settings page under Settings menu.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Default Post Author', 'default-post-author' ),
			__( 'Default Post Author', 'default-post-author' ),
			'manage_options',
			'default-post-author',
			array( $this, 'render_options_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( 'settings_page_default-post-author' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'dpap-admin-style',
			DPAP_PLUGIN_URL . 'assets/css/admin-style.css',
			array(),
			DPAP_VERSION
		);

		// Needed for the "Install Plugin" thickbox modal.
		add_thickbox();
		wp_enqueue_script( 'plugin-install' );

		wp_enqueue_script(
			'dpap-admin-script',
			DPAP_PLUGIN_URL . 'assets/js/admin-script.js',
			array( 'jquery', 'plugin-install', 'thickbox' ),
			DPAP_VERSION,
			true
		);

		wp_localize_script(
			'dpap-admin-script',
			'dpapAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'dpap_admin_nonce' ),
				'strings' => array(
					'saving'        => __( 'Saving...', 'default-post-author' ),
					'saved'         => __( 'Settings saved successfully!', 'default-post-author' ),
					'error'         => __( 'An error occurred. Please try again.', 'default-post-author' ),
					'updating'      => __( 'Updating posts...', 'default-post-author' ),
					'updated'       => __( 'posts updated successfully!', 'default-post-author' ),
					'confirm'       => __( 'Are you sure you want to update existing posts? This action cannot be undone.', 'default-post-author' ),
					'dry_run_info'  => __( 'Dry-run complete. No posts were changed.', 'default-post-author' ),
					'batch_done'    => __( 'All batches completed.', 'default-post-author' ),
					'no_posts'      => __( 'No posts matched the selected criteria.', 'default-post-author' ),
				),
			)
		);
	}

	/**
	 * Render the options page.
	 *
	 * @return void
	 */
	public function render_options_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include DPAP_PLUGIN_DIR . 'templates/admin-page.php';
	}

	/**
	 * Show admin notices.
	 *
	 * @return void
	 */
	public function admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$message = get_transient( 'dpap_update_result' );
		if ( $message ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
			delete_transient( 'dpap_update_result' );
		}
	}

	/**
	 * AJAX: Save settings.
	 *
	 * @return void
	 */
	public function ajax_save_settings() {
		check_ajax_referer( 'dpap_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'default-post-author' ) ) );
		}

		$enabled_post_types = array();
		if ( isset( $_POST['enabled_post_types'] ) && is_array( $_POST['enabled_post_types'] ) ) {
			$enabled_post_types = array_map( 'sanitize_key', wp_unslash( $_POST['enabled_post_types'] ) );
		}
		if ( empty( $enabled_post_types ) ) {
			$enabled_post_types = array( 'post' );
		}

		$dpap_options = array(
			'default_author'        => isset( $_POST['default_author'] ) ? absint( $_POST['default_author'] ) : 1,
			'enable_default_author' => isset( $_POST['enable_default_author'] ) && 'true' === $_POST['enable_default_author'],
			'sync_revisions'        => isset( $_POST['sync_revisions'] ) && 'true' === $_POST['sync_revisions'],
			'force_only_capable'    => isset( $_POST['force_only_capable'] ) && 'true' === $_POST['force_only_capable'],
			'enabled_post_types'    => $enabled_post_types,
		);

		update_option( DPAP_OPTIONS, $dpap_options );

		wp_send_json_success( array( 'message' => __( 'Settings saved successfully!', 'default-post-author' ) ) );
	}

	/**
	 * AJAX: Get total matching posts (for progress bar).
	 *
	 * @return void
	 */
	public function ajax_get_post_count() {
		check_ajax_referer( 'dpap_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'default-post-author' ) ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified above.
		$status = isset( $_POST['post_status'] ) ? sanitize_key( wp_unslash( $_POST['post_status'] ) ) : 'any';

		$args                   = $this->build_bulk_query_args( $status );
		$args['posts_per_page'] = 1;
		$args['fields']         = 'ids';

		$query = new WP_Query( $args );

		wp_send_json_success(
			array(
				'total' => (int) $query->found_posts,
			)
		);
	}

	/**
	 * AJAX: Update existing posts (real batch processing).
	 *
	 * @return void
	 */
	public function ajax_update_posts() {
		check_ajax_referer( 'dpap_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'default-post-author' ) ) );
		}

		$default_author_id = (int) dpap_get_option( 'default_author', 1 );
		$user              = get_userdata( $default_author_id );

		if ( ! $user || ! array_intersect( array( 'author', 'editor', 'administrator' ), (array) $user->roles ) ) {
			wp_send_json_error( array( 'message' => __( 'Selected default author is invalid.', 'default-post-author' ) ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified at the start of this method.
		$paged      = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified at the start of this method.
		$dry_run    = isset( $_POST['dry_run'] ) && 'true' === $_POST['dry_run'];
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified at the start of this method.
		$status     = isset( $_POST['post_status'] ) ? sanitize_key( wp_unslash( $_POST['post_status'] ) ) : 'any';
		$batch_size = 50;

		$args                   = $this->build_bulk_query_args( $status );
		$args['posts_per_page'] = $batch_size;
		$args['paged']          = $paged;
		$args['fields']         = 'ids';

		/**
		 * Filter the WP_Query arguments used for bulk updates.
		 *
		 * @param array $args Query arguments.
		 */
		$args = apply_filters( 'dpap_bulk_update_args', $args );

		$query = new WP_Query( $args );

		$updated = 0;
		$skipped = 0;

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post_id ) {
				$status = get_post_status( $post_id );
				if ( 'auto-draft' === $status ) {
					++$skipped;
					continue;
				}

				$current_author = (int) get_post_field( 'post_author', $post_id );
				if ( $current_author === $default_author_id ) {
					++$skipped;
					continue;
				}

				if ( ! $dry_run ) {
					$result = wp_update_post(
						array(
							'ID'          => $post_id,
							'post_author' => $default_author_id,
						),
						true
					);

					if ( ! is_wp_error( $result ) ) {
						++$updated;
					} else {
						++$skipped;
					}
				} else {
					++$updated; // Count as would-be-updated in dry-run.
				}
			}
		}

		$has_more = $paged < $query->max_num_pages;

		wp_send_json_success(
			array(
				'updated'   => $updated,
				'skipped'   => $skipped,
				'paged'     => $paged,
				'has_more'  => $has_more,
				'max_pages' => (int) $query->max_num_pages,
				'dry_run'   => $dry_run,
				'message'   => $dry_run
					? __( 'Dry-run batch processed.', 'default-post-author' )
					: __( 'Batch processed.', 'default-post-author' ),
			)
		);
	}

	/**
	 * Build common query args for bulk operations.
	 *
	 * @param string $status Optional post status filter. Defaults to 'any'.
	 * @return array
	 */
	private function build_bulk_query_args( $status = 'any' ) {
		$post_types = dpap_get_enabled_post_types();

		$allowed_statuses = array( 'any', 'publish', 'draft', 'pending', 'private', 'future' );
		$status           = sanitize_key( $status );
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			$status = 'any';
		}

		return array(
			'post_type'      => $post_types,
			'post_status'    => $status,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'no_found_rows'  => false,
		);
	}

	/**
	 * Get eligible users for author selection.
	 *
	 * @return array Array of WP_User objects.
	 */
	public static function get_eligible_users() {
		$query = new WP_User_Query(
			array(
				'role__in' => array( 'author', 'editor', 'administrator' ),
				'orderby'  => 'display_name',
				'order'    => 'ASC',
			)
		);

		$users = $query->get_results();

		/**
		 * Filter the list of users shown in the default-author dropdown.
		 *
		 * @param array $users Array of WP_User objects.
		 */
		return apply_filters( 'dpap_eligible_users', $users );
	}

	/**
	 * Get public post types that support the author feature.
	 *
	 * @return array
	 */
	public static function get_supported_post_types() {
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		$supported = array();
		foreach ( $post_types as $pt ) {
			if ( post_type_supports( $pt->name, 'author' ) ) {
				$supported[ $pt->name ] = $pt->labels->singular_name;
			}
		}

		return $supported;
	}

	/**
	 * Render an Install / Activate / Active button for a recommended plugin.
	 *
	 * @param string $slug Plugin slug (directory name on wordpress.org).
	 * @return string HTML for the button.
	 */
	public static function render_plugin_action_button( $slug ) {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return '<a href="' . esc_url( 'https://wordpress.org/plugins/' . $slug . '/' ) . '" class="dpap-btn dpap-btn-sm" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View Plugin', 'default-post-author' ) . '</a>';
		}

		$plugin_file = self::get_plugin_file_by_slug( $slug );

		// Already active.
		if ( $plugin_file && is_plugin_active( $plugin_file ) ) {
			return '<span class="dpap-btn dpap-btn-sm dpap-btn-active">' . esc_html__( 'Active', 'default-post-author' ) . '</span>';
		}

		// Installed but not active → Activate.
		if ( $plugin_file ) {
			$activate_url = wp_nonce_url(
				self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin_file ) ),
				'activate-plugin_' . $plugin_file
			);
			return '<a href="' . esc_url( $activate_url ) . '" class="dpap-btn dpap-btn-sm dpap-btn-activate">' . esc_html__( 'Activate', 'default-post-author' ) . '</a>';
		}

		// Not installed → Install via WordPress installer (thickbox / plugin-install).
		$install_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . rawurlencode( $slug ) . '&TB_iframe=true&width=600&height=550' );
		return '<a href="' . esc_url( $install_url ) . '" class="dpap-btn dpap-btn-sm thickbox open-plugin-details-modal" data-title="' . esc_attr( $slug ) . '">' . esc_html__( 'Install Plugin', 'default-post-author' ) . '</a>';
	}

	/**
	 * Try to find the main plugin file for a given slug.
	 *
	 * @param string $slug Plugin slug.
	 * @return string|false Plugin basename or false.
	 */
	private static function get_plugin_file_by_slug( $slug ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		foreach ( $plugins as $file => $data ) {
			if ( strpos( $file, $slug . '/' ) === 0 || $file === $slug . '.php' ) {
				return $file;
			}
		}
		return false;
	}
}
