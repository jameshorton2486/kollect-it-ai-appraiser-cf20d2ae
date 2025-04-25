
<?php
/**
 * Class for handling OpenAI GPT-4 Vision integration
 */
class Expert_Appraiser_AI {
    private $api_endpoint = 'https://api.openai.com/v1/chat/completions';
    private $openai_client;
    
    public function __construct() {
        // Load environment variables first
        Appraiser_Env_Loader::load();
        
        $api_key_manager = new Appraiser_API_Key_Manager();
        $api_key = $api_key_manager->get_api_key();
        $this->openai_client = new Appraiser_OpenAI_Client($api_key);
    }
    
    /**
     * Generate an appraisal based on the provided image
     */
    public function generate_appraisal($image_data, $user_notes = '') {
        // Check if we have a valid API key first
        if (!$this->openai_client->has_valid_api_key()) {
            return new WP_Error(
                'no_api_key', 
                __('OpenAI API key is not configured or is invalid. Please check settings and ensure the API key begins with "sk-".', 'expert-appraiser-ai')
            );
        }
        
        // Clean the base64 image data
        $image_data = $this->clean_base64_image($image_data);
        if (empty($image_data)) {
            return new WP_Error('invalid_image', __('Invalid image data. Please upload a valid image.', 'expert-appraiser-ai'));
        }
        
        // Get the expert prompt
        $prompt = $this->get_expert_prompt();
        if (!empty($user_notes)) {
            $prompt .= "\n\nAdditional notes about this item: " . $user_notes;
        }
        
        // Use our enhanced client to make the API request
        $result = $this->openai_client->generate_appraisal($image_data, $prompt);
        
        if (is_wp_error($result)) {
            // Log the error for debugging
            if (WP_DEBUG) {
                error_log('OpenAI API Error: ' . $result->get_error_message());
            }
            return $result;
        }
        
        // Save the appraisal if user is logged in
        if (is_user_logged_in()) {
            $this->save_appraisal($result['appraisalText'], $image_data);
        }
        
        return $result;
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
    
    /**
     * Clean base64 image data by removing data URL prefix if present
     */
    private function clean_base64_image($image_data) {
        if (strpos($image_data, 'data:image') === 0) {
            $image_data = preg_replace('/^data:image\/\w+;base64,/', '', $image_data);
        }
        return $image_data;
    }
    
    /**
     * Save appraisal to database
     */
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
        
        // Check if image data is valid base64
        $decoded = base64_decode($image_data, true);
        if ($decoded === false) {
            if (WP_DEBUG) {
                error_log('Failed to decode image data for appraisal');
            }
            return false;
        }
        
        $success = file_put_contents($file_path, $decoded);
        if ($success === false) {
            if (WP_DEBUG) {
                error_log('Failed to save image file for appraisal');
            }
            return false;
        }
        
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
    
    /**
     * Test if an API key is valid
     * 
     * @param string $api_key API key to test
     * @return bool|WP_Error True if valid, WP_Error otherwise
     */
    public function test_api_key($api_key) {
        // Basic validation first
        if (empty($api_key) || !preg_match('/^sk-/', $api_key)) {
            return new WP_Error(
                'invalid_api_key', 
                __('Invalid API key format. OpenAI API keys should start with "sk-".', 'expert-appraiser-ai')
            );
        }
        
        // Create a temporary client with this key for testing
        $client = new Appraiser_OpenAI_Client($api_key);
        return $client->test_api_key();
    }
}
