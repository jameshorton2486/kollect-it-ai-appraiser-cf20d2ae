
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get stored API key
 */
function kollect_it_get_api_key() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'kollect_it_api_keys';
    return $wpdb->get_var("SELECT api_key FROM $table_name ORDER BY id DESC LIMIT 1");
}

/**
 * Check if API key is configured
 */
function kollect_it_has_api_key() {
    return !empty(kollect_it_get_api_key());
}

/**
 * Format currency amount
 */
function kollect_it_format_currency($amount) {
    return '$' . number_format($amount, 2);
}

