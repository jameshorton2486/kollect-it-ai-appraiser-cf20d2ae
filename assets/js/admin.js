
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
                
                // Basic validation
                if (!apiKey || !apiKey.trim()) {
                    showNotice('error', 'API key cannot be empty.');
                    return;
                }
                
                if (!apiKey.startsWith('sk-')) {
                    showNotice('error', 'Invalid API key format. OpenAI API keys should start with "sk-" followed by a string of characters.');
                    return;
                }
                
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
                    error: function(xhr) {
                        submitButton.val('Save API Key').prop('disabled', false);
                        let errorMessage = 'An error occurred. Please try again.';
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response && response.data && response.data.message) {
                                errorMessage = response.data.message;
                            }
                        } catch(e) {
                            console.error('Error parsing error response:', e);
                        }
                        
                        showNotice('error', errorMessage);
                    }
                });
            });
        }
        
        // Test API key
        if (testButton.length) {
            testButton.on('click', function(e) {
                e.preventDefault();
                
                const apiKey = $('#expert-appraiser-api-key').val();
                
                // Basic validation
                if (!apiKey || !apiKey.trim()) {
                    showNotice('error', 'Please enter an API key to test.');
                    return;
                }
                
                if (!apiKey.startsWith('sk-')) {
                    showNotice('error', 'Invalid API key format. OpenAI API keys should start with "sk-" followed by a string of characters.');
                    return;
                }
                
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
                            let errorMsg = response.data.message || 'API test failed. Please check your key.';
                            // Add more context for 401 errors
                            if (errorMsg.includes('unauthorized') || errorMsg.includes('Invalid API key')) {
                                errorMsg += ' Make sure your OpenAI account is in good standing and has sufficient credits.';
                            }
                            showNotice('error', errorMsg);
                        }
                    },
                    error: function(xhr) {
                        testButton.text('Test API Key').prop('disabled', false);
                        let errorMessage = 'An error occurred while testing the API key.';
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response && response.data && response.data.message) {
                                errorMessage = response.data.message;
                            }
                        } catch(e) {
                            console.error('Error parsing error response:', e);
                        }
                        
                        showNotice('error', errorMessage);
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
        
        // Add a "Show/Hide" button for API key
        if ($('#expert-appraiser-api-key').length) {
            const apiKeyField = $('#expert-appraiser-api-key');
            const toggleButton = $('<button type="button" class="button button-secondary" id="toggle-api-key">Show Key</button>');
            
            apiKeyField.after(toggleButton);
            
            toggleButton.on('click', function() {
                const fieldType = apiKeyField.attr('type');
                
                if (fieldType === 'password') {
                    apiKeyField.attr('type', 'text');
                    toggleButton.text('Hide Key');
                } else {
                    apiKeyField.attr('type', 'password');
                    toggleButton.text('Show Key');
                }
            });
        }
    });
})(jQuery);
