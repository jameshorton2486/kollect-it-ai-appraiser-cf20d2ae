
<?php
/**
 * Shortcode for Appraisal Form
 */
class Kollect_It_Shortcode {
    /**
     * Register the shortcode
     */
    public function register() {
        add_shortcode('kollect_it_appraiser', array($this, 'render_shortcode'));
    }
    
    /**
     * Render the shortcode
     */
    public function render_shortcode($atts) {
        // Parse attributes
        $atts = shortcode_atts(array(
            'title' => 'Kollect-It Expert Appraiser',
            'show_save' => 'true'
        ), $atts);
        
        // Check if API key is set
        global $wpdb;
        $table_name = $wpdb->prefix . 'kollect_it_api_keys';
        $api_key = $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
        
        if (!$api_key) {
            if (current_user_can('manage_options')) {
                return '<div class="kollect-it-error">Please configure your OpenAI API key in the WordPress admin under Kollect-It → Settings.</div>';
            } else {
                return '<div class="kollect-it-error">This feature is currently unavailable. Please contact the site administrator.</div>';
            }
        }
        
        // Get prompt templates
        $prompt_templates = include KOLLECT_IT_PLUGIN_DIR . 'includes/prompt-templates.php';
        
        // Enqueue required scripts and styles
        wp_enqueue_style('kollect-it-css');
        wp_enqueue_script('kollect-it-js');
        
        // Start output buffering
        ob_start();
        
        // Include the template
        include KOLLECT_IT_PLUGIN_DIR . 'templates/appraisal-form.php';
        
        // Return the buffered content
        return ob_get_clean();
    }
}
