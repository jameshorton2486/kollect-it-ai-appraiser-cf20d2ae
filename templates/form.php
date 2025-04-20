
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="expert-appraiser-container">
    <h2 class="expert-appraiser-title"><?php echo esc_html($atts['title']); ?></h2>
    
    <div class="expert-appraiser-card">
        <form id="expert-appraiser-form" class="expert-appraiser-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('expert_appraiser_nonce', 'expert_appraiser_nonce'); ?>
            
            <div class="expert-appraiser-form-group">
                <label class="expert-appraiser-label" for="item_title">
                    <?php _e('Item Title', 'expert-appraiser-ai'); ?>
                </label>
                <input type="text" 
                       id="item_title" 
                       name="item_title" 
                       class="expert-appraiser-input" 
                       required 
                       placeholder="<?php _e('E.g., Antique Silver Tea Set', 'expert-appraiser-ai'); ?>"
                       value="<?php echo isset($_POST['item_title']) ? esc_attr($_POST['item_title']) : ''; ?>"
                />
            </div>

            <div class="expert-appraiser-form-group">
                <label class="expert-appraiser-label" for="item_description">
                    <?php _e('Item Description', 'expert-appraiser-ai'); ?>
                </label>
                <textarea id="item_description" 
                          name="item_description" 
                          class="expert-appraiser-textarea" 
                          required
                          placeholder="<?php _e('Describe the item with as much detail as possible', 'expert-appraiser-ai'); ?>"
                          rows="4"><?php echo isset($_POST['item_description']) ? esc_textarea($_POST['item_description']) : ''; ?></textarea>
            </div>

            <div class="expert-appraiser-form-group">
                <label class="expert-appraiser-label"><?php _e('Appraisal Type', 'expert-appraiser-ai'); ?></label>
                <select id="expert-appraiser-template-select" name="template_id" class="expert-appraiser-select">
                    <?php foreach ($templates as $id => $text) : ?>
                        <option value="<?php echo esc_attr($id); ?>" 
                                <?php selected(isset($_POST['template_id']) ? $_POST['template_id'] : 'standard', $id); ?>>
                            <?php echo esc_html($text); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="expert-appraiser-image-uploader" id="expert-appraiser-image-uploader">
                <div class="expert-appraiser-form-group">
                    <label class="expert-appraiser-label" for="item_image">
                        <?php _e('Upload Image', 'expert-appraiser-ai'); ?>
                    </label>
                    <input type="file" 
                           id="item_image" 
                           name="item_image" 
                           class="expert-appraiser-file-input" 
                           accept="image/*" 
                           required
                    />
                    <div class="expert-appraiser-dropzone" id="expert-appraiser-dropzone">
                        <div class="expert-appraiser-dropzone-message">
                            <?php _e('Drag and drop an image here, or click to select a file', 'expert-appraiser-ai'); ?>
                        </div>
                    </div>
                    <div class="expert-appraiser-image-preview" id="expert-appraiser-image-preview"></div>
                </div>
                <p class="expert-appraiser-hint">
                    <?php _e('Upload a clear, well-lit image of your item. Multiple angles are recommended for more accurate appraisals.', 'expert-appraiser-ai'); ?>
                </p>
            </div>

            <div class="expert-appraiser-form-group">
                <button type="submit" 
                        id="expert-appraiser-submit"
                        name="submit_appraisal" 
                        class="expert-appraiser-button">
                    <?php _e('Generate Appraisal', 'expert-appraiser-ai'); ?>
                </button>
                <div class="expert-appraiser-loading" id="expert-appraiser-loading" style="display: none;">
                    <div class="expert-appraiser-spinner"></div>
                    <p><?php _e('Generating expert appraisal...', 'expert-appraiser-ai'); ?></p>
                </div>
            </div>
        </form>

        <div class="expert-appraiser-results" id="expert-appraiser-results" style="display: none;">
            <h3><?php _e('Expert Appraisal Report', 'expert-appraiser-ai'); ?></h3>
            <div class="expert-appraiser-appraisal-content" id="expert-appraiser-appraisal-content"></div>
            <?php if ($atts['show_save'] !== 'false') : ?>
                <div class="expert-appraiser-actions">
                    <button id="expert-appraiser-save" class="expert-appraiser-button expert-appraiser-button-secondary">
                        <?php _e('Save This Appraisal', 'expert-appraiser-ai'); ?>
                    </button>
                    <button id="expert-appraiser-print" class="expert-appraiser-button expert-appraiser-button-secondary">
                        <?php _e('Print Appraisal', 'expert-appraiser-ai'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
