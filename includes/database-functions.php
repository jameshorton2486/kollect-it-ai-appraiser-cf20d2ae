
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

    return array(
        'id' => $post->ID,
        'title' => $post->post_title,
        'description' => get_post_meta($post->ID, '_appraisal_description', true),
        'template_id' => get_post_meta($post->ID, '_template_id', true),
        'image_url' => get_post_meta($post->ID, '_image_url', true),
        'appraisal_text' => $post->post_content,
        'created_at' => $post->post_date,
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
 * @return array Array of appraisals
 */
function kollect_it_get_recent_appraisals($limit = 10) {
    $args = array(
        'post_type' => 'expert_appraisal',
        'posts_per_page' => absint($limit),
        'orderby' => 'date',
        'order' => 'DESC'
    );

    $posts = get_posts($args);
    $appraisals = array();

    foreach ($posts as $post) {
        $appraisals[] = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'description' => get_post_meta($post->ID, '_appraisal_description', true),
            'template_id' => get_post_meta($post->ID, '_template_id', true),
            'image_url' => get_post_meta($post->ID, '_image_url', true),
            'appraisal_text' => $post->post_content,
            'created_at' => $post->post_date
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

    $result = wp_delete_post($id, true);
    if (!$result) {
        return new WP_Error('delete_failed', 'Failed to delete appraisal.');
    }

    return true;
}
