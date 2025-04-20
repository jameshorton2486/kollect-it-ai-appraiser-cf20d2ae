
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap expert-appraiser-admin-container">
    <div class="expert-appraiser-admin-header">
        <h1><?php _e('Expert Appraiser Settings', 'expert-appraiser-ai'); ?></h1>
    </div>
    
    <div class="expert-appraiser-admin-card">
        <h2><?php _e('API Configuration', 'expert-appraiser-ai'); ?></h2>
        <p><?php _e('Configure your OpenAI API key to enable image appraisals.', 'expert-appraiser-ai'); ?></p>
        
        <form id="expert-appraiser-api-key-form" class="expert-appraiser-admin-form">
            <?php wp_nonce_field('expert_appraiser_admin_nonce', 'expert_appraiser_admin_nonce'); ?>
            
            <div class="expert-appraiser-admin-form-row">
                <label for="expert-appraiser-api-key" class="expert-appraiser-admin-label">
                    <?php _e('OpenAI API Key', 'expert-appraiser-ai'); ?>
                </label>
                <input
                    type="password"
                    id="expert-appraiser-api-key"
                    name="api_key"
                    class="regular-text expert-appraiser-admin-field"
                    value="<?php echo esc_attr(Appraiser_Admin::get_api_key()); ?>"
                />
                <p class="expert-appraiser-admin-description">
                    <?php _e('Enter your OpenAI API key. You can get one from the OpenAI dashboard.', 'expert-appraiser-ai'); ?>
                </p>
            </div>
            
            <div class="expert-appraiser-admin-form-row">
                <input
                    type="submit"
                    id="expert-appraiser-api-key-submit"
                    class="button button-primary"
                    value="<?php _e('Save API Key', 'expert-appraiser-ai'); ?>"
                />
                <button
                    id="expert-appraiser-test-api-key"
                    class="button"
                    style="margin-left: 10px;"
                >
                    <?php _e('Test API Key', 'expert-appraiser-ai'); ?>
                </button>
            </div>
        </form>
    </div>
    
    <div class="expert-appraiser-admin-card">
        <h2><?php _e('OpenAI Model Settings', 'expert-appraiser-ai'); ?></h2>
        <p><?php _e('Configure which OpenAI model to use for image appraisals.', 'expert-appraiser-ai'); ?></p>
        
        <form method="post" action="options.php" class="expert-appraiser-admin-form">
            <?php settings_fields('expert_appraiser_settings'); ?>
            
            <div class="expert-appraiser-admin-form-row">
                <label for="expert_appraiser_openai_model" class="expert-appraiser-admin-label">
                    <?php _e('OpenAI Model', 'expert-appraiser-ai'); ?>
                </label>
                <select
                    id="expert_appraiser_openai_model"
                    name="expert_appraiser_openai_model"
                    class="expert-appraiser-admin-field"
                >
                    <option value="gpt-4o-mini" <?php selected(get_option('expert_appraiser_openai_model'), 'gpt-4o-mini'); ?>>
                        gpt-4o-mini (Faster, more affordable)
                    </option>
                    <option value="gpt-4o" <?php selected(get_option('expert_appraiser_openai_model'), 'gpt-4o'); ?>>
                        gpt-4o (Better quality, higher cost)
                    </option>
                </select>
                <p class="expert-appraiser-admin-description">
                    <?php _e('Select which OpenAI model to use. More powerful models provide better accuracy but cost more.', 'expert-appraiser-ai'); ?>
                </p>
            </div>
            
            <div class="expert-appraiser-admin-form-row">
                <?php submit_button(__('Save Settings', 'expert-appraiser-ai')); ?>
            </div>
        </form>
    </div>
</div>
