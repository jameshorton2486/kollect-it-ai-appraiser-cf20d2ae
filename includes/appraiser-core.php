
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Run an appraisal through OpenAI
 *
 * @param array $data Form data
 * @param array $files Uploaded files
 * @return string|WP_Error Appraisal result or error
 */
function kollect_it_run_appraisal($data, $files) {
    // Validate input
    if (empty($data['item_title']) || empty($data['item_description'])) {
        return new WP_Error('missing_fields', 'Title and description are required.');
    }

    $title = sanitize_text_field($data['item_title']);
    $desc = sanitize_textarea_field($data['item_description']);
    $template_id = sanitize_text_field($data['template_id'] ?? 'standard');

    // Handle image upload
    $image_data = '';
    if (!empty($files['item_image']['tmp_name'])) {
        $upload = wp_handle_upload($files['item_image'], array(
            'test_form' => false,
            'mimes' => array('jpg' => 'image/jpeg', 'png' => 'image/png')
        ));

        if (isset($upload['error'])) {
            return new WP_Error('upload_error', $upload['error']);
        }

        // Convert image to base64
        $image_data = base64_encode(file_get_contents($files['item_image']['tmp_name']));
    } else {
        return new WP_Error('no_image', 'An image is required for appraisal.');
    }

    // Get API key
    $api_key = kollect_it_get_api_key();
    if (empty($api_key)) {
        return new WP_Error('no_api_key', 'OpenAI API key not configured.');
    }

    // Load prompt template
    $prompt_templates = include KOLLECT_IT_PLUGIN_DIR . 'includes/prompt-templates.php';
    $prompt_text = $prompt_templates[$template_id] ?? $prompt_templates['standard'];
    
    // Format prompt with item details
    $final_prompt = str_replace(
        ['{{TITLE}}', '{{DESCRIPTION}}'],
        [$title, $desc],
        $prompt_text
    );

    // Prepare OpenAI API request
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
                    'content' => array(
                        array(
                            'type' => 'text',
                            'text' => $final_prompt
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
    ));

    // Handle API response
    if (is_wp_error($response)) {
        return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        return new WP_Error('api_error', $body['error']['message']);
    }

    $ai_result = $body['choices'][0]['message']['content'];

    // Store the appraisal
    $result = kollect_it_save_appraisal(array(
        'title' => $title,
        'description' => $desc,
        'template_id' => $template_id,
        'image_url' => $upload['url'],
        'appraisal_text' => $ai_result
    ));

    if (is_wp_error($result)) {
        return $result;
    }

    return $ai_result;
}

/**
 * Get the OpenAI API key
 *
 * @return string|null API key if set
 */
function kollect_it_get_api_key() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'kollect_it_api_keys';
    return $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
}

