<?php
class Appraiser_API_Key_Manager {
    private $table_name;
    private $encryption_key;
    private $key_pattern = '/^sk-[a-zA-Z0-9]{32,}$/'; // More strict pattern for OpenAI keys
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
        $this->encryption_key = wp_salt('auth');
    }
    
    /**
     * Get the API key from environment or database
     * 
     * @return string|false API key if found, false otherwise
     */
    public function get_api_key() {
        // First try to get from environment variable
        $api_key = Appraiser_Env_Loader::get('OPENAI_API_KEY');
        
        // If found in env and valid format, return it
        if ($api_key && preg_match('/^sk-/', $api_key)) {
            return $api_key;
        }
        
        // Otherwise try from database
        global $wpdb;
        
        // Check if table exists, create if not
        $this->maybe_create_table();
        
        $encrypted_key = $wpdb->get_var("SELECT api_key FROM {$this->table_name} ORDER BY id DESC LIMIT 1");
        
        if (!$encrypted_key) {
            if (WP_DEBUG) {
                error_log('No API key found in the database or environment');
            }
            return false;
        }
        
        if ($wpdb->last_error) {
            if (WP_DEBUG) {
                error_log('Database error retrieving API key: ' . $wpdb->last_error);
            }
            return false;
        }
        
        // Decrypt the key
        try {
            $decrypted_key = $this->decrypt_key($encrypted_key);
            
            // Basic validation of the key format
            if (empty($decrypted_key) || !preg_match('/^sk-/', $decrypted_key)) {
                if (WP_DEBUG) {
                    error_log('Retrieved API key has invalid format');
                }
                return false;
            }
            
            return $decrypted_key;
        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log('Error decrypting API key: ' . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Store API key in .env file
     * 
     * @param string $api_key API key to store
     * @return bool True on success, false on failure
     */
    public function store_api_key_in_env($api_key) {
        // Basic validation
        if (empty($api_key) || !preg_match('/^sk-/', $api_key)) {
            if (WP_DEBUG) {
                error_log('Attempted to store invalid API key format');
            }
            return false;
        }
        
        // Update the .env file
        return Appraiser_Env_Loader::update_env_file(['OPENAI_API_KEY' => $api_key]);
    }
    
    /**
     * Validate API key format
     *
     * @param string $api_key API key to validate
     * @return bool True if valid format, false otherwise
     */
    public function validate_key_format($api_key) {
        return !empty($api_key) && preg_match('/^sk-/', $api_key);
    }
    
    /**
     * Store the API key in the database
     * 
     * @param string $api_key API key to store
     * @return bool True on success, false on failure
     */
    public function store_api_key($api_key) {
        global $wpdb;
        
        // Basic validation
        if (empty($api_key) || !preg_match('/^sk-/', $api_key)) {
            if (WP_DEBUG) {
                error_log('Attempted to store invalid API key format');
            }
            return false;
        }
        
        // Encrypt the key before storing
        try {
            $encrypted_key = $this->encrypt_key($api_key);
        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log('Error encrypting API key: ' . $e->getMessage());
            }
            return false;
        }
        
        // Check if table exists, create if not
        $this->maybe_create_table();
        
        // Clear existing keys
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
        
        // Insert new encrypted key
        $result = $wpdb->insert(
            $this->table_name,
            array('api_key' => $encrypted_key),
            array('%s')
        );
        
        if ($result === false) {
            if (WP_DEBUG) {
                error_log('Failed to insert API key: ' . $wpdb->last_error);
            }
        }
        
        return $result !== false;
    }
    
    /**
     * Create the API keys table if it doesn't exist
     */
    private function maybe_create_table() {
        global $wpdb;
        
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE {$this->table_name} (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                api_key text NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            
            if ($wpdb->last_error && WP_DEBUG) {
                error_log('Error creating API keys table: ' . $wpdb->last_error);
            }
        }
    }
    
    /**
     * Encrypt an API key
     * 
     * @param string $key The API key to encrypt
     * @return string The encrypted key
     */
    private function encrypt_key($key) {
        if (!extension_loaded('openssl')) {
            throw new Exception('OpenSSL extension not loaded');
        }
        
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($key, 'aes-256-cbc', $this->encryption_key, 0, $iv);
        
        if ($encrypted === false) {
            throw new Exception('Failed to encrypt API key');
        }
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt an API key
     * 
     * @param string $encrypted_data The encrypted API key
     * @return string The decrypted key
     */
    private function decrypt_key($encrypted_data) {
        if (!extension_loaded('openssl')) {
            throw new Exception('OpenSSL extension not loaded');
        }
        
        $data = base64_decode($encrypted_data);
        if ($data === false) {
            throw new Exception('Invalid base64 encoded data');
        }
        
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        if (strlen($data) <= $iv_length) {
            throw new Exception('Encrypted data is too short');
        }
        
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);
        
        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $this->encryption_key, 0, $iv);
        
        if ($decrypted === false) {
            throw new Exception('Failed to decrypt API key');
        }
        
        return $decrypted;
    }
    
    /**
     * Delete all stored API keys
     * 
     * @return bool True on success
     */
    public function delete_all_keys() {
        global $wpdb;
        return $wpdb->query("TRUNCATE TABLE {$this->table_name}") !== false;
    }
}
