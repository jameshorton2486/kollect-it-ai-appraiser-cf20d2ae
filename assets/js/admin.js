
/**
 * Expert Appraiser AI Admin JavaScript
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // API Key form handling
        const apiKeyForm = $('#expert-appraiser-api-key-form');
        const testButton = $('#expert-appraiser-test-api-key');
        
        if (apiKeyForm.length) {
            apiKeyForm.on('submit', function(e) {
                e.preventDefault();
                
                const apiKey = $('#expert-appraiser-api-key').val();
                const submitButton = $('#expert-appraiser-api-key-submit');
                
                submitButton.val('Saving...').prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'expert_appraiser_save_api_key',
                        nonce: $('#expert_appraiser_admin_nonce').val(),
                        api_key: apiKey
                    },
                    success: function(response) {
                        submitButton.val('Save API Key').prop('disabled', false);
                        
                        if (response.success) {
                            showNotice('success', response.data.message);
                        } else {
                            showNotice('error', response.data.message);
                        }
                    },
                    error: function() {
                        submitButton.val('Save API Key').prop('disabled', false);
                        showNotice('error', 'An error occurred. Please try again.');
                    }
                });
            });
        }
        
        // Test API key
        if (testButton.length) {
            testButton.on('click', function(e) {
                e.preventDefault();
                
                const apiKey = $('#expert-appraiser-api-key').val();
                
                $(this).text('Testing...').prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'expert_appraiser_test_api_key',
                        nonce: $('#expert_appraiser_admin_nonce').val(),
                        api_key: apiKey
                    },
                    success: function(response) {
                        testButton.text('Test API Key').prop('disabled', false);
                        
                        if (response.success) {
                            showNotice('success', response.data.message);
                        } else {
                            showNotice('error', response.data.message);
                        }
                    },
                    error: function() {
                        testButton.text('Test API Key').prop('disabled', false);
                        showNotice('error', 'An error occurred while testing the API key.');
                    }
                });
            });
        }
        
        // Show notice
        function showNotice(type, message) {
            // Remove existing notices
            $('.expert-appraiser-admin-notice').remove();
            
            // Create new notice
            const notice = $(`
                <div class="expert-appraiser-admin-notice expert-appraiser-admin-notice-${type}">
                    <p>${message}</p>
                </div>
            `);
            
            // Insert after form
            apiKeyForm.after(notice);
            
            // Scroll to notice
            $('html, body').animate({
                scrollTop: notice.offset().top - 50
            }, 500);
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                notice.fadeOut(400, function() {
                    notice.remove();
                });
            }, 5000);
        }
    });
})(jQuery);
