
/**
 * Kollect-It Appraiser Frontend JavaScript
 */
(function($) {
    'use strict';

    // Initialize once DOM is ready
    $(document).ready(function() {
        // Elements
        const tabs = $('.kollect-it-tab');
        const tabContents = $('.kollect-it-tab-content');
        const imageUploader = $('.kollect-it-image-uploader');
        const imagePreview = $('.kollect-it-image-preview');
        const generateButton = $('#kollect-it-generate-button');
        const saveButton = $('#kollect-it-save-button');
        const templateSelect = $('#kollect-it-template-select');
        
        // Variables
        let selectedImage = null;
        let optimizedImage = null;
        let appraisalResult = null;
        let isGenerating = false;
        
        // Paste event for the entire document
        $(document).on('paste', function(e) {
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    const reader = new FileReader();
                    
                    reader.onload = function(event) {
                        handleImageSelection(event.target.result);
                    };
                    
                    reader.readAsDataURL(blob);
                    break;
                }
            }
        });
        
        // Tab switching
        tabs.on('click', function() {
            tabs.removeClass('active');
            $(this).addClass('active');
            
            const target = $(this).data('target');
            tabContents.removeClass('active');
            $(target).addClass('active');
        });
        
        // File selection via button
        $('#kollect-it-select-image').on('click', function() {
            $('#kollect-it-file-input').click();
        });
        
        // File input change
        $('#kollect-it-file-input').on('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    handleImageSelection(event.target.result);
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Drag and drop
        imageUploader.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragging');
        });
        
        imageUploader.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragging');
        });
        
        imageUploader.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragging');
            
            const file = e.originalEvent.dataTransfer.files[0];
            if (file && file.type.indexOf('image') !== -1) {
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    handleImageSelection(event.target.result);
                };
                
                reader.readAsDataURL(file);
            }
        });
        
        // Generate appraisal
        generateButton.on('click', function() {
            if (!optimizedImage) {
                showNotification('Please wait for image processing to complete', 'error');
                return;
            }
            
            if (isGenerating) {
                return;
            }
            
            isGenerating = true;
            generateButton.prop('disabled', true);
            $('#kollect-it-generate-spinner').show();
            $('#kollect-it-generate-text').text('Generating...');
            
            showNotification('Generating appraisal...', 'info');
            
            $.ajax({
                url: kollectItSettings.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'kollect_it_generate_appraisal',
                    nonce: kollectItSettings.nonce,
                    image: optimizedImage,
                    template_id: templateSelect.val()
                },
                success: function(response) {
                    if (response.success) {
                        appraisalResult = response.data.appraisalText;
                        $('#kollect-it-appraisal-results').html(appraisalResult);
                        
                        // Switch to results tab
                        tabs.removeClass('active');
                        $('.kollect-it-tab[data-target="#kollect-it-tab-results"]').addClass('active');
                        tabContents.removeClass('active');
                        $('#kollect-it-tab-results').addClass('active');
                        
                        saveButton.prop('disabled', false);
                        
                        showNotification('Appraisal generated successfully!', 'success');
                    } else {
                        showNotification(response.data.message || 'Failed to generate appraisal', 'error');
                    }
                },
                error: function() {
                    showNotification('Server error. Please try again later.', 'error');
                },
                complete: function() {
                    isGenerating = false;
                    generateButton.prop('disabled', false);
                    $('#kollect-it-generate-spinner').hide();
                    $('#kollect-it-generate-text').text('Generate Appraisal');
                }
            });
        });
        
        // Save appraisal
        saveButton.on('click', function() {
            if (!appraisalResult) {
                showNotification('Please generate an appraisal first', 'error');
                return;
            }
            
            saveButton.prop('disabled', true);
            $('#kollect-it-save-spinner').show();
            
            showNotification('Saving appraisal...', 'info');
            
            $.ajax({
                url: kollectItSettings.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'kollect_it_save_appraisal',
                    nonce: kollectItSettings.nonce,
                    image: optimizedImage,
                    appraisal: appraisalResult,
                    template_id: templateSelect.val()
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Appraisal saved successfully!', 'success');
                    } else {
                        showNotification(response.data.message || 'Failed to save appraisal', 'error');
                    }
                },
                error: function() {
                    showNotification('Server error. Please try again later.', 'error');
                },
                complete: function() {
                    saveButton.prop('disabled', false);
                    $('#kollect-it-save-spinner').hide();
                }
            });
        });
        
        // Template select change
        templateSelect.on('change', function() {
            const templateId = $(this).val();
            const description = $(this).find('option:selected').data('description');
            $('#kollect-it-template-description').text(description);
        });
        
        // Helper function to handle image selection
        function handleImageSelection(imageData) {
            selectedImage = imageData;
            
            // Show the image in the preview area
            imagePreview.html(`<img src="${imageData}" alt="Item to appraise">`);
            $('#kollect-it-image-metadata').show();
            
            // Process the image
            processImage(imageData);
            
            // Enable the generate button
            generateButton.prop('disabled', false);
        }
        
        // Helper function to process the image
        function processImage(imageData) {
            // Show processing indicators
            $('#kollect-it-processing-overlay').show();
            updateProgress(25);
            
            // Create an image object to get dimensions
            const img = new Image();
            img.onload = function() {
                // Get dimensions and update metadata
                $('#kollect-it-image-width').text(this.width);
                $('#kollect-it-image-height').text(this.height);
                
                updateProgress(50);
                
                // In a real implementation, you might want to resize/compress the image here
                // For now, we'll just use a setTimeout to simulate processing
                setTimeout(function() {
                    // Set optimized image
                    optimizedImage = imageData;
                    
                    updateProgress(100);
                    
                    // Hide processing overlay after a short delay
                    setTimeout(function() {
                        $('#kollect-it-processing-overlay').hide();
                    }, 500);
                }, 1000);
            };
            
            img.src = imageData;
        }
        
        // Helper function to update progress bar
        function updateProgress(value) {
            $('#kollect-it-progress-bar').css('width', value + '%');
        }
        
        // Helper function to show notifications
        function showNotification(message, type) {
            const notification = $('#kollect-it-notification');
            
            // Set message and type
            notification.text(message);
            notification.removeClass('kollect-it-error kollect-it-success');
            
            if (type === 'error') {
                notification.addClass('kollect-it-error');
            } else if (type === 'success') {
                notification.addClass('kollect-it-success');
            }
            
            // Show notification
            notification.addClass('show');
            
            // Hide after 3 seconds
            setTimeout(function() {
                notification.removeClass('show');
            }, 3000);
        }
    });
})(jQuery);
