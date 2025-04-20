
<?php
/**
 * Plugin Settings
 */
class Kollect_It_Settings {
    /**
     * Initialize settings
     */
    public function init() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_kollect_it_save_api_key', array($this, 'save_api_key'));
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('kollect_it_settings', 'kollect_it_openai_model', array(
            'type' => 'string',
            'default' => 'gpt-4o-mini',
            'sanitize_callback' => 'sanitize_text_field'
        ));
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
        if (!check_ajax_referer('kollect_it_admin_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        
        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'API key cannot be empty.'));
        }
        
        // Store in the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'kollect_it_api_keys';
        
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
     * Get API key
     */
    public static function get_api_key() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kollect_it_api_keys';
        return $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
    }
}
