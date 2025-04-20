
<?php
class Appraiser_OpenAI_Client {
    private $api_key;
    private $model;
    
    public function __construct($api_key) {
        $this->api_key = $api_key;
        $this->model = get_option('expert_appraiser_openai_model', 'gpt-4o-mini');
    }
    
    public function generate_appraisal($image_data, $prompt_text) {
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => $this->model,
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
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($body['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', 'Invalid response from OpenAI API');
        }
        
        return array(
            'appraisalText' => $body['choices'][0]['message']['content'],
            'metadata' => array(
                'model' => $this->model,
                'promptTokens' => isset($body['usage']['prompt_tokens']) ? $body['usage']['prompt_tokens'] : null,
                'completionTokens' => isset($body['usage']['completion_tokens']) ? $body['usage']['completion_tokens'] : null,
                'totalTokens' => isset($body['usage']['total_tokens']) ? $body['usage']['total_tokens'] : null
            )
        );
    }
}
