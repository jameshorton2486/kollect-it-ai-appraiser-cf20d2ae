
<?php
/**
 * Class for handling OpenAI GPT-4 Vision integration
 */
class Expert_Appraiser_AI {
    private $api_endpoint = 'https://api.openai.com/v1/chat/completions';
    private $api_key_manager;
    
    public function __construct() {
        $this->api_key_manager = new Appraiser_API_Key_Manager();
    }
    
    /**
     * Generate an appraisal based on the provided image
     */
    public function generate_appraisal($image_data, $user_notes = '') {
        $api_key = $this->api_key_manager->get_api_key();
        if (!$api_key) {
            return new WP_Error('no_api_key', __('OpenAI API key is not configured', 'expert-appraiser-ai'));
        }
        
        // Clean the base64 image data
        $image_data = $this->clean_base64_image($image_data);
        if (empty($image_data)) {
            return new WP_Error('invalid_image', __('Invalid image data', 'expert-appraiser-ai'));
        }
        
        // Get the expert prompt
        $prompt = $this->get_expert_prompt();
        if (!empty($user_notes)) {
            $prompt .= "\n\nAdditional notes about this item: " . $user_notes;
        }
        
        // Prepare the API request with the correct model
        $payload = array(
            'model' => 'gpt-4o-mini', // Using the recommended faster model
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => array(
                        array(
                            'type' => 'text',
                            'text' => $prompt
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
        );
        
        // Make the API request with proper error handling
        $response = wp_remote_post($this->api_endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($payload),
            'timeout' => 60
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            return new WP_Error(
                'api_error',
                isset($body['error']['message']) ? $body['error']['message'] : __('Unknown API error', 'expert-appraiser-ai'),
                array('status' => $response_code)
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($body['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', __('Invalid response from OpenAI', 'expert-appraiser-ai'));
        }
        
        $appraisal_text = $body['choices'][0]['message']['content'];
        
        // Save the appraisal if user is logged in
        if (is_user_logged_in()) {
            $this->save_appraisal($appraisal_text, $image_data);
        }
        
        return array(
            'appraisalText' => $appraisal_text,
            'metadata' => array(
                'model' => $payload['model'],
                'totalTokens' => isset($body['usage']['total_tokens']) ? $body['usage']['total_tokens'] : null,
            )
        );
    }
    
    /**
     * Get the expert prompt from file
     */
    private function get_expert_prompt() {
        $prompt_file = EXPERT_APPRAISER_PLUGIN_DIR . 'prompts/expert-appraisal.txt';
        if (file_exists($prompt_file)) {
            return file_get_contents($prompt_file);
        }
        return 'You are an expert appraiser. Please analyze this image and provide a comprehensive professional appraisal.';
    }
    
    private function clean_base64_image($image_data) {
        if (strpos($image_data, 'data:image') === 0) {
            $image_data = preg_replace('/^data:image\/\w+;base64,/', '', $image_data);
        }
        return $image_data;
    }
    
    private function save_appraisal($appraisal_text, $image_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expert_appraisals';
        
        // Create appraisals directory if needed
        $upload_dir = wp_upload_dir();
        $appraisal_dir = $upload_dir['basedir'] . '/appraisals';
        if (!file_exists($appraisal_dir)) {
            wp_mkdir_p($appraisal_dir);
        }
        
        // Generate unique filename and save image
        $filename = 'appraisal-' . md5(uniqid() . time()) . '.jpg';
        $file_path = $appraisal_dir . '/' . $filename;
        file_put_contents($file_path, base64_decode($image_data));
        
        // Extract item name from appraisal text
        preg_match('/ITEM IDENTIFICATION[:\s]*(.+?)(?:\n|$)/i', $appraisal_text, $matches);
        $item_name = !empty($matches[1]) ? trim($matches[1]) : __('Appraisal', 'expert-appraiser-ai');
        
        // Save to database
        return $wpdb->insert(
            $table_name,
            array(
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'item_name' => $item_name,
                'appraisal_text' => $appraisal_text,
                'image_path' => 'appraisals/' . $filename
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
    }
}
