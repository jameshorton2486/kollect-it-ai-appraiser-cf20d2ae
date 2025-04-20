
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="kollect-it-container">
    <h2 class="kollect-it-title"><?php echo esc_html($atts['title']); ?></h2>
    
    <div class="kollect-it-card">
        <div class="kollect-it-form-group">
            <label class="kollect-it-label"><?php _e('Appraisal Type', 'kollect-it-appraiser'); ?></label>
            <select id="kollect-it-template-select" class="kollect-it-select">
                <?php foreach ($prompt_templates as $id => $text) : ?>
                    <option value="<?php echo esc_attr($id); ?>" data-description="<?php echo esc_attr(substr($text, 0, 100) . '...'); ?>">
                        <?php echo esc_html(ucfirst($id)); ?> <?php _e('Appraisal', 'kollect-it-appraiser'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p id="kollect-it-template-description" class="kollect-it-description">
                <?php echo esc_html(substr(reset($prompt_templates), 0, 100) . '...'); ?>
            </p>
        </div>
        
        <div class="kollect-it-control-panel">
            <button class="kollect-it-button kollect-it-button-outline" id="kollect-it-paste-button">
                <?php _e('Paste Image (Ctrl+V)', 'kollect-it-appraiser'); ?>
            </button>
            
            <button class="kollect-it-button" id="kollect-it-generate-button" disabled>
                <span id="kollect-it-generate-spinner" class="kollect-it-spinner" style="display: none;"></span>
                <span id="kollect-it-generate-text"><?php _e('Generate Appraisal', 'kollect-it-appraiser'); ?></span>
            </button>
            
            <?php if ($atts['show_save'] === 'true') : ?>
                <button class="kollect-it-button kollect-it-button-outline" id="kollect-it-save-button" disabled>
                    <span id="kollect-it-save-spinner" class="kollect-it-spinner" style="display: none;"></span>
                    <?php _e('Save Appraisal', 'kollect-it-appraiser'); ?>
                </button>
            <?php endif; ?>
        </div>
        
        <div class="kollect-it-tabs">
            <div class="kollect-it-tab active" data-target="#kollect-it-tab-upload">
                <?php _e('Image Upload', 'kollect-it-appraiser'); ?>
            </div>
            <div class="kollect-it-tab" data-target="#kollect-it-tab-results">
                <?php _e('Appraisal Results', 'kollect-it-appraiser'); ?>
            </div>
        </div>
        
        <div id="kollect-it-tab-upload" class="kollect-it-tab-content active">
            <div class="kollect-it-card">
                <h3><?php _e('Upload Image', 'kollect-it-appraiser'); ?></h3>
                <p class="kollect-it-description">
                    <?php _e('Upload or paste an image of the item you want to appraise', 'kollect-it-appraiser'); ?>
                </p>
                
                <div class="kollect-it-image-preview">
                    <div id="kollect-it-processing-overlay" style="display: none;">
                        <div class="kollect-it-spinner"></div>
                        <p><?php _e('Processing image...', 'kollect-it-appraiser'); ?></p>
                        <div class="kollect-it-progress">
                            <div id="kollect-it-progress-bar" class="kollect-it-progress-bar"></div>
                        </div>
                    </div>
                    
                    <div id="kollect-it-image-metadata" style="display: none;">
                        <div class="kollect-it-metadata-row">
                            <?php _e('Dimensions:', 'kollect-it-appraiser'); ?> 
                            <span id="kollect-it-image-width">0</span> x <span id="kollect-it-image-height">0</span>px
                        </div>
                    </div>
                </div>
                
                <div class="kollect-it-image-uploader">
                    <div>
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 15V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V15M17 8L12 3M12 3L7 8M12 3V15" stroke="#9b87f5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <p><?php _e('Drag and drop an image here, or', 'kollect-it-appraiser'); ?></p>
                    <button id="kollect-it-select-image" class="kollect-it-button">
                        <?php _e('Select Image', 'kollect-it-appraiser'); ?>
                    </button>
                    <input id="kollect-it-file-input" type="file" accept="image/*" style="display: none;">
                </div>
            </div>
        </div>
        
        <div id="kollect-it-tab-results" class="kollect-it-tab-content">
            <div class="kollect-it-card">
                <h3><?php _e('Appraisal Results', 'kollect-it-appraiser'); ?></h3>
                <p class="kollect-it-description">
                    <?php _e('Professional appraisal report with detailed analysis', 'kollect-it-appraiser'); ?>
                </p>
                
                <div class="kollect-it-results" id="kollect-it-appraisal-results">
                    <p class="kollect-it-empty-state">
                        <?php _e('No appraisal results yet. Upload an image to get started.', 'kollect-it-appraiser'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div id="kollect-it-notification" class="kollect-it-notification"></div>
</div>
