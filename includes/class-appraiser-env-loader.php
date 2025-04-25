<?php
/**
 * Environment variable loader class
 */
class Appraiser_Env_Loader {
    /**
     * Load environment variables from .env file
     */
    public static function load() {
        $env_file = ABSPATH . '.env';
        
        if (file_exists($env_file)) {
            $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Only process lines with equals sign
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
                        $value = $matches[2];
                    }
                    
                    // Set as environment variable
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                    putenv("$name=$value");
                }
            }
        }
    }
    
    /**
     * Get an environment variable with fallback
     * 
     * @param string $key Environment variable name
     * @param mixed $default Default value if not set
     * @return mixed Environment variable value or default
     */
    public static function get($key, $default = null) {
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        return $value;
    }
    
    /**
     * Create or update the .env file with new values
     * 
     * @param array $values Key-value pairs to add to .env
     * @return bool True on success, false on failure
     */
    public static function update_env_file($values) {
        if (!is_array($values) || empty($values)) {
            return false;
        }
        
        $env_file = ABSPATH . '.env';
        $env_content = [];
        
        // Read existing content if file exists
        if (file_exists($env_file)) {
            $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    // Keep comments
                    $env_content[] = $line;
                    continue;
                }
                
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    
                    // Skip this line if we're updating this key
                    if (array_key_exists($name, $values)) {
                        continue;
                    }
                    
                    $env_content[] = $line;
                }
            }
        }
        
        // Add new values
        foreach ($values as $key => $value) {
            $env_content[] = "$key=$value";
        }
        
        // Try to write to file
        try {
            $result = file_put_contents($env_file, implode(PHP_EOL, $env_content) . PHP_EOL);
            return $result !== false;
        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log('Error writing to .env file: ' . $e->getMessage());
            }
            return false;
        }
    }
}
