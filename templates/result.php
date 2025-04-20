<?php
/**
 * Template for displaying appraisal results
 *
 * @package Expert_Appraiser_AI
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the appraisal result from the ajax response
$appraisal = isset($result) ? $result : '';
?>

<div class="appraisal-result-container">
    <div class="appraisal-header">
        <h2><?php _e('Expert Appraisal Report', 'expert-appraiser-ai'); ?></h2>
        <p class="date"><?php echo date_i18n(get_option('date_format')); ?></p>
    </div>
    
    <div class="appraisal-content">
        <?php 
        // Convert markdown to HTML
        echo wpautop(wp_kses_post($appraisal));
        ?>
    </div>
    
    <div class="appraisal-actions">
        <button type="button" id="print-appraisal" class="action-button print-button">
            <?php _e('Print', 'expert-appraiser-ai'); ?>
        </button>
        
        <button type="button" id="copy-appraisal" class="action-button copy-button">
            <?php _e('Copy to Clipboard', 'expert-appraiser-ai'); ?>
        </button>
        
        <div class="export-dropdown">
            <button type="button" class="action-button export-button">
                <?php _e('Export', 'expert-appraiser-ai'); ?>
            </button>
            <div class="export-options">
                <a href="#" id="export-txt" class="export-option">
                    <?php _e('Text File (.txt)', 'expert-appraiser-ai'); ?>
                </a>
                <a href="#" id="export-docx" class="export-option">
                    <?php _e('Word Document (.docx)', 'expert-appraiser-ai'); ?>
                </a>
                <a href="#" id="export-pdf" class="export-option">
                    <?php _e('PDF Document (.pdf)', 'expert-appraiser-ai'); ?>
                </a>
            </div>
        </div>
        
        <button type="button" id="new-appraisal" class="action-button new-button">
            <?php _e('New Appraisal', 'expert-appraiser-ai'); ?>
        </button>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Print functionality
        $('#print-appraisal').on('click', function() {
            window.print();
        });
        
        // Copy to clipboard
        $('#copy-appraisal').on('click', function() {
            const appraisalText = $('.appraisal-content').text();
            navigator.clipboard.writeText(appraisalText).then(function() {
                alert('<?php _e('Appraisal copied to clipboard', 'expert-appraiser-ai'); ?>');
            });
        });
        
        // Export to text file
        $('#export-txt').on('click', function(e) {
            e.preventDefault();
            const appraisalText = $('.appraisal-content').text();
            const blob = new Blob([appraisalText], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'appraisal-report.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
        
        // New appraisal
        $('#new-appraisal').on('click', function() {
            $('#expert-appraiser-form').show();
            $('#appraisal-results').hide();
            $('#image-preview').empty();
            $('#paste-area').removeClass('has-image');
            $('.instructions', $('#paste-area')).show();
            $('#image-data').val('');
            $('#user-notes').val('');
            $('#submit-appraisal').prop('disabled', true);
        });
        
        // Export dropdown toggle
        $('.export-button').on('click', function() {
            $('.export-options').toggleClass('show');
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.export-dropdown').length) {
                $('.export-options').removeClass('show');
            }
        });
        
        // Add AJAX endpoints for DOCX and PDF exports (these will be implemented in future)
        $('#export-docx, #export-pdf').on('click', function(e) {
            e.preventDefault();
            alert('This export feature will be available in a future update.');
        });
    });
</script>
