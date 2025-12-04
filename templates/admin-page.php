<?php
/**
 * Admin page template.
 *
 * @package DefaultPostAuthor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$dpap_options               = get_option( DPAP_OPTIONS, array() );
$dpap_default_author        = isset( $dpap_options['default_author'] ) ? $dpap_options['default_author'] : 1;
$dpap_enable_default_author = isset( $dpap_options['enable_default_author'] ) ? $dpap_options['enable_default_author'] : true;
$dpap_sync_revisions        = isset( $dpap_options['sync_revisions'] ) ? $dpap_options['sync_revisions'] : true;

$dpap_users = DPAP_Admin::get_eligible_users();

// Get post count.
$dpap_post_count  = wp_count_posts( 'post' );
$dpap_total_posts = $dpap_post_count->publish + $dpap_post_count->draft + $dpap_post_count->pending + $dpap_post_count->private;
?>

<div class="dpap-wrap">
    <!-- Header -->
    <div class="dpap-header">
        <div class="dpap-header-content">
            <div class="dpap-logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
            <div class="dpap-header-text">
                <h1><?php esc_html_e( 'Default Post Author', 'default-post-author' ); ?></h1>
                <p><?php esc_html_e( 'Configure default author settings for your posts', 'default-post-author' ); ?></p>
            </div>
        </div>
        <div class="dpap-header-badge">
            <span class="dpap-version">v<?php echo esc_html( DPAP_VERSION ); ?></span>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="dpap-toast" id="dpap-toast">
        <div class="dpap-toast-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>
        </div>
        <span class="dpap-toast-message"></span>
    </div>

    <div class="dpap-container">
        <!-- Main Settings Card -->
        <div class="dpap-card dpap-card-main">
            <div class="dpap-card-header">
                <div class="dpap-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.14 12.94c.04-.31.06-.63.06-.94 0-.31-.02-.63-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
                    </svg>
                </div>
                <h2><?php esc_html_e( 'General Settings', 'default-post-author' ); ?></h2>
            </div>

            <div class="dpap-card-body">
                <form id="dpap-settings-form">
                    <!-- Default Author Selection -->
                    <div class="dpap-form-group">
                        <label for="default_author" class="dpap-label">
                            <?php esc_html_e( 'Default Post Author', 'default-post-author' ); ?>
                        </label>
                        <div class="dpap-select-wrapper">
                            <select id="default_author" name="default_author" class="dpap-select">
                                <?php if ( empty( $dpap_users ) ) : ?>
                                    <option value="0"><?php esc_html_e( 'No eligible users found', 'default-post-author' ); ?></option>
                                <?php else : ?>
                                    <?php foreach ( $dpap_users as $dpap_user ) : ?>
                                        <option value="<?php echo esc_attr( $dpap_user->ID ); ?>" <?php selected( $dpap_user->ID, $dpap_default_author ); ?>>
                                            <?php echo esc_html( $dpap_user->display_name ); ?> (<?php echo esc_html( $dpap_user->user_email ); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="dpap-select-arrow">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M7 10l5 5 5-5z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="dpap-description"><?php esc_html_e( 'Select the default author that will be assigned to new posts.', 'default-post-author' ); ?></p>
                    </div>

                    <!-- Toggle Options -->
                    <div class="dpap-toggles-section">
                        <h3 class="dpap-section-title"><?php esc_html_e( 'Feature Settings', 'default-post-author' ); ?></h3>
                        
                        <!-- Enable Default Author -->
                        <div class="dpap-toggle-group">
                            <div class="dpap-toggle-info">
                                <span class="dpap-toggle-label"><?php esc_html_e( 'Enable Default Author', 'default-post-author' ); ?></span>
                                <span class="dpap-toggle-description"><?php esc_html_e( 'Automatically assign the default author to new posts.', 'default-post-author' ); ?></span>
                            </div>
                            <label class="dpap-toggle">
                                <input type="checkbox" name="enable_default_author" id="enable_default_author" <?php checked( $dpap_enable_default_author ); ?>>
                                <span class="dpap-toggle-slider"></span>
                            </label>
                        </div>

                        <!-- Sync Revisions -->
                        <div class="dpap-toggle-group">
                            <div class="dpap-toggle-info">
                                <span class="dpap-toggle-label"><?php esc_html_e( 'Sync Revision Authors', 'default-post-author' ); ?></span>
                                <span class="dpap-toggle-description"><?php esc_html_e( 'Keep revision authors in sync with the parent post author.', 'default-post-author' ); ?></span>
                            </div>
                            <label class="dpap-toggle">
                                <input type="checkbox" name="sync_revisions" id="sync_revisions" <?php checked( $dpap_sync_revisions ); ?>>
                                <span class="dpap-toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="dpap-form-actions">
                        <button type="submit" class="dpap-btn dpap-btn-primary" id="dpap-save-btn">
                            <span class="dpap-btn-text"><?php esc_html_e( 'Save Settings', 'default-post-author' ); ?></span>
                            <span class="dpap-btn-loading">
                                <svg class="dpap-spinner" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round">
                                        <animate attributeName="stroke-dasharray" values="0 150;42 150;42 150;42 150" dur="1.5s" repeatCount="indefinite"/>
                                        <animate attributeName="stroke-dashoffset" values="0;-16;-59;-59" dur="1.5s" repeatCount="indefinite"/>
                                    </circle>
                                </svg>
                                <?php esc_html_e( 'Saving...', 'default-post-author' ); ?>
                            </span>
                            <span class="dpap-btn-success">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                </svg>
                                <?php esc_html_e( 'Saved!', 'default-post-author' ); ?>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Update Card -->
        <div class="dpap-card dpap-card-warning">
            <div class="dpap-card-header">
                <div class="dpap-card-icon dpap-icon-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <h2><?php esc_html_e( 'Bulk Update Posts', 'default-post-author' ); ?></h2>
            </div>

            <div class="dpap-card-body">
                <div class="dpap-info-box">
                    <div class="dpap-info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                        </svg>
                    </div>
                    <div class="dpap-info-content">
                        <p><?php esc_html_e( 'This will update the author of all existing posts to the selected default author. The operation is processed in batches to prevent timeouts.', 'default-post-author' ); ?></p>
                        <div class="dpap-stats">
                            <div class="dpap-stat">
                                <span class="dpap-stat-value"><?php echo esc_html( number_format( $dpap_total_posts ) ); ?></span>
                                <span class="dpap-stat-label"><?php esc_html_e( 'Total Posts', 'default-post-author' ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="dpap-progress-wrapper" id="dpap-progress-wrapper" style="display: none;">
                    <div class="dpap-progress-bar">
                        <div class="dpap-progress-fill" id="dpap-progress-fill"></div>
                    </div>
                    <span class="dpap-progress-text" id="dpap-progress-text">0%</span>
                </div>

                <div class="dpap-form-actions">
                    <button type="button" class="dpap-btn dpap-btn-warning" id="dpap-update-btn">
                        <span class="dpap-btn-text">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                            </svg>
                            <?php esc_html_e( 'Update All Posts', 'default-post-author' ); ?>
                        </span>
                        <span class="dpap-btn-loading">
                            <svg class="dpap-spinner" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round">
                                    <animate attributeName="stroke-dasharray" values="0 150;42 150;42 150;42 150" dur="1.5s" repeatCount="indefinite"/>
                                    <animate attributeName="stroke-dashoffset" values="0;-16;-59;-59" dur="1.5s" repeatCount="indefinite"/>
                                </circle>
                            </svg>
                            <?php esc_html_e( 'Updating...', 'default-post-author' ); ?>
                        </span>
                    </button>
                </div>

                <!-- Result Message -->
                <div class="dpap-result-message" id="dpap-result-message" style="display: none;">
                    <div class="dpap-result-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                    </div>
                    <span class="dpap-result-text"></span>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="dpap-card dpap-card-info">
            <div class="dpap-card-header">
                <div class="dpap-card-icon dpap-icon-info">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                    </svg>
                </div>
                <h2><?php esc_html_e( 'Plugin Information', 'default-post-author' ); ?></h2>
            </div>

            <div class="dpap-card-body">
                <div class="dpap-info-grid">
                    <div class="dpap-info-item">
                        <span class="dpap-info-label"><?php esc_html_e( 'Version', 'default-post-author' ); ?></span>
                        <span class="dpap-info-value"><?php echo esc_html( DPAP_VERSION ); ?></span>
                    </div>
                    <div class="dpap-info-item">
                        <span class="dpap-info-label"><?php esc_html_e( 'Author', 'default-post-author' ); ?></span>
                        <span class="dpap-info-value">
                            <a href="https://www.monarchwp.com" target="_blank" rel="noopener">MonarchWP</a>
                        </span>
                    </div>
                    <div class="dpap-info-item">
                        <span class="dpap-info-label"><?php esc_html_e( 'Support', 'default-post-author' ); ?></span>
                        <span class="dpap-info-value">
                            <a href="https://wordpress.org/support/plugin/default-post-author/" target="_blank" rel="noopener"><?php esc_html_e( 'Get Help', 'default-post-author' ); ?></a>
                        </span>
                    </div>
                    <div class="dpap-info-item">
                        <span class="dpap-info-label"><?php esc_html_e( 'Rate Us', 'default-post-author' ); ?></span>
                        <span class="dpap-info-value">
                            <a href="https://wordpress.org/support/plugin/default-post-author/reviews/#new-post" target="_blank" rel="noopener">
                                <span class="dpap-stars">★★★★★</span>
                            </a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="dpap-footer">
        <p>
            <?php
            printf(
                /* Translators: %1$s is a heart icon, %2$s is the developer name with link. */
                esc_html__( 'Made with %1$s by %2$s', 'default-post-author' ),
                '<span class="dpap-heart">❤</span>',
                '<a href="https://www.monarchwp.com" target="_blank" rel="noopener">MonarchWP</a>'
            );
            ?>
        </p>
    </div>
</div>