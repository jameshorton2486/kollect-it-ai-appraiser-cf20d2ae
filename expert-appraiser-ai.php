<?php
/**
 * Plugin Name: Expert Appraiser AI
 * Plugin URI: https://kollect-it.com
 * Description: AI-powered expert appraisal tool for antiques, collectibles, and art using OpenAI's vision API.
 * Version: 1.0.0
 * Author: Kollect-It
 * Text Domain: expert-appraiser-ai
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EXPERT_APPRAISER_VERSION', '1.0.0');
define('EXPERT_APPRAISER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EXPERT_APPRAISER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/class-appraiser-ai.php';
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/class-appraiser-admin.php';
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/helpers.php';
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/database-functions.php';

// Initialize the plugin
function expert_appraiser_initialize() {
    // Initialize main plugin class
    $plugin = new Appraiser_AI();
    $plugin->init();
    
    // Initialize admin
    $admin = new Appraiser_Admin();
    $admin->init();
}
add_action('plugins_loaded', 'expert_appraiser_initialize');

// Activation hook
register_activation_hook(__FILE__, 'expert_appraiser_activate');
function expert_appraiser_activate() {
    // Create database tables if needed
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        api_key varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Create the custom post type for appraisals
    $cpt = new Appraiser_CPT();
    $cpt->register();
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'expert_appraiser_deactivate');
function expert_appraiser_deactivate() {
    // Cleanup tasks if needed
    flush_rewrite_rules();
}

// Enqueue admin scripts and styles
function expert_appraiser_admin_scripts() {
    wp_enqueue_style('expert-appraiser-admin-css', EXPERT_APPRAISER_PLUGIN_URL . 'assets/css/admin.css', array(), EXPERT_APPRAISER_VERSION);
    wp_enqueue_script('expert-appraiser-admin-js', EXPERT_APPRAISER_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), EXPERT_APPRAISER_VERSION, true);
}
add_action('admin_enqueue_scripts', 'expert_appraiser_admin_scripts');

// Enqueue frontend scripts and styles
function expert_appraiser_frontend_scripts() {
    wp_enqueue_style('expert-appraiser-css', EXPERT_APPRAISER_PLUGIN_URL . 'assets/css/frontend.css', array(), EXPERT_APPRAISER_VERSION);
    wp_enqueue_script('expert-appraiser-js', EXPERT_APPRAISER_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), EXPERT_APPRAISER_VERSION, true);

    // Add plugin settings to script
    $settings = array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('expert_appraiser_nonce')
    );
    wp_localize_script('expert-appraiser-js', 'expertAppraiserSettings', $settings);
}
add_action('wp_enqueue_scripts', 'expert_appraiser_frontend_scripts');

// Register shortcode with new name
function expert_appraiser_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(array(
        'title' => 'Expert Appraiser AI',
        'show_save' => 'true'
    ), $atts);
    
    // Include the form template
    ob_start();
    include EXPERT_APPRAISER_PLUGIN_DIR . 'templates/form.php';
    return ob_get_clean();
}
add_shortcode('expert_appraiser', 'expert_appraiser_shortcode');

// Initialize custom post type
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/class-appraiser-cpt.php';
$cpt = new Appraiser_CPT();
$cpt->init();
