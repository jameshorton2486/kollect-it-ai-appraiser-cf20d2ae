<?php
/**
 * Helper functions for Expert Appraiser AI
 *
 * @package Expert_Appraiser_AI
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Convert markdown to HTML
 *
 * @param string $markdown
 * @return string
 */
function expert_appraiser_markdown_to_html($markdown) {
    // Convert headers
    $html = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $markdown);
    $html = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $html);
    
    // Convert bold
    $html = preg_replace('/\*\*(.*?)\*\*/m', '<strong>$1</strong>', $html);
    
    // Convert italic
    $html = preg_replace('/\*(.*?)\*/m', '<em>$1</em>', $html);
    
    // Convert lists
    $html = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*?<\/li>\n)+/s', '<ul>$0</ul>', $html);
    
    // Convert paragraphs
    $html = '<p>' . str_replace("\n\n", '</p><p>', $html) . '</p>';
    
    return $html;
}

/**
 * Generate export URL for appraisal
 *
 * @param int $appraisal_id
 * @param string $format
 * @return string
 */
function expert_appraiser_get_export_url($appraisal_id, $format = 'pdf') {
    $nonce = wp_create_nonce('expert_appraiser_export_' . $appraisal_id);
    
    return add_query_arg(array(
        'action' => 'expert_appraiser_export',
        'id' => $appraisal_id,
        'format' => $format,
        'nonce' => $nonce
    ), admin_url('admin-ajax.php'));
}

/**
 * Get user's appraisal count
 *
 * @param int $user_id
 * @return int
 */
function expert_appraiser_get_user_count($user_id = 0) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return 0;
    }
    
    $count = get_user_meta($user_id, 'appraiser_usage_count', true);
    
    return $count ? intval($count) : 0;
}

/**
 * Check if user has reached their appraisal limit
 *
 * @param int $user_id
 * @return bool
 */
function expert_appraiser_user_reached_limit($user_id = 0) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $count = expert_appraiser_get_user_count($user_id);
    $limit = get_option('appraiser_usage_limit', 10);
    
    return $count >= $limit;
}
