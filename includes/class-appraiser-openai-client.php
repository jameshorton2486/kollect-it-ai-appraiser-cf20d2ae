
<?php
class Appraiser_OpenAI_Client {
    private $api_key;
    private $model;
    private $max_retries = 3;
    private $retry_delay = 1; // seconds
    
    public function __construct($api_key) {
        $this->api_key = $api_key;
        $this->model = get_option('expert_appraiser_openai_model', 'gpt-4o-mini');
    }
    
    public function generate_appraisal($image_data, $prompt_text) {
        $retry_count = 0;
        
        while ($retry_count < $this->max_retries) {
            try {
                return $this->make_api_request($image_data, $prompt_text);
            } catch (Exception $e) {
                $retry_count++;
                if ($retry_count === $this->max_retries) {
                    return new WP_Error(
                        'api_error',
                        sprintf('API request failed after %d attempts: %s', $this->max_retries, $e->getMessage())
                    );
                }
                sleep($this->retry_delay * $retry_count); // Exponential backoff
            }
        }
    }
    
    private function make_api_request($image_data, $prompt_text) {
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
            throw new Exception($response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        // Handle rate limiting
        if ($response_code === 429) {
            $retry_after = wp_remote_retrieve_header($response, 'retry-after');
            if ($retry_after) {
                sleep(intval($retry_after));
            }
            throw new Exception('Rate limit exceeded. Please try again.');
        }
        
        // Handle other error responses
        if ($response_code !== 200) {
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Unknown API error';
            throw new Exception($error_message);
        }
        
        if (!isset($body['choices'][0]['message']['content'])) {
            throw new Exception('Invalid response from OpenAI API');
        }
        
        // Format and structure the response
        return array(
            'appraisalText' => $this->format_appraisal_text($body['choices'][0]['message']['content']),
            'metadata' => array(
                'model' => $this->model,
                'promptTokens' => isset($body['usage']['prompt_tokens']) ? $body['usage']['prompt_tokens'] : null,
                'completionTokens' => isset($body['usage']['completion_tokens']) ? $body['usage']['completion_tokens'] : null,
                'totalTokens' => isset($body['usage']['total_tokens']) ? $body['usage']['total_tokens'] : null,
                'timestamp' => current_time('mysql')
            )
        );
    }
    
    private function format_appraisal_text($text) {
        // Clean and format the AI response
        $text = wp_kses_post($text); // Allow safe HTML
        $text = str_replace("\n", '<br>', $text); // Convert newlines to <br>
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text); // Convert **bold** to <strong>
        return $text;
    }
}
