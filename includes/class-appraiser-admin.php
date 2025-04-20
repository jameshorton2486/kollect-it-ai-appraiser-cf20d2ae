
<?php
/**
 * Admin functionality for Expert Appraiser AI
 */
class Appraiser_Admin {
    /**
     * Initialize admin functionality
     */
    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // AJAX handlers for admin
        add_action('wp_ajax_expert_appraiser_save_api_key', array($this, 'save_api_key'));
        add_action('wp_ajax_expert_appraiser_test_api_key', array($this, 'test_api_key'));
    }
    
    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Expert Appraiser AI', 'expert-appraiser-ai'),
            __('Expert Appraiser', 'expert-appraiser-ai'),
            'manage_options',
            'expert-appraiser',
            array($this, 'render_admin_page'),
            'dashicons-search',
            30
        );
        
        add_submenu_page(
            'expert-appraiser',
            __('Appraisals', 'expert-appraiser-ai'),
            __('Appraisals', 'expert-appraiser-ai'),
            'manage_options',
            'expert-appraiser-items',
            array($this, 'render_appraisals_page')
        );
        
        add_submenu_page(
            'expert-appraiser',
            __('Settings', 'expert-appraiser-ai'),
            __('Settings', 'expert-appraiser-ai'),
            'manage_options',
            'expert-appraiser-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('expert_appraiser_settings', 'expert_appraiser_openai_model', array(
            'type' => 'string',
            'default' => 'gpt-4o-mini',
            'sanitize_callback' => 'sanitize_text_field'
        ));
    }
    
    /**
     * Render the main admin page
     */
    public function render_admin_page() {
        include EXPERT_APPRAISER_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }
    
    /**
     * Render the appraisals admin page
     */
    public function render_appraisals_page() {
        include EXPERT_APPRAISER_PLUGIN_DIR . 'templates/admin-appraisals.php';
    }
    
    /**
     * Render the settings admin page
     */
    public function render_settings_page() {
        include EXPERT_APPRAISER_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    /**
     * Save API key
     */
    public function save_api_key() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        // Check nonce
        if (!check_ajax_referer('expert_appraiser_admin_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'API key cannot be empty.'));
        }
        
        // Store in the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
        
        // Clear existing keys
        $wpdb->query("TRUNCATE TABLE $table_name");
        
        // Insert new key
        $result = $wpdb->insert(
            $table_name,
            array('api_key' => $api_key),
            array('%s')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to save API key.'));
        }
        
        wp_send_json_success(array('message' => 'API key saved successfully.'));
    }
    
    /**
     * Test API key
     */
    public function test_api_key() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        // Check nonce
        if (!check_ajax_referer('expert_appraiser_admin_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Get API key from POST or database
        $api_key = '';
        if (!empty($_POST['api_key'])) {
            $api_key = sanitize_text_field($_POST['api_key']);
        } else {
            global $wpdb;
            $table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
            $api_key = $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
        }
        
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'No API key provided.'));
        }
        
        // Simple test request to OpenAI
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => 'gpt-4o-mini',
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => 'Say "API key is working" in 5 words or less'
                    )
                ),
                'max_tokens' => 20
            )),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'API test failed: ' . $response->get_error_message()));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            wp_send_json_error(array('message' => 'API test failed: ' . $body['error']['message']));
        }
        
        wp_send_json_success(array('message' => 'API key is valid and working.'));
    }
    
    /**
     * Get API key
     */
    public static function get_api_key() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
        return $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
    }
}
