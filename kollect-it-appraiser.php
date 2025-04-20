<?php
/**
 * Plugin Name: Kollect-It Appraiser
 * Plugin URI: https://kollect-it.com
 * Description: AI-powered appraisal tool for collectibles, antiques, and art.
 * Version: 1.0.0
 * Author: Kollect-It
 * Text Domain: kollect-it-appraiser
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KOLLECT_IT_VERSION', '1.0.0');
define('KOLLECT_IT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KOLLECT_IT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once KOLLECT_IT_PLUGIN_DIR . 'includes/class-kollect-it-appraiser.php';
require_once KOLLECT_IT_PLUGIN_DIR . 'includes/class-kollect-it-appraisal-cpt.php';
require_once KOLLECT_IT_PLUGIN_DIR . 'includes/class-kollect-it-settings.php';
require_once KOLLECT_IT_PLUGIN_DIR . 'includes/class-kollect-it-shortcode.php';
require_once KOLLECT_IT_PLUGIN_DIR . 'includes/database-functions.php';

// Initialize the plugin
function kollect_it_initialize() {
    // Initialize main plugin class
    $plugin = new Kollect_It_Appraiser();
    $plugin->init();
    
    // Register custom post type
    $cpt = new Kollect_It_Appraisal_CPT();
    $cpt->register();
    
    // Initialize settings
    $settings = new Kollect_It_Settings();
    $settings->init();
    
    // Register shortcode
    $shortcode = new Kollect_It_Shortcode();
    $shortcode->register();
}
add_action('plugins_loaded', 'kollect_it_initialize');

// Activation hook
register_activation_hook(__FILE__, 'kollect_it_activate');
function kollect_it_activate() {
    // Create database tables if needed
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $table_name = $wpdb->prefix . 'kollect_it_api_keys';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        api_key varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'kollect_it_deactivate');
function kollect_it_deactivate() {
    // Cleanup tasks if needed
}

// Enqueue admin scripts and styles
function kollect_it_admin_scripts() {
    wp_enqueue_style('kollect-it-admin-css', KOLLECT_IT_PLUGIN_URL . 'assets/css/admin.css', array(), KOLLECT_IT_VERSION);
    wp_enqueue_script('kollect-it-admin-js', KOLLECT_IT_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), KOLLECT_IT_VERSION, true);
}
add_action('admin_enqueue_scripts', 'kollect_it_admin_scripts');

// Enqueue frontend scripts and styles
function kollect_it_frontend_scripts() {
    wp_enqueue_style('kollect-it-css', KOLLECT_IT_PLUGIN_URL . 'assets/css/frontend.css', array(), KOLLECT_IT_VERSION);
    wp_enqueue_script('kollect-it-js', KOLLECT_IT_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), KOLLECT_IT_VERSION, true);

    // Add plugin settings to script
    $settings = array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('kollect_it_nonce')
    );
    wp_localize_script('kollect-it-js', 'kollectItSettings', $settings);
}
add_action('wp_enqueue_scripts', 'kollect_it_frontend_scripts');
