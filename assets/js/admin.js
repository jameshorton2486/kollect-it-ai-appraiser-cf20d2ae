
/**
 * Kollect-It Appraiser Admin JavaScript
 */
(function($) {
    'use strict';

    // Initialize once DOM is ready
    $(document).ready(function() {
        // API Key form
        const apiKeyForm = $('#kollect-it-api-key-form');
        
        if (apiKeyForm.length) {
            apiKeyForm.on('submit', function(e) {
                e.preventDefault();
                
                const apiKey = $('#kollect-it-api-key').val();
                const submitButton = $('#kollect-it-api-key-submit');
                const originalText = submitButton.val();
                
                // Disable button and show loading state
                submitButton.val('Saving...').prop('disabled', true);
                
                // Send AJAX request
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'kollect_it_save_api_key',
                        nonce: $('#kollect_it_admin_nonce').val(),
                        api_key: apiKey
                    },
                    success: function(response) {
                        if (response.success) {
                            showAdminNotice('success', response.data.message);
                        } else {
                            showAdminNotice('error', response.data.message);
                        }
                    },
                    error: function() {
                        showAdminNotice('error', 'A server error occurred. Please try again.');
                    },
                    complete: function() {
                        // Restore button state
                        submitButton.val(originalText).prop('disabled', false);
                    }
                });
            });
        }
        
        // Test API Key
        $('#kollect-it-test-api-key').on('click', function(e) {
            e.preventDefault();
            
            const apiKey = $('#kollect-it-api-key').val();
            const button = $(this);
            const originalText = button.text();
            
            if (!apiKey) {
                showAdminNotice('error', 'Please enter an API key to test.');
                return;
            }
            
            // Disable button and show loading state
            button.text('Testing...').prop('disabled', true);
            
            // Send AJAX request to test the API key
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'kollect_it_test_api_key',
                    nonce: $('#kollect_it_admin_nonce').val(),
                    api_key: apiKey
                },
                success: function(response) {
                    if (response.success) {
                        showAdminNotice('success', 'API key is valid and working properly.');
                    } else {
                        showAdminNotice('error', response.data.message || 'Failed to validate API key.');
                    }
                },
                error: function() {
                    showAdminNotice('error', 'A server error occurred. Please try again.');
                },
                complete: function() {
                    // Restore button state
                    button.text(originalText).prop('disabled', false);
                }
            });
        });
        
        // Helper function to show admin notices
        function showAdminNotice(type, message) {
            // Remove any existing notices
            $('.kollect-it-admin-notice').remove();
            
            // Create new notice
            const notice = $('<div class="kollect-it-admin-notice kollect-it-admin-notice-' + type + '"><p>' + message + '</p></div>');
            
            // Insert after the form
            apiKeyForm.after(notice);
            
            // Scroll to notice
            $('html, body').animate({
                scrollTop: notice.offset().top - 50
            }, 500);
        }
    });
})(jQuery);
