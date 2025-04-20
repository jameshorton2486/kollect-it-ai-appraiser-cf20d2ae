
<?php
/**
 * Main plugin class for handling OpenAI Vision API integration
 */
class Appraiser_AI {
    /**
     * Initialize the plugin
     */
    public function init() {
        // Register AJAX handlers
        add_action('wp_ajax_expert_appraiser_generate', array($this, 'generate_appraisal'));
        add_action('wp_ajax_nopriv_expert_appraiser_generate', array($this, 'generate_appraisal'));
        add_action('wp_ajax_expert_appraiser_save', array($this, 'save_appraisal'));
        add_action('wp_ajax_nopriv_expert_appraiser_save', array($this, 'save_appraisal'));
    }
    
    /**
     * Handle the appraisal generation via AJAX
     */
    public function generate_appraisal() {
        // Check nonce for security
        if (!check_ajax_referer('expert_appraiser_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'));
        }
        
        // Get parameters
        $image_data = isset($_POST['image']) ? sanitize_text_field($_POST['image']) : '';
        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : 'standard';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        
        // Validate image data
        if (empty($image_data)) {
            wp_send_json_error(array('message' => 'No image data provided.'));
        }
        
        // Get API key securely
        $api_key = $this->get_api_key();
        
        if (!$api_key) {
            wp_send_json_error(array('message' => 'API key not configured. Please add your OpenAI API key in the settings.'));
        }
        
        // Process image data
        $image_data = $this->clean_image_data($image_data);
        
        // Get prompt template
        $prompt = $this->get_prompt_template($template_id);
        
        // Add title and description to prompt if provided
        if (!empty($title) || !empty($description)) {
            $prompt = $this->customize_prompt($prompt, $title, $description);
        }
        
        // Call OpenAI API
        $response = $this->call_openai_api($image_data, $api_key, $prompt);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => $response->get_error_message(),
                'code' => $response->get_error_code()
            ));
        }
        
        // Log successful appraisal (for admin metrics)
        $this->log_appraisal_request($template_id, !empty($response['metadata']) ? $response['metadata'] : array());
        
        wp_send_json_success(array(
            'appraisalText' => $response['appraisalText'],
            'metadata' => !empty($response['metadata']) ? $response['metadata'] : null
        ));
    }
    
    /**
     * Save an appraisal
     */
    public function save_appraisal() {
        // Check nonce
        if (!check_ajax_referer('expert_appraiser_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'));
        }
        
        // Get parameters
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : 'Unnamed Appraisal';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $image_data = isset($_POST['image']) ? sanitize_text_field($_POST['image']) : '';
        $appraisal_text = isset($_POST['appraisal']) ? wp_kses_post($_POST['appraisal']) : '';
        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : 'standard';
        
        // Validate appraisal text
        if (empty($appraisal_text)) {
            wp_send_json_error(array('message' => 'No appraisal content to save.'));
        }
        
        // Prepare data for saving
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
        
        // Insert post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => $post_id->get_error_message()));
            return;
        }
        
        // Save image as attachment if provided
        if (!empty($image_data)) {
            $attachment_id = $this->save_image_attachment($post_id, $image_data);
            
            if (is_wp_error($attachment_id)) {
                // Continue even if image saving fails, just log the error
                error_log('Error saving appraisal image: ' . $attachment_id->get_error_message());
            }
        }
        
        wp_send_json_success(array(
            'message' => 'Appraisal saved successfully.',
            'post_id' => $post_id,
            'view_url' => get_permalink($post_id)
        ));
    }
    
    /**
     * Get API key from database
     */
    private function get_api_key() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
        
        // Get API key with error handling
        $api_key = $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
        
        // Check for database errors
        if ($wpdb->last_error) {
            error_log('Database error retrieving API key: ' . $wpdb->last_error);
            return false;
        }
        
        return $api_key;
    }
    
    /**
     * Clean image data - remove data URL prefix if present
     */
    private function clean_image_data($image_data) {
        // Remove data URL prefix if present
        if (strpos($image_data, 'data:image/jpeg;base64,') === 0) {
            return substr($image_data, strlen('data:image/jpeg;base64,'));
        } elseif (strpos($image_data, 'data:image/png;base64,') === 0) {
            return substr($image_data, strlen('data:image/png;base64,'));
        } elseif (strpos($image_data, 'data:image/') === 0) {
            // Handle any image type
            $start = strpos($image_data, 'base64,');
            if ($start !== false) {
                return substr($image_data, $start + 7);
            }
        }
        
        return $image_data;
    }
    
    /**
     * Save uploaded image as attachment
     */
    private function save_image_attachment($post_id, $image_data) {
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'];
        $upload_url = $upload_dir['url'];
        
        // Process image data
        $image_data = $this->clean_image_data($image_data);
        $image_data_decoded = base64_decode($image_data);
        
        if (!$image_data_decoded) {
            return new WP_Error('invalid_image', 'Invalid image data');
        }
        
        // Create unique filename
        $filename = 'appraisal-' . $post_id . '-' . time() . '.jpg';
        $file_path = $upload_path . '/' . $filename;
        
        // Write file to disk
        $result = file_put_contents($file_path, $image_data_decoded);
        
        if (!$result) {
            return new WP_Error('file_save_failed', 'Failed to save image file');
        }
        
        // Prepare attachment data
        $attachment = array(
            'post_mime_type' => 'image/jpeg',
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit',
            'guid' => $upload_url . '/' . $filename
        );
        
        // Insert attachment
        $attachment_id = wp_insert_attachment($attachment, $file_path, $post_id);
        
        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }
        
        // Generate attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        // Set as featured image
        set_post_thumbnail($post_id, $attachment_id);
        
        return $attachment_id;
    }
    
    /**
     * Get prompt template
     */
    private function get_prompt_template($template_id) {
        $prompt_file = EXPERT_APPRAISER_PLUGIN_DIR . 'prompts/expert-appraisal.txt';
        
        if (file_exists($prompt_file)) {
            $standard_prompt = file_get_contents($prompt_file);
        } else {
            // Default prompt if file doesn't exist
            $standard_prompt = 'You are an expert appraiser with over 30 years of experience appraising antiques, collectibles, and art. Please provide a detailed appraisal for the item in this image.';
        }
        
        // Get prompt templates
        $templates = include EXPERT_APPRAISER_PLUGIN_DIR . 'includes/prompt-templates.php';
        
        // Return the requested template or fall back to standard
        return isset($templates[$template_id]) ? $templates[$template_id] : $standard_prompt;
    }
    
    /**
     * Customize prompt with title and description
     */
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
    
    /**
     * Call the OpenAI API
     */
    private function call_openai_api($image_data, $api_key, $prompt_text) {
        // Get model from settings
        $model = get_option('expert_appraiser_openai_model', 'gpt-4o-mini');
        
        // Set up the API request
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => $model,
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
            'timeout' => 60  // 60-second timeout
        );
        
        // Make the request
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);
        
        // Check for errors
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Get response code
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Unknown API error';
            
            return new WP_Error(
                'api_error_' . $response_code,
                $error_message,
                array('status' => $response_code)
            );
        }
        
        // Process the response
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($body['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', 'Invalid response from OpenAI API');
        }
        
        // Return the appraisal text and metadata
        return array(
            'appraisalText' => $body['choices'][0]['message']['content'],
            'metadata' => array(
                'model' => $model,
                'promptTokens' => isset($body['usage']['prompt_tokens']) ? $body['usage']['prompt_tokens'] : null,
                'completionTokens' => isset($body['usage']['completion_tokens']) ? $body['usage']['completion_tokens'] : null,
                'totalTokens' => isset($body['usage']['total_tokens']) ? $body['usage']['total_tokens'] : null
            )
        );
    }
    
    /**
     * Log appraisal request for admin metrics
     */
    private function log_appraisal_request($template_id, $metadata = array()) {
        // Get existing log
        $log = get_option('expert_appraiser_usage_log', array());
        
        // Add new entry
        $log[] = array(
            'date' => current_time('mysql'),
            'template' => $template_id,
            'model' => isset($metadata['model']) ? $metadata['model'] : 'unknown',
            'tokens' => isset($metadata['totalTokens']) ? $metadata['totalTokens'] : 0
        );
        
        // Limit log size to last 100 entries
        if (count($log) > 100) {
            $log = array_slice($log, -100);
        }
        
        // Update log
        update_option('expert_appraiser_usage_log', $log);
    }
}
