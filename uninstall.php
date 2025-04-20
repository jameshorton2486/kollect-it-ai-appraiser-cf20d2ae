
<?php
/**
 * Uninstall file for Expert Appraiser AI
 *
 * @package Expert_Appraiser_AI
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('expert_appraiser_openai_model');

// Delete API keys table
global $wpdb;
$table_name = $wpdb->prefix . 'expert_appraiser_api_keys';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Get appraisals
$appraisals = get_posts([
    'post_type' => 'expert_appraisal',
    'numberposts' => -1,
    'post_status' => 'any',
    'fields' => 'ids'
]);

// Delete all appraisals and their meta
foreach ($appraisals as $appraisal_id) {
    // Delete attachments
    $attachment_id = get_post_thumbnail_id($appraisal_id);
    if ($attachment_id) {
        wp_delete_attachment($attachment_id, true);
    }
    
    // Delete post and meta
    wp_delete_post($appraisal_id, true);
}

// Clean up post meta
$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '_expert_appraiser_%'");

// Flush rewrite rules
flush_rewrite_rules();

