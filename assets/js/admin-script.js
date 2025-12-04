/**
 * Default Post Author - Admin Script
 * Handles AJAX operations and UI interactions
 */

(function($) {
    'use strict';

    // Cache DOM elements
    const $settingsForm = $('#dpap-settings-form');
    const $saveBtn = $('#dpap-save-btn');
    const $updateBtn = $('#dpap-update-btn');
    const $toast = $('#dpap-toast');
    const $progressWrapper = $('#dpap-progress-wrapper');
    const $progressFill = $('#dpap-progress-fill');
    const $progressText = $('#dpap-progress-text');
    const $resultMessage = $('#dpap-result-message');

    /**
     * Show toast notification
     *
     * @param {string} message - Message to display
     * @param {string} type - Type of toast (success/error)
     */
    function showToast(message, type) {
        type = type || 'success';
        const $toastMessage = $toast.find('.dpap-toast-message');

        $toast.removeClass('success error').addClass(type);
        $toastMessage.text(message);
        $toast.addClass('show');

        setTimeout(function() {
            $toast.removeClass('show');
        }, 4000);
    }

    /**
     * Set button state
     *
     * @param {jQuery} $btn - Button element
     * @param {string} state - State to set (loading/success/empty)
     */
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

    /**
     * Update progress bar
     *
     * @param {number} percent - Progress percentage
     */
    function updateProgress(percent) {
        percent = Math.min(100, Math.max(0, percent));
        $progressFill.css('width', percent + '%');
        $progressText.text(Math.round(percent) + '%');
    }

    /**
     * Show result message
     *
     * @param {string} message - Message to display
     */
    function showResult(message) {
        $resultMessage.find('.dpap-result-text').text(message);
        $resultMessage.slideDown(300);
    }

    /**
     * Handle settings form submission
     */
    $settingsForm.on('submit', function(e) {
        e.preventDefault();

        // Get form data
        const formData = {
            action: 'dpap_save_settings',
            nonce: dpapAdmin.nonce,
            default_author: $('#default_author').val(),
            enable_default_author: $('#enable_default_author').is(':checked'),
            sync_revisions: $('#sync_revisions').is(':checked')
        };

        // Set loading state
        setButtonState($saveBtn, 'loading');
        $saveBtn.prop('disabled', true);

        // Send AJAX request
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

    /**
     * Handle bulk update posts
     */
    $updateBtn.on('click', function(e) {
        e.preventDefault();

        // Confirm action
        if (!confirm(dpapAdmin.strings.confirm)) {
            return;
        }

        // Set loading state
        setButtonState($updateBtn, 'loading');
        $updateBtn.prop('disabled', true);

        // Show progress
        $progressWrapper.slideDown(300);
        $resultMessage.hide();

        // Animate progress (simulated since we're doing batch processing)
        let progress = 0;
        const progressInterval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress > 90) {
                progress = 90;
                clearInterval(progressInterval);
            }
            updateProgress(progress);
        }, 200);

        // Send AJAX request
        $.ajax({
            url: dpapAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'dpap_update_posts',
                nonce: dpapAdmin.nonce
            },
            success: function(response) {
                clearInterval(progressInterval);
                updateProgress(100);

                setTimeout(function() {
                    $progressWrapper.slideUp(300);

                    if (response.success) {
                        showToast(response.data.message, 'success');
                        showResult(response.data.message);
                    } else {
                        showToast(response.data.message || dpapAdmin.strings.error, 'error');
                    }

                    setButtonState($updateBtn, '');
                }, 500);
            },
            error: function() {
                clearInterval(progressInterval);
                $progressWrapper.slideUp(300);
                setButtonState($updateBtn, '');
                showToast(dpapAdmin.strings.error, 'error');
            },
            complete: function() {
                $updateBtn.prop('disabled', false);
            }
        });
    });

    /**
     * Add ripple effect to buttons
     */
    $('.dpap-btn').on('click', function(e) {
        const $btn = $(this);
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const $ripple = $('<span class="dpap-ripple"></span>');
        $ripple.css({
            left: x + 'px',
            top: y + 'px'
        });

        $btn.append($ripple);

        setTimeout(function() {
            $ripple.remove();
        }, 600);
    });

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        // Add loaded class for animations
        setTimeout(function() {
            $('.dpap-wrap').addClass('dpap-loaded');
        }, 100);
    });

})(jQuery);