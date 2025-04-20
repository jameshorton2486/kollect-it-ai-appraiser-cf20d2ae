
<?php
/**
 * Main plugin class
 */
class Kollect_It_Appraiser {
    /**
     * Initialize the plugin
     */
    public function init() {
        // Register AJAX handlers
        add_action('wp_ajax_kollect_it_generate_appraisal', array($this, 'generate_appraisal'));
        add_action('wp_ajax_nopriv_kollect_it_generate_appraisal', array($this, 'generate_appraisal'));
        add_action('wp_ajax_kollect_it_save_appraisal', array($this, 'save_appraisal'));
        add_action('wp_ajax_nopriv_kollect_it_save_appraisal', array($this, 'save_appraisal'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Kollect-It Appraiser', 'kollect-it-appraiser'),
            __('Kollect-It', 'kollect-it-appraiser'),
            'manage_options',
            'kollect-it-appraiser',
            array($this, 'render_admin_page'),
            'dashicons-search',
            30
        );
        
        add_submenu_page(
            'kollect-it-appraiser',
            __('Appraisals', 'kollect-it-appraiser'),
            __('Appraisals', 'kollect-it-appraiser'),
            'manage_options',
            'kollect-it-appraisals',
            array($this, 'render_appraisals_page')
        );
        
        add_submenu_page(
            'kollect-it-appraiser',
            __('Settings', 'kollect-it-appraiser'),
            __('Settings', 'kollect-it-appraiser'),
            'manage_options',
            'kollect-it-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render the main admin page
     */
    public function render_admin_page() {
        include KOLLECT_IT_PLUGIN_DIR . 'admin/admin-page.php';
    }
    
    /**
     * Render the appraisals admin page
     */
    public function render_appraisals_page() {
        include KOLLECT_IT_PLUGIN_DIR . 'admin/appraisals-page.php';
    }
    
    /**
     * Render the settings admin page
     */
    public function render_settings_page() {
        include KOLLECT_IT_PLUGIN_DIR . 'admin/settings-page.php';
    }
    
    /**
     * Handle the appraisal generation via AJAX
     */
    public function generate_appraisal() {
        // Check nonce
        if (!check_ajax_referer('kollect_it_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Get parameters
        $image_data = sanitize_text_field($_POST['image']);
        $template_id = sanitize_text_field($_POST['template_id']);
        
        // Get API key from database
        global $wpdb;
        $table_name = $wpdb->prefix . 'kollect_it_api_keys';
        $api_key = $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
        
        if (!$api_key) {
            wp_send_json_error(array('message' => 'API key not configured.'));
        }
        
        // Process image data if needed
        $image_data = str_replace('data:image/jpeg;base64,', '', $image_data);
        $image_data = str_replace('data:image/png;base64,', '', $image_data);
        
        // Get prompt template
        $prompt_templates = include KOLLECT_IT_PLUGIN_DIR . 'includes/prompt-templates.php';
        $template = $prompt_templates['standard']; // Default
        
        foreach ($prompt_templates as $id => $prompt) {
            if ($id === $template_id) {
                $template = $prompt;
                break;
            }
        }
        
        // Call OpenAI API
        $response = $this->call_openai_api($image_data, $api_key, $template);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }
        
        wp_send_json_success(array(
            'appraisalText' => $response['appraisalText']
        ));
    }
    
    /**
     * Save an appraisal
     */
    public function save_appraisal() {
        // Check nonce
        if (!check_ajax_referer('kollect_it_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Get parameters
        $image_data = sanitize_text_field($_POST['image']);
        $appraisal_text = sanitize_textarea_field($_POST['appraisal']);
        $template_id = sanitize_text_field($_POST['template_id']);
        
        // Create a new post of custom type
        $post_id = wp_insert_post(array(
            'post_title' => 'Appraisal ' . date('Y-m-d H:i:s'),
            'post_content' => $appraisal_text,
            'post_status' => 'publish',
            'post_type' => 'kollect_it_appraisal'
        ));
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => $post_id->get_error_message()));
        }
        
        // Save template ID as meta
        update_post_meta($post_id, '_template_id', $template_id);
        
        // Save image as attachment
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'];
        $upload_url = $upload_dir['url'];
        
        $image_data_decoded = base64_decode(str_replace('data:image/jpeg;base64,', '', $image_data));
        $filename = 'appraisal-image-' . $post_id . '.jpg';
        $file_path = $upload_path . '/' . $filename;
        
        file_put_contents($file_path, $image_data_decoded);
        
        $attachment = array(
            'post_mime_type' => 'image/jpeg',
            'post_title' => 'Appraisal Image ' . $post_id,
            'post_content' => '',
            'post_status' => 'inherit',
            'guid' => $upload_url . '/' . $filename
        );
        
        $attachment_id = wp_insert_attachment($attachment, $file_path, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        // Set as featured image
        set_post_thumbnail($post_id, $attachment_id);
        
        wp_send_json_success(array(
            'message' => 'Appraisal saved successfully.',
            'post_id' => $post_id
        ));
    }
    
    /**
     * Call the OpenAI API
     */
    private function call_openai_api($image_data, $api_key, $prompt_text) {
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => 'gpt-4o-mini',
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => array(
                            array(
                                'type' => 'text',
                                'text' => $prompt_text
                            ),
                            array(
                                'type' => 'image_url',
                                'image_url' => array(
                                    'url' => 'data:image/jpeg;base64,' . $image_data
                                )
                            )
                        )
                    )
                ),
                'max_tokens' => 4000
            )),
            'timeout' => 60
        );
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('api_error', $body['error']['message']);
        }
        
        return array(
            'appraisalText' => $body['choices'][0]['message']['content']
        );
    }
}
