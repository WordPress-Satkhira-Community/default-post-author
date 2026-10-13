/**
 * Default Post Author - Admin Script (v2.3.0)
 * Handles AJAX operations, real batch processing and UI interactions
 */

(function($) {
	'use strict';

	const $settingsForm   = $('#dpap-settings-form');
	const $saveBtn        = $('#dpap-save-btn');
	const $updateBtn      = $('#dpap-update-btn');
	const $toast          = $('#dpap-toast');
	const $progressWrapper = $('#dpap-progress-wrapper');
	const $progressFill   = $('#dpap-progress-fill');
	const $progressText   = $('#dpap-progress-text');
	const $resultMessage  = $('#dpap-result-message');

	function showToast(message, type) {
		type = type || 'success';
		const $toastMessage = $toast.find('.dpap-toast-message');
		$toast.removeClass('success error').addClass(type);
		$toastMessage.text(message);
		$toast.addClass('show');
		setTimeout(function() {
			$toast.removeClass('show');
		}, 4500);
	}

	function setButtonState($btn, state) {
		$btn.removeClass('loading success');
		if (state) {
			$btn.addClass(state);
		}
		if (state === 'success') {
			setTimeout(function() {
				$btn.removeClass('success');
			}, 2000);
		}
	}

	function updateProgress(percent) {
		percent = Math.min(100, Math.max(0, percent));
		$progressFill.css('width', percent + '%');
		$progressText.text(Math.round(percent) + '%');
	}

	function showResult(message) {
		$resultMessage.find('.dpap-result-text').text(message);
		$resultMessage.slideDown(300);
	}

	/* ---------- Save Settings ---------- */
	$settingsForm.on('submit', function(e) {
		e.preventDefault();

		const postTypes = [];
		$('input[name="enabled_post_types[]"]:checked').each(function() {
			postTypes.push($(this).val());
		});

		const formData = {
			action: 'dpap_save_settings',
			nonce: dpapAdmin.nonce,
			default_author: $('#default_author').val(),
			enable_default_author: $('#enable_default_author').is(':checked'),
			sync_revisions: $('#sync_revisions').is(':checked'),
			force_only_capable: $('#force_only_capable').is(':checked'),
			enabled_post_types: postTypes
		};

		setButtonState($saveBtn, 'loading');
		$saveBtn.prop('disabled', true);

		$.ajax({
			url: dpapAdmin.ajaxUrl,
			type: 'POST',
			data: formData,
			success: function(response) {
				if (response.success) {
					setButtonState($saveBtn, 'success');
					showToast(response.data.message, 'success');
				} else {
					setButtonState($saveBtn, '');
					showToast(response.data.message || dpapAdmin.strings.error, 'error');
				}
			},
			error: function() {
				setButtonState($saveBtn, '');
				showToast(dpapAdmin.strings.error, 'error');
			},
			complete: function() {
				$saveBtn.prop('disabled', false);
			}
		});
	});

	/* ---------- Real Batch Bulk Update ---------- */
	$updateBtn.on('click', function(e) {
		e.preventDefault();

		if (!confirm(dpapAdmin.strings.confirm)) {
			return;
		}

		const dryRun     = $('#dpap_dry_run').is(':checked');
		const postStatus = $('#dpap_post_status').val();

		setButtonState($updateBtn, 'loading');
		$updateBtn.prop('disabled', true);
		$progressWrapper.slideDown(300);
		$resultMessage.hide();
		updateProgress(0);

		let totalUpdated = 0;
		let totalSkipped = 0;
		let currentPage  = 1;
		let maxPages     = 1;

		function processBatch() {
			$.ajax({
				url: dpapAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'dpap_update_posts',
					nonce: dpapAdmin.nonce,
					paged: currentPage,
					dry_run: dryRun ? 'true' : 'false',
					post_status: postStatus
				},
				success: function(response) {
					if (!response.success) {
						showToast(response.data.message || dpapAdmin.strings.error, 'error');
						finish();
						return;
					}

					const data = response.data;
					totalUpdated += data.updated;
					totalSkipped += data.skipped;
					maxPages = data.max_pages || 1;

					const percent = maxPages > 0 ? Math.round((currentPage / maxPages) * 100) : 100;
					updateProgress(percent);

					if (data.has_more) {
						currentPage++;
						processBatch(); // next batch
					} else {
						// Finished
						updateProgress(100);
						setTimeout(function() {
							$progressWrapper.slideUp(300);

							let msg;
							if (dryRun) {
								msg = dpapAdmin.strings.dry_run_info + ' ' +
									totalUpdated + ' would be updated, ' + totalSkipped + ' skipped.';
							} else {
								msg = totalUpdated + ' ' + dpapAdmin.strings.updated +
									' (' + totalSkipped + ' skipped).';
							}

							showToast(msg, 'success');
							showResult(msg);
							finish();
						}, 400);
					}
				},
				error: function() {
					showToast(dpapAdmin.strings.error, 'error');
					finish();
				}
			});
		}

		function finish() {
			setButtonState($updateBtn, '');
			$updateBtn.prop('disabled', false);
		}

		// Start the first batch
		processBatch();
	});

	/* ---------- Ripple effect ---------- */
	$('.dpap-btn').on('click', function(e) {
		const $btn = $(this);
		const rect = this.getBoundingClientRect();
		const x = e.clientX - rect.left;
		const y = e.clientY - rect.top;

		const $ripple = $('<span class="dpap-ripple"></span>');
		$ripple.css({ left: x + 'px', top: y + 'px' });
		$btn.append($ripple);

		setTimeout(function() {
			$ripple.remove();
		}, 600);
	});

	/* ---------- Init ---------- */
	$(document).ready(function() {
		setTimeout(function() {
			$('.dpap-wrap').addClass('dpap-loaded');
		}, 100);
	});

})(jQuery);
