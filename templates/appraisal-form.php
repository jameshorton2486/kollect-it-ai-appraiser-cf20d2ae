
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Verify nonce if form is submitted
if (isset($_POST['submit_appraisal'])) {
    if (!isset($_POST['kollect_it_nonce']) || !wp_verify_nonce($_POST['kollect_it_nonce'], 'kollect_it_appraisal')) {
        wp_die('Security check failed');
    }
}
?>

<div class="kollect-it-container">
    <h2 class="kollect-it-title"><?php echo esc_html($atts['title']); ?></h2>
    
    <div class="kollect-it-card">
        <form id="kollect-it-form" class="kollect-it-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('kollect_it_appraisal', 'kollect_it_nonce'); ?>
            
            <div class="kollect-it-form-group">
                <label class="kollect-it-label" for="item_title">
                    <?php _e('Item Title', 'kollect-it-appraiser'); ?>
                </label>
                <input type="text" 
                       id="item_title" 
                       name="item_title" 
                       class="kollect-it-input" 
                       required 
                       value="<?php echo isset($_POST['item_title']) ? esc_attr($_POST['item_title']) : ''; ?>"
                />
            </div>

            <div class="kollect-it-form-group">
                <label class="kollect-it-label" for="item_description">
                    <?php _e('Item Description', 'kollect-it-appraiser'); ?>
                </label>
                <textarea id="item_description" 
                          name="item_description" 
                          class="kollect-it-textarea" 
                          required
                          rows="4"><?php echo isset($_POST['item_description']) ? esc_textarea($_POST['item_description']) : ''; ?></textarea>
            </div>

            <div class="kollect-it-form-group">
                <label class="kollect-it-label"><?php _e('Appraisal Type', 'kollect-it-appraiser'); ?></label>
                <select id="kollect-it-template-select" name="template_id" class="kollect-it-select">
                    <?php foreach ($prompt_templates as $id => $text) : ?>
                        <option value="<?php echo esc_attr($id); ?>" 
                                <?php selected(isset($_POST['template_id']) ? $_POST['template_id'] : 'standard', $id); ?>>
                            <?php echo esc_html(ucfirst($id)); ?> <?php _e('Appraisal', 'kollect-it-appraiser'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="kollect-it-image-uploader" id="kollect-it-image-uploader">
                <div class="kollect-it-form-group">
                    <label class="kollect-it-label" for="item_image">
                        <?php _e('Upload Image', 'kollect-it-appraiser'); ?>
                    </label>
                    <input type="file" 
                           id="item_image" 
                           name="item_image" 
                           class="kollect-it-file-input" 
                           accept="image/*" 
                           required
                    />
                    <div class="kollect-it-image-preview" id="kollect-it-image-preview"></div>
                </div>
            </div>

            <div class="kollect-it-form-group">
                <button type="submit" 
                        name="submit_appraisal" 
                        class="kollect-it-button">
                    <?php _e('Get Appraisal', 'kollect-it-appraiser'); ?>
                </button>
            </div>
        </form>

        <?php if (isset($_POST['submit_appraisal']) && isset($response)): ?>
            <div class="kollect-it-results">
                <h3><?php _e('Appraisal Results', 'kollect-it-appraiser'); ?></h3>
                <div class="kollect-it-appraisal-content">
                    <?php echo wp_kses_post($response); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

