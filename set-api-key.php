
<?php
/**
 * Utility script to set the OpenAI API key in .env file
 * 
 * Usage: php set-api-key.php YOUR_API_KEY
 */

// Define ABSPATH if not defined
if (!defined('ABSPATH')) {
    // Go up one level to simulate being in the WordPress root
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Include the necessary files
require_once 'includes/class-appraiser-env-loader.php';
require_once 'includes/class-appraiser-api-key-manager.php';

// Check if key provided
if ($argc < 2) {
    echo "Please provide your OpenAI API key as an argument.\n";
    echo "Usage: php set-api-key.php YOUR_API_KEY\n";
    exit(1);
}

// Get the API key from command line
$api_key = trim($argv[1]);

// Validate key format
if (!preg_match('/^sk-/', $api_key)) {
    echo "Error: Invalid API key format. OpenAI API keys should start with 'sk-'.\n";
    exit(1);
}

// Try to save to .env file
$result = Appraiser_Env_Loader::update_env_file(['OPENAI_API_KEY' => $api_key]);

if ($result) {
    echo "Success! API key has been saved to .env file.\n";
    exit(0);
} else {
    echo "Error: Failed to save API key to .env file. Check file permissions.\n";
    exit(1);
}
