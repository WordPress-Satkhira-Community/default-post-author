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
$dpap_default_author        = isset( $dpap_options['default_author'] ) ? (int) $dpap_options['default_author'] : 1;
$dpap_enable_default_author = isset( $dpap_options['enable_default_author'] ) ? (bool) $dpap_options['enable_default_author'] : true;
$dpap_sync_revisions        = isset( $dpap_options['sync_revisions'] ) ? (bool) $dpap_options['sync_revisions'] : true;
$dpap_force_only_capable    = isset( $dpap_options['force_only_capable'] ) ? (bool) $dpap_options['force_only_capable'] : true;
$dpap_enabled_post_types    = isset( $dpap_options['enabled_post_types'] ) && is_array( $dpap_options['enabled_post_types'] ) ? $dpap_options['enabled_post_types'] : array( 'post' );

$dpap_users      = DPAP_Admin::get_eligible_users();
$dpap_post_types = DPAP_Admin::get_supported_post_types();

// Approximate post count for display.
$dpap_post_count  = wp_count_posts( 'post' );
$dpap_total_posts = (int) $dpap_post_count->publish + (int) $dpap_post_count->draft + (int) $dpap_post_count->pending + (int) $dpap_post_count->private;
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

					<!-- Post Types -->
					<div class="dpap-form-group">
						<label class="dpap-label"><?php esc_html_e( 'Apply to Post Types', 'default-post-author' ); ?></label>
						<div class="dpap-post-types">
							<?php foreach ( $dpap_post_types as $dpap_pt_name => $dpap_pt_label ) : ?>
								<label class="dpap-checkbox-label">
									<input type="checkbox" name="enabled_post_types[]" value="<?php echo esc_attr( $dpap_pt_name ); ?>"
										<?php checked( in_array( $dpap_pt_name, $dpap_enabled_post_types, true ) ); ?>>
									<span><?php echo esc_html( $dpap_pt_label ); ?> <code><?php echo esc_html( $dpap_pt_name ); ?></code></span>
								</label>
							<?php endforeach; ?>
						</div>
						<p class="dpap-description"><?php esc_html_e( 'Choose which post types should receive the default author.', 'default-post-author' ); ?></p>
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

						<!-- Force only for capable users (fixes Author role issue) -->
						<div class="dpap-toggle-group">
							<div class="dpap-toggle-info">
								<span class="dpap-toggle-label"><?php esc_html_e( 'Only force for users who can edit others’ posts', 'default-post-author' ); ?></span>
								<span class="dpap-toggle-description"><?php esc_html_e( 'Recommended. Prevents permission errors when Authors create posts. Admins & Editors still get the default author forced.', 'default-post-author' ); ?></span>
							</div>
							<label class="dpap-toggle">
								<input type="checkbox" name="force_only_capable" id="force_only_capable" <?php checked( $dpap_force_only_capable ); ?>>
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
						<p><?php esc_html_e( 'Update the author of existing posts to the selected default author. Processing is done in real batches to avoid timeouts.', 'default-post-author' ); ?></p>
						<div class="dpap-stats">
							<div class="dpap-stat">
								<span class="dpap-stat-value"><?php echo esc_html( number_format_i18n( $dpap_total_posts ) ); ?></span>
								<span class="dpap-stat-label"><?php esc_html_e( 'Posts (approx.)', 'default-post-author' ); ?></span>
							</div>
						</div>
					</div>
				</div>

				<!-- Bulk options -->
				<div class="dpap-form-group">
					<label for="dpap_post_status" class="dpap-label"><?php esc_html_e( 'Post Status Filter', 'default-post-author' ); ?></label>
					<select id="dpap_post_status" class="dpap-select">
						<option value="any"><?php esc_html_e( 'Any status', 'default-post-author' ); ?></option>
						<option value="publish"><?php esc_html_e( 'Published', 'default-post-author' ); ?></option>
						<option value="draft"><?php esc_html_e( 'Draft', 'default-post-author' ); ?></option>
						<option value="pending"><?php esc_html_e( 'Pending', 'default-post-author' ); ?></option>
						<option value="private"><?php esc_html_e( 'Private', 'default-post-author' ); ?></option>
						<option value="future"><?php esc_html_e( 'Scheduled', 'default-post-author' ); ?></option>
					</select>
				</div>

				<label class="dpap-checkbox-label" style="margin-bottom: 20px;">
					<input type="checkbox" id="dpap_dry_run">
					<span><?php esc_html_e( 'Dry-run mode (preview only – no changes will be made)', 'default-post-author' ); ?></span>
				</label>

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
							<?php esc_html_e( 'Update Posts', 'default-post-author' ); ?>
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

		<!-- Recommended Plugins Card -->
		<div class="dpap-card dpap-card-recommended">
			<div class="dpap-card-header">
				<div class="dpap-card-icon dpap-icon-recommended">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
						<path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
					</svg>
				</div>
				<h2><?php esc_html_e( 'Our Recommended Plugins', 'default-post-author' ); ?></h2>
			</div>
			<div class="dpap-card-body">
				<p class="dpap-description" style="margin-bottom: 20px;">
					<?php esc_html_e( 'Enhance your site security and performance with these free plugins from the same team.', 'default-post-author' ); ?>
				</p>
				<div class="dpap-recommended-grid">
					<!-- Bot Traffic Shield -->
					<div class="dpap-recommended-item">
						<div class="dpap-recommended-icon">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
						</div>
						<div class="dpap-recommended-content">
							<h3><?php esc_html_e( 'Bot Traffic Shield', 'default-post-author' ); ?></h3>
							<p><?php esc_html_e( 'Block malicious bots, scrapers and unwanted traffic to protect your server resources and improve performance.', 'default-post-author' ); ?></p>
							<?php echo DPAP_Admin::render_plugin_action_button( 'bot-traffic-shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>

					<!-- Simple Disable XML-RPC -->
					<div class="dpap-recommended-item">
						<div class="dpap-recommended-icon">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
						</div>
						<div class="dpap-recommended-content">
							<h3><?php esc_html_e( 'Simple Disable XML-RPC', 'default-post-author' ); ?></h3>
							<p><?php esc_html_e( 'One-click solution to disable XML-RPC and protect your site from brute-force and DDoS attacks via XML-RPC.', 'default-post-author' ); ?></p>
							<?php echo DPAP_Admin::render_plugin_action_button( 'simple-disable-xml-rpc' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>

					<!-- Comments Shield -->
					<div class="dpap-recommended-item">
						<div class="dpap-recommended-icon">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
						</div>
						<div class="dpap-recommended-content">
							<h3><?php esc_html_e( 'Comments Shield', 'default-post-author' ); ?></h3>
							<p><?php esc_html_e( 'Stop comment spam and protect your comment forms with advanced anti-spam rules and honeypot protection.', 'default-post-author' ); ?></p>
							<?php echo DPAP_Admin::render_plugin_action_button( 'comments-shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
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
				<ul class="dpap-info-list">
					<li><strong><?php esc_html_e( 'Version', 'default-post-author' ); ?>:</strong> <?php echo esc_html( DPAP_VERSION ); ?></li>
					<li><strong><?php esc_html_e( 'Requires WordPress', 'default-post-author' ); ?>:</strong> 6.1+</li>
					<li><strong><?php esc_html_e( 'Requires PHP', 'default-post-author' ); ?>:</strong> 8.0+</li>
					<li>
						<strong><?php esc_html_e( 'Support', 'default-post-author' ); ?>:</strong>
						<a href="https://wordpress.org/support/plugin/default-post-author/" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'WordPress.org Support Forum', 'default-post-author' ); ?>
						</a>
					</li>
					<li>
						<strong><?php esc_html_e( 'GitHub', 'default-post-author' ); ?>:</strong>
						<a href="https://github.com/WordPress-Satkhira-Community/default-post-author" target="_blank" rel="noopener noreferrer">
							WordPress-Satkhira-Community/default-post-author
						</a>
					</li>
				</ul>
				<p class="dpap-description" style="margin-top: 15px;">
					<?php esc_html_e( 'Tip: Use the “Only force for users who can edit others’ posts” option to avoid permission errors when Authors create new posts.', 'default-post-author' ); ?>
				</p>
			</div>
		</div>
	</div>
</div>
