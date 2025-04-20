
<?php
class Appraiser_API_Key_Manager {
    private $table_name;
    private $encryption_key;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
        $this->encryption_key = wp_salt('auth');
    }
    
    public function get_api_key() {
        global $wpdb;
        
        $encrypted_key = $wpdb->get_var("SELECT api_key FROM {$this->table_name} ORDER BY id DESC LIMIT 1");
        
        if (!$encrypted_key) {
            return false;
        }
        
        if ($wpdb->last_error) {
            error_log('Database error retrieving API key: ' . $wpdb->last_error);
            return false;
        }
        
        // Decrypt the key
        try {
            return $this->decrypt_key($encrypted_key);
        } catch (Exception $e) {
            error_log('Error decrypting API key: ' . $e->getMessage());
            return false;
        }
    }
    
    public function store_api_key($api_key) {
        global $wpdb;
        
        // Encrypt the key before storing
        try {
            $encrypted_key = $this->encrypt_key($api_key);
        } catch (Exception $e) {
            error_log('Error encrypting API key: ' . $e->getMessage());
            return false;
        }
        
        // Clear existing keys
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
        
        // Insert new encrypted key
        $result = $wpdb->insert(
            $this->table_name,
            array('api_key' => $encrypted_key),
            array('%s')
        );
        
        return $result !== false;
    }
    
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
    
    private function decrypt_key($encrypted_data) {
        if (!extension_loaded('openssl')) {
            throw new Exception('OpenSSL extension not loaded');
        }
        
        $data = base64_decode($encrypted_data);
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);
        
        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $this->encryption_key, 0, $iv);
        
        if ($decrypted === false) {
            throw new Exception('Failed to decrypt API key');
        }
        
        return $decrypted;
    }
}
