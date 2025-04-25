<?php
// Include the needed admin key handler
require_once plugin_dir_path(dirname(__FILE__)) . 'admin/api-key-handler.php';

class Appraiser_Admin {
    /**
     * Get API key
     */
    public static function get_api_key() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
        return $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
    }
}
