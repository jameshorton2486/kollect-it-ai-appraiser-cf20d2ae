
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle API key submission
function expert_appraiser_handle_env_api_key() {
    // Check if form is submitted
    if (isset($_POST['expert_appraiser_env_api_key_nonce']) && 
        wp_verify_nonce($_POST['expert_appraiser_env_api_key_nonce'], 'expert_appraiser_env_api_key')) {
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'expert-appraiser-ai'));
        }
        
        // Get the API key
        $api_key = isset($_POST['env_api_key']) ? sanitize_text_field($_POST['env_api_key']) : '';
        
        // Validate key format
        if (empty($api_key) || !preg_match('/^sk-/', $api_key)) {
            add_settings_error(
                'expert_appraiser_env_api_key',
                'invalid-api-key',
                __('Invalid API key format. OpenAI API keys should start with "sk-".', 'expert-appraiser-ai'),
                'error'
            );
            return;
        }
        
        // Save key to .env file
        $api_key_manager = new Appraiser_API_Key_Manager();
        $result = $api_key_manager->store_api_key_in_env($api_key);
        
        if ($result) {
            add_settings_error(
                'expert_appraiser_env_api_key',
                'api-key-saved',
                __('API key saved successfully to .env file.', 'expert-appraiser-ai'),
                'success'
            );
            
            // Reload environment variables
            Appraiser_Env_Loader::load();
        } else {
            add_settings_error(
                'expert_appraiser_env_api_key',
                'api-key-error',
                __('Failed to save API key. Please check file permissions for the .env file.', 'expert-appraiser-ai'),
                'error'
            );
        }
    }
}
add_action('admin_init', 'expert_appraiser_handle_env_api_key');

// Display the form for setting API key in .env file
function expert_appraiser_display_env_api_key_form() {
    // Load environment variables
    Appraiser_Env_Loader::load();
    
    // Get current value
    $current_key = Appraiser_Env_Loader::get('OPENAI_API_KEY', '');
    $has_key = !empty($current_key);
    
    // Display form
    ?>
    <div class="expert-appraiser-admin-card">
        <h2><?php _e('.env API Key Configuration', 'expert-appraiser-ai'); ?></h2>
        <p><?php _e('Set your OpenAI API key securely in a .env file.', 'expert-appraiser-ai'); ?></p>
        
        <?php settings_errors('expert_appraiser_env_api_key'); ?>
        
        <form method="post" action="" class="expert-appraiser-admin-form">
            <?php wp_nonce_field('expert_appraiser_env_api_key', 'expert_appraiser_env_api_key_nonce'); ?>
            
            <div class="expert-appraiser-admin-form-row">
                <label for="env_api_key" class="expert-appraiser-admin-label">
                    <?php _e('OpenAI API Key', 'expert-appraiser-ai'); ?>
                </label>
                <input
                    type="password"
                    id="env_api_key"
                    name="env_api_key"
                    class="regular-text expert-appraiser-admin-field"
                    value="<?php echo esc_attr($has_key ? '************' : ''); ?>"
                    placeholder="<?php echo $has_key ? '' : 'Enter your OpenAI API key'; ?>"
                />
                <p class="expert-appraiser-admin-description">
                    <?php 
                    if ($has_key) {
                        _e('API key is already set in .env file. Enter a new value to change it.', 'expert-appraiser-ai');
                    } else {
                        _e('Enter your OpenAI API key to store it in the .env file.', 'expert-appraiser-ai');
                    }
                    ?>
                </p>
            </div>
            
            <div class="expert-appraiser-admin-form-row">
                <input
                    type="submit"
                    class="button button-primary"
                    value="<?php _e('Save to .env File', 'expert-appraiser-ai'); ?>"
                />
            </div>
        </form>
    </div>
    <?php
}
