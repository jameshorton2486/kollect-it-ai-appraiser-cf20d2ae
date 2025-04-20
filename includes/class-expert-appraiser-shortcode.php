
<?php
/**
 * Shortcode for Appraisal Form
 */
class Expert_Appraiser_Shortcode {
    /**
     * Register the shortcode
     */
    public function register() {
        add_shortcode('expert_appraiser', array($this, 'render_shortcode'));
    }
    
    /**
     * Render the shortcode
     */
    public function render_shortcode($atts) {
        // Parse attributes
        $atts = shortcode_atts(array(
            'title' => 'Expert Appraiser AI',
            'show_save' => 'true'
        ), $atts);
        
        // Check if API key is set
        global $wpdb;
        $table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
        $api_key = $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
        
        if (!$api_key) {
            if (current_user_can('manage_options')) {
                return '<div class="expert-appraiser-error">Please configure your OpenAI API key in the WordPress admin under Expert Appraiser → Settings.</div>';
            } else {
                return '<div class="expert-appraiser-error">This feature is currently unavailable. Please contact the site administrator.</div>';
            }
        }
        
        // Get prompt templates
        $prompt_templates = include EXPERT_APPRAISER_PLUGIN_DIR . 'includes/prompt-templates.php';
        
        // Enqueue required scripts and styles
        wp_enqueue_style('expert-appraiser-css');
        wp_enqueue_script('expert-appraiser-js');
        
        // Start output buffering
        ob_start();
        
        // Include the template
        include EXPERT_APPRAISER_PLUGIN_DIR . 'templates/form.php';
        
        // Return the buffered content
        return ob_get_clean();
    }
}
