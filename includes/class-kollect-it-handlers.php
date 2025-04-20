
<?php
class Kollect_It_Handlers {
    private $api;
    
    public function __construct() {
        $this->api = new Kollect_It_API();
    }
    
    public function init() {
        add_action('wp_ajax_kollect_it_generate_appraisal', array($this, 'generate_appraisal'));
        add_action('wp_ajax_nopriv_kollect_it_generate_appraisal', array($this, 'generate_appraisal'));
        add_action('wp_ajax_kollect_it_save_appraisal', array($this, 'save_appraisal'));
        add_action('wp_ajax_nopriv_kollect_it_save_appraisal', array($this, 'save_appraisal'));
    }
    
    public function generate_appraisal() {
        if (!check_ajax_referer('kollect_it_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        $image_data = sanitize_text_field($_POST['image']);
        $template_id = sanitize_text_field($_POST['template_id']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'kollect_it_api_keys';
        $api_key = $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
        
        if (!$api_key) {
            wp_send_json_error(array('message' => 'API key not configured.'));
        }
        
        $image_data = str_replace('data:image/jpeg;base64,', '', $image_data);
        $image_data = str_replace('data:image/png;base64,', '', $image_data);
        
        $prompt_templates = include KOLLECT_IT_PLUGIN_DIR . 'includes/prompt-templates.php';
        $template = $prompt_templates['standard'];
        
        foreach ($prompt_templates as $id => $prompt) {
            if ($id === $template_id) {
                $template = $prompt;
                break;
            }
        }
        
        $response = $this->api->call_openai_api($image_data, $api_key, $template);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }
        
        wp_send_json_success(array(
            'appraisalText' => $response['appraisalText']
        ));
    }
    
    public function save_appraisal() {
        if (!check_ajax_referer('kollect_it_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        $image_data = sanitize_text_field($_POST['image']);
        $appraisal_text = sanitize_textarea_field($_POST['appraisal']);
        $template_id = sanitize_text_field($_POST['template_id']);
        
        $post_id = wp_insert_post(array(
            'post_title' => 'Appraisal ' . date('Y-m-d H:i:s'),
            'post_content' => $appraisal_text,
            'post_status' => 'publish',
            'post_type' => 'kollect_it_appraisal'
        ));
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => $post_id->get_error_message()));
        }
        
        update_post_meta($post_id, '_template_id', $template_id);
        
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
        
        set_post_thumbnail($post_id, $attachment_id);
        
        wp_send_json_success(array(
            'message' => 'Appraisal saved successfully.',
            'post_id' => $post_id
        ));
    }
}
