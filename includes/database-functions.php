
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Save an appraisal to the database
 *
 * @param array $data Appraisal data
 * @return int|WP_Error Post ID on success, WP_Error on failure
 */
function kollect_it_save_appraisal($data) {
    // Validate required fields
    if (empty($data['title']) || empty($data['description']) || empty($data['appraisal_text'])) {
        return new WP_Error('missing_data', 'Required appraisal data is missing.');
    }

    // Check user capability
    if (!current_user_can('use_appraiser') && !current_user_can('manage_options')) {
        return new WP_Error('permission_denied', 'You do not have permission to save appraisals.');
    }

    // Verify nonce if provided
    if (!empty($data['nonce']) && !wp_verify_nonce($data['nonce'], 'kollect_it_save_appraisal')) {
        return new WP_Error('invalid_nonce', 'Security verification failed.');
    }

    // Create post for the appraisal
    $post_data = array(
        'post_title'   => wp_strip_all_tags($data['title']),
        'post_content' => wp_kses_post($data['appraisal_text']),
        'post_status'  => 'publish',
        'post_type'    => 'expert_appraisal',
        'post_author'  => get_current_user_id()
    );

    // Insert the post
    $post_id = wp_insert_post($post_data, true);
    if (is_wp_error($post_id)) {
        return $post_id;
    }

    // Save metadata
    update_post_meta($post_id, '_appraisal_description', sanitize_textarea_field($data['description']));
    update_post_meta($post_id, '_template_id', sanitize_text_field($data['template_id']));
    update_post_meta($post_id, '_model', sanitize_text_field($data['metadata']['model'] ?? ''));
    update_post_meta($post_id, '_tokens_used', absint($data['metadata']['totalTokens'] ?? 0));
    update_post_meta($post_id, '_generated_at', sanitize_text_field($data['metadata']['timestamp'] ?? ''));
    update_post_meta($post_id, '_user_id', get_current_user_id());
    update_post_meta($post_id, '_user_ip', sanitize_text_field($_SERVER['REMOTE_ADDR']));
    
    if (!empty($data['image_url'])) {
        update_post_meta($post_id, '_image_url', esc_url_raw($data['image_url']));
    }

    return $post_id;
}

/**
 * Get a single appraisal by ID
 *
 * @param int $id Appraisal ID
 * @return array|WP_Error Appraisal data or error
 */
function kollect_it_get_appraisal($id) {
    $post = get_post($id);
    if (!$post || $post->post_type !== 'expert_appraisal') {
        return new WP_Error('not_found', 'Appraisal not found.');
    }
    
    // Check if user has permission to view this appraisal
    $user_id = get_current_user_id();
    $is_admin = current_user_can('manage_options');
    $is_author = $user_id == $post->post_author;
    
    if (!$is_admin && !$is_author && !current_user_can('manage_appraisals')) {
        return new WP_Error('permission_denied', 'You do not have permission to view this appraisal.');
    }

    return array(
        'id' => $post->ID,
        'title' => $post->post_title,
        'description' => get_post_meta($post->ID, '_appraisal_description', true),
        'template_id' => get_post_meta($post->ID, '_template_id', true),
        'image_url' => get_post_meta($post->ID, '_image_url', true),
        'appraisal_text' => $post->post_content,
        'created_at' => $post->post_date,
        'user_id' => $post->post_author,
        'user_name' => get_the_author_meta('display_name', $post->post_author),
        'metadata' => array(
            'model' => get_post_meta($post->ID, '_model', true),
            'tokens_used' => get_post_meta($post->ID, '_tokens_used', true),
            'generated_at' => get_post_meta($post->ID, '_generated_at', true)
        )
    );
}

/**
 * Get recent appraisals
 *
 * @param int $limit Number of appraisals to return
 * @param array $args Additional query arguments
 * @return array Array of appraisals
 */
function kollect_it_get_recent_appraisals($limit = 10, $args = array()) {
    $user_id = get_current_user_id();
    $is_admin = current_user_can('manage_options');
    $can_manage = current_user_can('manage_appraisals');
    
    // Default query arguments
    $query_args = array(
        'post_type' => 'expert_appraisal',
        'posts_per_page' => absint($limit),
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    // If not admin or editor, only show own appraisals
    if (!$is_admin && !$can_manage) {
        $query_args['author'] = $user_id;
    }
    
    // Merge with additional arguments
    $query_args = array_merge($query_args, $args);
    
    // Security: Ensure we're not bypassing the author restriction
    if (!$is_admin && !$can_manage && isset($args['author'])) {
        $query_args['author'] = $user_id;
    }

    $posts = get_posts($query_args);
    $appraisals = array();

    foreach ($posts as $post) {
        $appraisals[] = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'description' => get_post_meta($post->ID, '_appraisal_description', true),
            'template_id' => get_post_meta($post->ID, '_template_id', true),
            'image_url' => get_post_meta($post->ID, '_image_url', true),
            'appraisal_text' => $post->post_content,
            'created_at' => $post->post_date,
            'user_id' => $post->post_author,
            'user_name' => get_the_author_meta('display_name', $post->post_author)
        );
    }

    return $appraisals;
}

/**
 * Delete an appraisal
 *
 * @param int $id Appraisal ID
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function kollect_it_delete_appraisal($id) {
    $post = get_post($id);
    if (!$post || $post->post_type !== 'expert_appraisal') {
        return new WP_Error('not_found', 'Appraisal not found.');
    }
    
    // Security check: Only admin or the author can delete
    $user_id = get_current_user_id();
    $is_admin = current_user_can('manage_options');
    $is_author = $user_id == $post->post_author;
    
    if (!$is_admin && !$is_author) {
        return new WP_Error('permission_denied', 'You do not have permission to delete this appraisal.');
    }

    // Verify nonce if provided
    if (!empty($_REQUEST['_wpnonce']) && !wp_verify_nonce($_REQUEST['_wpnonce'], 'delete_appraisal_' . $id)) {
        return new WP_Error('invalid_nonce', 'Security verification failed.');
    }

    $result = wp_delete_post($id, true);
    if (!$result) {
        return new WP_Error('delete_failed', 'Failed to delete appraisal.');
    }

    return true;
}

/**
 * Get appraisal statistics
 *
 * @return array Statistics about appraisals
 */
function kollect_it_get_appraisal_stats() {
    $user_id = get_current_user_id();
    $is_admin = current_user_can('manage_options');
    
    // Query parameters
    $query_args = array(
        'post_type' => 'expert_appraisal',
        'posts_per_page' => -1,
        'fields' => 'ids',
    );
    
    // Non-admins only see their own stats
    if (!$is_admin) {
        $query_args['author'] = $user_id;
    }
    
    // Get all appraisal IDs
    $appraisal_ids = get_posts($query_args);
    
    // Total count
    $total_count = count($appraisal_ids);
    
    // Count by template
    $templates = array();
    foreach ($appraisal_ids as $id) {
        $template_id = get_post_meta($id, '_template_id', true);
        if (!isset($templates[$template_id])) {
            $templates[$template_id] = 0;
        }
        $templates[$template_id]++;
    }
    
    // Get today's count
    $today_start = strtotime('today midnight');
    $today_count = 0;
    
    foreach ($appraisal_ids as $id) {
        $post_date = get_post_time('U', false, $id);
        if ($post_date >= $today_start) {
            $today_count++;
        }
    }
    
    // Get this month's count
    $month_start = strtotime('first day of this month midnight');
    $month_count = 0;
    
    foreach ($appraisal_ids as $id) {
        $post_date = get_post_time('U', false, $id);
        if ($post_date >= $month_start) {
            $month_count++;
        }
    }
    
    return array(
        'total' => $total_count,
        'today' => $today_count,
        'month' => $month_count,
        'by_template' => $templates
    );
}
