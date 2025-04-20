(function($) {
    'use strict';
    
    $(document).ready(function() {
        const $appraiserForm = $('#expert-appraiser-form');
        const $imagePreview = $('#image-preview');
        const $pasteArea = $('#paste-area');
        const $submitButton = $('#submit-appraisal');
        const $resetButton = $('#reset-appraisal');
        const $resultsArea = $('#appraisal-results');
        const $loadingIndicator = $('#loading-indicator');
        const $imageData = $('#image-data');
        
        // Handle file uploads
        $('#image-upload').on('change', function(e) {
            const file = this.files[0];
            if (file) {
                handleImageFile(file);
            }
        });
        
        // Handle drag and drop
        $pasteArea.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });
        
        $pasteArea.on('dragleave', function() {
            $(this).removeClass('dragover');
        });
        
        $pasteArea.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            
            const file = e.originalEvent.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                handleImageFile(file);
            }
        });
        
        // Handle paste events globally
        $(document).on('paste', function(e) {
            const items = (e.originalEvent.clipboardData || e.clipboardData).items;
            
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    handleImageFile(blob);
                    break;
                }
            }
        });
        
        // Handle form submission
        $appraiserForm.on('submit', function(e) {
            e.preventDefault();
            
            if (!$imageData.val()) {
                alert('Please paste or upload an image first.');
                return;
            }
            
            submitAppraisal();
        });
        
        // Reset form
        $resetButton.on('click', function() {
            resetForm();
        });
        
        // Update the model reference in the submitAppraisal function
        function submitAppraisal() {
            $loadingIndicator.show();
            $submitButton.prop('disabled', true);
            
            $.ajax({
                url: expertAppraiserData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'process_appraisal',
                    nonce: expertAppraiserData.nonce,
                    image_data: $imageData.val(),
                    user_notes: $('#user-notes').val(),
                    model: 'gpt-4o-mini' // Updated to use the recommended model
                },
                success: function(response) {
                    $loadingIndicator.hide();
                    
                    if (response.success) {
                        $resultsArea.html(response.data.html);
                        $appraiserForm.hide();
                        $resultsArea.show();
                        
                        // Set up export buttons
                        setupExportButtons(response.data.appraisal);
                    } else {
                        alert(response.data.message || 'An error occurred');
                        $submitButton.prop('disabled', false);
                    }
                },
                error: function() {
                    $loadingIndicator.hide();
                    alert('Server error. Please try again.');
                    $submitButton.prop('disabled', false);
                }
            });
        }
        
        // Helper functions
        function handleImageFile(file) {
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                displayImagePreview(e.target.result);
                $imageData.val(e.target.result);
                $submitButton.prop('disabled', false);
            };
            reader.readAsDataURL(file);
        }
        
        function displayImagePreview(imageData) {
            $imagePreview.html(`<img src="${imageData}" alt="Image Preview" />`);
            $pasteArea.addClass('has-image');
            $('.instructions', $pasteArea).hide();
        }
        
        function resetForm() {
            $imagePreview.empty();
            $('.instructions', $pasteArea).show();
            $pasteArea.removeClass('has-image');
            $imageData.val('');
            $('#user-notes').val('');
            $submitButton.prop('disabled', true);
            $appraiserForm.show();
            $resultsArea.hide();
        }
        
        function setupExportButtons(appraisalText) {
            // Implemented in the results template
        }
    });
})(jQuery);
