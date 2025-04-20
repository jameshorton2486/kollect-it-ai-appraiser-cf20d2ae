
<?php
class Appraiser_API_Key_Manager {
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
    }
    
    public function get_api_key() {
        global $wpdb;
        
        $api_key = $wpdb->get_var("SELECT api_key FROM {$this->table_name} ORDER BY id DESC LIMIT 1");
        
        if ($wpdb->last_error) {
            error_log('Database error retrieving API key: ' . $wpdb->last_error);
            return false;
        }
        
        return $api_key;
    }
    
    public function store_api_key($api_key) {
        global $wpdb;
        
        // Clear existing keys
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
        
        // Insert new key
        $result = $wpdb->insert(
            $this->table_name,
            array('api_key' => $api_key),
            array('%s')
        );
        
        return $result !== false;
    }
}
