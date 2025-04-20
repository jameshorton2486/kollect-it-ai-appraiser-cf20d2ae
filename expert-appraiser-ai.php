
<?php
/**
 * Plugin Name: Expert Appraiser AI
 * Plugin URI: https://kollect-it.com/expert-appraiser
 * Description: AI-powered expert appraisal tool for antiques, collectibles, and art using OpenAI's vision API.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Kollect-It
 * Author URI: https://kollect-it.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: expert-appraiser-ai
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EXPERT_APPRAISER_VERSION', '1.0.0');
define('EXPERT_APPRAISER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EXPERT_APPRAISER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EXPERT_APPRAISER_MINIMUM_WP_VERSION', '5.8');
define('EXPERT_APPRAISER_MINIMUM_PHP_VERSION', '7.4');

// System compatibility check
function expert_appraiser_compatibility_check() {
    global $wp_version;
    
    if (version_compare(PHP_VERSION, EXPERT_APPRAISER_MINIMUM_PHP_VERSION, '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            sprintf(
                __('Expert Appraiser AI requires PHP version %s or higher. Your current version is %s.', 'expert-appraiser-ai'),
                EXPERT_APPRAISER_MINIMUM_PHP_VERSION,
                PHP_VERSION
            )
        );
    }
    
    if (version_compare($wp_version, EXPERT_APPRAISER_MINIMUM_WP_VERSION, '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            sprintf(
                __('Expert Appraiser AI requires WordPress version %s or higher. Your current version is %s.', 'expert-appraiser-ai'),
                EXPERT_APPRAISER_MINIMUM_WP_VERSION,
                $wp_version
            )
        );
    }
}
register_activation_hook(__FILE__, 'expert_appraiser_compatibility_check');

// Include required files
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/class-appraiser-api-key-manager.php';
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/class-appraiser-image-processor.php';
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/class-appraiser-openai-client.php';
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/class-appraiser-ai.php';
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/class-appraiser-admin.php';
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/class-appraiser-cpt.php';
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/helpers.php';
require_once EXPERT_APPRAISER_PLUGIN_DIR . 'includes/database-functions.php';

/**
 * Initialize the plugin
 */
function expert_appraiser_initialize() {
    // Load text domain for translations
    load_plugin_textdomain(
        'expert-appraiser-ai',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
    
    // Initialize main plugin class
    $plugin = new Appraiser_AI();
    $plugin->init();
    
    // Initialize admin
    if (is_admin()) {
        $admin = new Appraiser_Admin();
        $admin->init();
    }
}
add_action('plugins_loaded', 'expert_appraiser_initialize');

/**
 * Plugin activation
 */
register_activation_hook(__FILE__, 'expert_appraiser_activate');
function expert_appraiser_activate() {
    // Run compatibility check
    expert_appraiser_compatibility_check();
    
    // Create database tables
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // API keys table
    $table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        api_key varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    // Usage tracking table
    $usage_table = $wpdb->prefix . 'expert_appraiser_usage';
    $sql .= "CREATE TABLE IF NOT EXISTS $usage_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        appraisal_count int NOT NULL DEFAULT 0,
        last_request datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Register custom post type
    $cpt = new Appraiser_CPT();
    $cpt->register();
    
    // Clear rewrite rules
    flush_rewrite_rules();
}

/**
 * Plugin deactivation
 */
register_deactivation_hook(__FILE__, 'expert_appraiser_deactivate');
function expert_appraiser_deactivate() {
    // Clear any scheduled hooks
    wp_clear_scheduled_hook('expert_appraiser_daily_cleanup');
    
    // Clear rewrite rules
    flush_rewrite_rules();
}

/**
 * Enqueue admin scripts and styles
 */
function expert_appraiser_admin_scripts($hook) {
    // Only load on our plugin pages
    if (strpos($hook, 'expert-appraiser') === false) {
        return;
    }
    
    wp_enqueue_style(
        'expert-appraiser-admin-css',
        EXPERT_APPRAISER_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        EXPERT_APPRAISER_VERSION
    );
    
    wp_enqueue_script(
        'expert-appraiser-admin-js',
        EXPERT_APPRAISER_PLUGIN_URL . 'assets/js/admin.js',
        array('jquery'),
        EXPERT_APPRAISER_VERSION,
        true
    );
    
    wp_localize_script('expert-appraiser-admin-js', 'expertAppraiserAdmin', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('expert_appraiser_admin_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'expert_appraiser_admin_scripts');

/**
 * Enqueue frontend scripts and styles
 */
function expert_appraiser_frontend_scripts() {
    wp_enqueue_style(
        'expert-appraiser-css',
        EXPERT_APPRAISER_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        EXPERT_APPRAISER_VERSION
    );
    
    wp_enqueue_script(
        'expert-appraiser-js',
        EXPERT_APPRAISER_PLUGIN_URL . 'assets/js/frontend.js',
        array('jquery'),
        EXPERT_APPRAISER_VERSION,
        true
    );
    
    wp_localize_script('expert-appraiser-js', 'expertAppraiserSettings', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('expert_appraiser_nonce'),
        'maxFileSize' => wp_max_upload_size(),
        'allowedTypes' => array('image/jpeg', 'image/png')
    ));
}
add_action('wp_enqueue_scripts', 'expert_appraiser_frontend_scripts');

// Daily cleanup task
add_action('expert_appraiser_daily_cleanup', 'expert_appraiser_do_daily_cleanup');
function expert_appraiser_do_daily_cleanup() {
    global $wpdb;
    
    // Reset daily usage counts
    $wpdb->query("UPDATE {$wpdb->prefix}expert_appraiser_usage SET appraisal_count = 0");
    
    // Clean up any temporary files older than 24 hours
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/expert-appraiser-temp';
    
    if (is_dir($temp_dir)) {
        foreach (glob($temp_dir . '/*') as $file) {
            if (filemtime($file) < time() - 86400) {
                unlink($file);
            }
        }
    }
}

// Schedule daily cleanup if not already scheduled
if (!wp_next_scheduled('expert_appraiser_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'expert_appraiser_daily_cleanup');
}

