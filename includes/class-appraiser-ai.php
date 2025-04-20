
<?php
class Appraiser_AI {
    private $api_key_manager;
    private $image_processor;
    private $openai_client;
    
    public function __construct() {
        $this->api_key_manager = new Appraiser_API_Key_Manager();
        $this->image_processor = new Appraiser_Image_Processor();
    }
    
    public function init() {
        add_action('wp_ajax_expert_appraiser_generate', array($this, 'generate_appraisal'));
        add_action('wp_ajax_nopriv_expert_appraiser_generate', array($this, 'generate_appraisal'));
        add_action('wp_ajax_expert_appraiser_save', array($this, 'save_appraisal'));
        add_action('wp_ajax_nopriv_expert_appraiser_save', array($this, 'save_appraisal'));
    }
    
    public function generate_appraisal() {
        if (!check_ajax_referer('expert_appraiser_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        $image_data = isset($_POST['image']) ? sanitize_text_field($_POST['image']) : '';
        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : 'standard';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        
        if (empty($image_data)) {
            wp_send_json_error(array('message' => 'No image data provided.'));
        }
        
        $api_key = $this->api_key_manager->get_api_key();
        if (!$api_key) {
            wp_send_json_error(array('message' => 'API key not configured.'));
        }
        
        $this->openai_client = new Appraiser_OpenAI_Client($api_key);
        
        // Process image data
        $cleaned_image = $this->image_processor->clean_image_data($image_data);
        
        // Get and customize prompt
        $prompt = $this->get_prompt_template($template_id);
        if (!empty($title) || !empty($description)) {
            $prompt = $this->customize_prompt($prompt, $title, $description);
        }
        
        // Generate appraisal
        $response = $this->openai_client->generate_appraisal($cleaned_image, $prompt);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => $response->get_error_message(),
                'code' => $response->get_error_code()
            ));
        }
        
        $this->log_appraisal_request($template_id, $response['metadata']);
        
        wp_send_json_success($response);
    }
    
    public function save_appraisal() {
        if (!check_ajax_referer('expert_appraiser_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : 'Unnamed Appraisal';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $image_data = isset($_POST['image']) ? sanitize_text_field($_POST['image']) : '';
        $appraisal_text = isset($_POST['appraisal']) ? wp_kses_post($_POST['appraisal']) : '';
        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : 'standard';
        
        if (empty($appraisal_text)) {
            wp_send_json_error(array('message' => 'No appraisal content to save.'));
        }
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $appraisal_text,
            'post_excerpt' => $description,
            'post_status' => 'publish',
            'post_type' => 'appraisal',
            'meta_input' => array(
                '_appraisal_template' => $template_id,
                '_appraisal_date' => current_time('mysql'),
            )
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => $post_id->get_error_message()));
            return;
        }
        
        if (!empty($image_data)) {
            $attachment_id = $this->image_processor->save_image_attachment($post_id, $image_data);
            if (is_wp_error($attachment_id)) {
                error_log('Error saving appraisal image: ' . $attachment_id->get_error_message());
            }
        }
        
        wp_send_json_success(array(
            'message' => 'Appraisal saved successfully.',
            'post_id' => $post_id,
            'view_url' => get_permalink($post_id)
        ));
    }
    
    private function get_prompt_template($template_id) {
        $prompt_file = EXPERT_APPRAISER_PLUGIN_DIR . 'prompts/expert-appraisal.txt';
        
        if (file_exists($prompt_file)) {
            $standard_prompt = file_get_contents($prompt_file);
        } else {
            $standard_prompt = 'You are an expert appraiser with over 30 years of experience appraising antiques, collectibles, and art. Please provide a detailed appraisal for the item in this image.';
        }
        
        $templates = include EXPERT_APPRAISER_PLUGIN_DIR . 'includes/prompt-templates.php';
        return isset($templates[$template_id]) ? $templates[$template_id] : $standard_prompt;
    }
    
    private function customize_prompt($prompt, $title, $description) {
        if (!empty($title) || !empty($description)) {
            $prompt .= "\n\nAdditional Item Information:";
            if (!empty($title)) {
                $prompt .= "\nTitle: " . $title;
            }
            if (!empty($description)) {
                $prompt .= "\nDescription: " . $description;
            }
        }
        return $prompt;
    }
    
    private function log_appraisal_request($template_id, $metadata = array()) {
        $log = get_option('expert_appraiser_usage_log', array());
        
        $log[] = array(
            'date' => current_time('mysql'),
            'template' => $template_id,
            'model' => isset($metadata['model']) ? $metadata['model'] : 'unknown',
            'tokens' => isset($metadata['totalTokens']) ? $metadata['totalTokens'] : 0
        );
        
        if (count($log) > 100) {
            $log = array_slice($log, -100);
        }
        
        update_option('expert_appraiser_usage_log', $log);
    }
}
