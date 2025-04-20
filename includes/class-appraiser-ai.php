
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
        // Check nonce
        if (!check_ajax_referer('expert_appraiser_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Get parameters
        $image_data = sanitize_text_field($_POST['image']);
        $template_id = sanitize_text_field($_POST['template_id']);
        
        // Get API key
        $api_key = $this->get_api_key();
        
        if (!$api_key) {
            wp_send_json_error(array('message' => 'API key not configured.'));
        }
        
        // Process image data if needed
        $image_data = str_replace('data:image/jpeg;base64,', '', $image_data);
        $image_data = str_replace('data:image/png;base64,', '', $image_data);
        
        // Get prompt template
        $prompt_templates = $this->get_prompt_templates();
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
        if (!check_ajax_referer('expert_appraiser_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token.'));
        }
        
        // Get parameters
        $title = sanitize_text_field($_POST['title']);
        $description = sanitize_textarea_field($_POST['description']);
        $image_data = sanitize_text_field($_POST['image']);
        $appraisal_text = sanitize_textarea_field($_POST['appraisal']);
        $template_id = sanitize_text_field($_POST['template_id']);
        
        // Prepare data for saving
        $data = array(
            'title' => $title,
            'description' => $description,
            'appraisal_text' => $appraisal_text,
            'template_id' => $template_id
        );
        
        // Save appraisal to database
        $post_id = kollect_it_save_appraisal($data);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => $post_id->get_error_message()));
            return;
        }
        
        // Save image as attachment if provided
        if (!empty($image_data)) {
            $this->save_image_attachment($post_id, $image_data);
        }
        
        wp_send_json_success(array(
            'message' => 'Appraisal saved successfully.',
            'post_id' => $post_id
        ));
    }
    
    /**
     * Get API key from database
     */
    private function get_api_key() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
        return $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
    }
    
    /**
     * Save uploaded image as attachment
     */
    private function save_image_attachment($post_id, $image_data) {
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'];
        $upload_url = $upload_dir['url'];
        
        // Process image data
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
        
        return $attachment_id;
    }
    
    /**
     * Call the OpenAI API
     */
    private function call_openai_api($image_data, $api_key, $prompt_text) {
        // Get model from settings
        $model = get_option('expert_appraiser_openai_model', 'gpt-4o-mini');
        
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
    
    /**
     * Get prompt templates
     */
    private function get_prompt_templates() {
        $prompt_file = EXPERT_APPRAISER_PLUGIN_DIR . 'prompts/expert-appraisal.txt';
        
        if (file_exists($prompt_file)) {
            $standard_prompt = file_get_contents($prompt_file);
        } else {
            // Default prompt if file doesn't exist
            $standard_prompt = 'You are an expert appraiser with over 30 years of experience appraising antiques, collectibles, and art.
            
Please provide a detailed and comprehensive appraisal for the item in this image, including:

1. What the item is - full identification with specific details
2. The estimated era, period, or date of creation
3. Materials and construction techniques used
4. Condition assessment
5. Historical significance or background of the item type
6. Current market value estimate (provide a range if appropriate)
7. Any notable features that affect the valuation
8. Comparable items that have recently sold with their prices

Format your response with clear section headings for readability.';
        }
        
        // Define templates
        return array(
            'standard' => $standard_prompt,
            'antique' => 'You are an expert antiques appraiser with 35 years of experience. Provide a detailed appraisal for this antique item, including identification, age, materials, condition, provenance if identifiable, historical context, and current fair market value range. Include comparables from recent auctions or sales.',
            'art' => 'You are a fine art appraiser with expertise in all periods and mediums. Analyze this artwork including: artist identification if possible, medium, period/style, composition analysis, condition, artistic significance, provenance if evident, and a current market valuation range. Include auction comparables if relevant.',
            'collectible' => 'You are an expert collectibles appraiser specializing in memorabilia, toys, coins, stamps, and other collectible items. Provide a detailed assessment of this collectible including: precise identification, era/date, manufacturer if relevant, rarity, condition using standard grading terminology, collector demand, recent comparable sales, and current market value.'
        );
    }
}
