
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get stored API key
 */
function expert_appraiser_get_api_key() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
    return $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
}

/**
 * Check if API key is configured
 */
function expert_appraiser_has_api_key() {
    return !empty(expert_appraiser_get_api_key());
}

/**
 * Format currency amount
 */
function expert_appraiser_format_currency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Get appraisal template options
 */
function expert_appraiser_get_templates() {
    return array(
        'standard' => __('Standard Appraisal', 'expert-appraiser-ai'),
        'antique' => __('Antique Appraisal', 'expert-appraiser-ai'),
        'art' => __('Art Appraisal', 'expert-appraiser-ai'),
        'collectible' => __('Collectible Appraisal', 'expert-appraiser-ai')
    );
}

/**
 * Create a shortcode for the appraisal form
 */
function expert_appraiser_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title' => __('Expert Appraiser AI', 'expert-appraiser-ai'),
        'show_save' => 'true'
    ), $atts);
    
    // Check if API key is set
    if (!expert_appraiser_has_api_key()) {
        if (current_user_can('manage_options')) {
            return '<div class="expert-appraiser-error">Please configure your OpenAI API key in the WordPress admin under Expert Appraiser → Settings.</div>';
        } else {
            return '<div class="expert-appraiser-error">This feature is currently unavailable. Please contact the site administrator.</div>';
        }
    }
    
    // Get template options
    $templates = expert_appraiser_get_templates();
    
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
add_shortcode('expert_appraiser', 'expert_appraiser_shortcode');
