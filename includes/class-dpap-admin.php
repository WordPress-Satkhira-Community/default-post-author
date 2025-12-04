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

        wp_enqueue_script(
            'dpap-admin-script',
            DPAP_PLUGIN_URL . 'assets/js/admin-script.js',
            array( 'jquery' ),
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
                    'saving'   => __( 'Saving...', 'default-post-author' ),
                    'saved'    => __( 'Settings saved successfully!', 'default-post-author' ),
                    'error'    => __( 'An error occurred. Please try again.', 'default-post-author' ),
                    'updating' => __( 'Updating posts...', 'default-post-author' ),
                    'updated'  => __( 'posts updated successfully!', 'default-post-author' ),
                    'confirm'  => __( 'Are you sure you want to update all existing posts? This action cannot be undone.', 'default-post-author' ),
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

        $dpap_options = array(
            'default_author'        => isset( $_POST['default_author'] ) ? absint( $_POST['default_author'] ) : 1,
            'enable_default_author' => isset( $_POST['enable_default_author'] ) && 'true' === $_POST['enable_default_author'],
            'sync_revisions'        => isset( $_POST['sync_revisions'] ) && 'true' === $_POST['sync_revisions'],
        );

        update_option( DPAP_OPTIONS, $dpap_options );

        wp_send_json_success( array( 'message' => __( 'Settings saved successfully!', 'default-post-author' ) ) );
    }

    /**
     * AJAX: Update existing posts.
     *
     * @return void
     */
    public function ajax_update_posts() {
        check_ajax_referer( 'dpap_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'default-post-author' ) ) );
        }

        $dpap_default_author_id = dpap_get_option( 'default_author', 1 );

        // Validate user.
        $dpap_user = get_userdata( $dpap_default_author_id );
        if ( ! $dpap_user || ! array_intersect( array( 'author', 'editor', 'administrator' ), $dpap_user->roles ) ) {
            wp_send_json_error( array( 'message' => __( 'Selected default author is invalid.', 'default-post-author' ) ) );
        }

        $dpap_args = array(
            'post_type'      => 'post',
            'post_status'    => 'any',
            'posts_per_page' => 100,
            'fields'         => 'ids',
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'paged'          => 1,
        );

        $dpap_total_updated = 0;

        while ( true ) {
            $dpap_query = new WP_Query( $dpap_args );
            if ( ! $dpap_query->have_posts() ) {
                break;
            }

            foreach ( $dpap_query->posts as $dpap_post_id ) {
                $dpap_post_status = get_post_status( $dpap_post_id );
                if ( 'auto-draft' === $dpap_post_status ) {
                    continue;
                }

                $dpap_current_author = (int) get_post_field( 'post_author', $dpap_post_id );
                if ( $dpap_current_author === $dpap_default_author_id ) {
                    continue;
                }

                $dpap_updated_post_id = wp_update_post(
                    array(
                        'ID'          => $dpap_post_id,
                        'post_author' => $dpap_default_author_id,
                    ),
                    true
                );

                if ( ! is_wp_error( $dpap_updated_post_id ) ) {
                    ++$dpap_total_updated;
                }
            }

            ++$dpap_args['paged'];

            if ( 0 === count( $dpap_query->posts ) ) {
                break;
            }

            wp_reset_postdata();
        }

        if ( $dpap_total_updated > 1 ) {
            /* translators: %d: The number of posts updated. */
            $dpap_message = sprintf( __( '%d posts updated successfully!', 'default-post-author' ), $dpap_total_updated );
        } else {
            /* translators: %d: The number of posts updated. */
            $dpap_message = sprintf( __( '%d post updated successfully!', 'default-post-author' ), $dpap_total_updated );
        }

        wp_send_json_success(
            array(
                'message' => $dpap_message,
                'count'   => $dpap_total_updated,
            )
        );
    }

    /**
     * Get eligible users for author selection.
     *
     * @return array Array of WP_User objects.
     */
    public static function get_eligible_users() {
        $dpap_user_query = new WP_User_Query(
            array(
                'role__in' => array( 'author', 'editor', 'administrator' ),
                'orderby'  => 'display_name',
                'order'    => 'ASC',
            )
        );

        return $dpap_user_query->get_results();
    }
}