
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
}
