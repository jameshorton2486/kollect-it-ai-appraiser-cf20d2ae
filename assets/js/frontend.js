
/**
 * Expert Appraiser AI Frontend JavaScript
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        const form = $('#expert-appraiser-form');
        const imageInput = $('#item_image');
        const dropzone = $('#expert-appraiser-dropzone');
        const preview = $('#expert-appraiser-image-preview');
        const results = $('#expert-appraiser-results');
        const appraisalContent = $('#expert-appraiser-appraisal-content');
        const submitButton = $('#expert-appraiser-submit');
        const loadingIndicator = $('#expert-appraiser-loading');
        const saveButton = $('#expert-appraiser-save');
        const printButton = $('#expert-appraiser-print');
        
        // Store the image data
        let imageData = '';
        
        // Handle drag and drop
        dropzone.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).css('border-color', '#0073aa');
        });
        
        dropzone.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).css('border-color', '#ccc');
        });
        
        dropzone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).css('border-color', '#ccc');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                handleFiles(files);
            }
        });
        
        // Click on dropzone
        dropzone.on('click', function() {
            imageInput.click();
        });
        
        // File input change
        imageInput.on('change', function() {
            if (this.files.length) {
                handleFiles(this.files);
            }
        });
        
        // Handle selected files
        function handleFiles(files) {
            const file = files[0];
            
            // Check if it's an image
            if (!file.type.match('image.*')) {
                alert('Please select an image file.');
                return;
            }
            
            // Read and display the image
            const reader = new FileReader();
            reader.onload = function(e) {
                imageData = e.target.result;
                preview.html('<img src="' + imageData + '" alt="Item preview"/>');
                preview.show();
                dropzone.hide();
            };
            reader.readAsDataURL(file);
        }
        
        // Form submission
        form.on('submit', function(e) {
            e.preventDefault();
            
            if (!imageData) {
                alert('Please upload an image of the item.');
                return;
            }
            
            // Get form values
            const title = $('#item_title').val();
            const description = $('#item_description').val();
            const templateId = $('#expert-appraiser-template-select').val();
            
            // Show loading indicator
            submitButton.hide();
            loadingIndicator.show();
            
            // Generate appraisal
            $.ajax({
                url: expertAppraiserSettings.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'expert_appraiser_generate',
                    nonce: expertAppraiserSettings.nonce,
                    title: title,
                    description: description,
                    template_id: templateId,
                    image: imageData
                },
                success: function(response) {
                    // Hide loading indicator
                    loadingIndicator.hide();
                    submitButton.show();
                    
                    if (response.success) {
                        // Show results
                        appraisalContent.html(response.data.appraisalText);
                        results.show();
                        
                        // Scroll to results
                        $('html, body').animate({
                            scrollTop: results.offset().top - 50
                        }, 500);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    loadingIndicator.hide();
                    submitButton.show();
                    alert('An error occurred. Please try again.');
                }
            });
        });
        
        // Save appraisal
        if (saveButton) {
            saveButton.on('click', function() {
                const title = $('#item_title').val();
                const description = $('#item_description').val();
                const templateId = $('#expert-appraiser-template-select').val();
                const appraisal = appraisalContent.html();
                
                $(this).prop('disabled', true).text('Saving...');
                
                $.ajax({
                    url: expertAppraiserSettings.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'expert_appraiser_save',
                        nonce: expertAppraiserSettings.nonce,
                        title: title,
                        description: description,
                        template_id: templateId,
                        image: imageData,
                        appraisal: appraisal
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Appraisal saved successfully!');
                        } else {
                            alert('Error: ' + response.data.message);
                        }
                        saveButton.prop('disabled', false).text('Save This Appraisal');
                    },
                    error: function() {
                        alert('An error occurred while saving the appraisal.');
                        saveButton.prop('disabled', false).text('Save This Appraisal');
                    }
                });
            });
        }
        
        // Print appraisal
        if (printButton) {
            printButton.on('click', function() {
                const title = $('#item_title').val();
                const appraisal = appraisalContent.html();
                
                // Create print window
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Expert Appraisal: ${title}</title>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                            h1 { text-align: center; }
                            .appraisal-date { text-align: right; margin-bottom: 20px; }
                            .content { margin-top: 30px; }
                        </style>
                    </head>
                    <body>
                        <h1>Expert Appraisal Report</h1>
                        <div class="appraisal-date">Date: ${new Date().toLocaleDateString()}</div>
                        <h2>${title}</h2>
                        <div class="content">${appraisal}</div>
                    </body>
                    </html>
                `);
                
                // Print and close
                printWindow.document.close();
                printWindow.focus();
                setTimeout(function() {
                    printWindow.print();
                    printWindow.close();
                }, 250);
            });
        }
    });
})(jQuery);
