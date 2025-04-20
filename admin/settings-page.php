
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap kollect-it-admin-container">
    <div class="kollect-it-admin-header">
        <h1><?php _e('Kollect-It Settings', 'kollect-it-appraiser'); ?></h1>
    </div>
    
    <div class="kollect-it-admin-card">
        <h2><?php _e('API Configuration', 'kollect-it-appraiser'); ?></h2>
        <p><?php _e('Configure your OpenAI API key to enable image appraisals.', 'kollect-it-appraiser'); ?></p>
        
        <form id="kollect-it-api-key-form" class="kollect-it-admin-form">
            <?php wp_nonce_field('kollect_it_admin_nonce', 'kollect_it_admin_nonce'); ?>
            
            <div class="kollect-it-admin-form-row">
                <label for="kollect-it-api-key" class="kollect-it-admin-label">
                    <?php _e('OpenAI API Key', 'kollect-it-appraiser'); ?>
                </label>
                <input
                    type="password"
                    id="kollect-it-api-key"
                    name="api_key"
                    class="regular-text kollect-it-admin-field"
                    value="<?php echo esc_attr(Kollect_It_Settings::get_api_key()); ?>"
                />
                <p class="kollect-it-admin-description">
                    <?php _e('Enter your OpenAI API key. You can get one from the OpenAI dashboard.', 'kollect-it-appraiser'); ?>
                </p>
            </div>
            
            <div class="kollect-it-admin-form-row">
                <input
                    type="submit"
                    id="kollect-it-api-key-submit"
                    class="button button-primary"
                    value="<?php _e('Save API Key', 'kollect-it-appraiser'); ?>"
                />
                <button
                    id="kollect-it-test-api-key"
                    class="button"
                    style="margin-left: 10px;"
                >
                    <?php _e('Test API Key', 'kollect-it-appraiser'); ?>
                </button>
            </div>
        </form>
    </div>
    
    <div class="kollect-it-admin-card">
        <h2><?php _e('OpenAI Model Settings', 'kollect-it-appraiser'); ?></h2>
        <p><?php _e('Configure which OpenAI model to use for image appraisals.', 'kollect-it-appraiser'); ?></p>
        
        <form method="post" action="options.php" class="kollect-it-admin-form">
            <?php settings_fields('kollect_it_settings'); ?>
            
            <div class="kollect-it-admin-form-row">
                <label for="kollect_it_openai_model" class="kollect-it-admin-label">
                    <?php _e('OpenAI Model', 'kollect-it-appraiser'); ?>
                </label>
                <select
                    id="kollect_it_openai_model"
                    name="kollect_it_openai_model"
                    class="kollect-it-admin-field"
                >
                    <option value="gpt-4o-mini" <?php selected(get_option('kollect_it_openai_model'), 'gpt-4o-mini'); ?>>
                        gpt-4o-mini (Faster, cheaper)
                    </option>
                    <option value="gpt-4o" <?php selected(get_option('kollect_it_openai_model'), 'gpt-4o'); ?>>
                        gpt-4o (Better quality, more expensive)
                    </option>
                </select>
                <p class="kollect-it-admin-description">
                    <?php _e('Select which OpenAI model to use. More powerful models provide better accuracy but cost more.', 'kollect-it-appraiser'); ?>
                </p>
            </div>
            
            <div class="kollect-it-admin-form-row">
                <?php submit_button(__('Save Settings', 'kollect-it-appraiser')); ?>
            </div>
        </form>
    </div>
</div>
