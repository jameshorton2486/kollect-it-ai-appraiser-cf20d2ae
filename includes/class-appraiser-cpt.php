
<?php
/**
 * Register Appraisal Custom Post Type
 */
class Appraiser_CPT {
    /**
     * Register the custom post type
     */
    public function register() {
        $labels = array(
            'name'               => _x('Appraisals', 'post type general name', 'expert-appraiser-ai'),
            'singular_name'      => _x('Appraisal', 'post type singular name', 'expert-appraiser-ai'),
            'menu_name'          => _x('Appraisals', 'admin menu', 'expert-appraiser-ai'),
            'name_admin_bar'     => _x('Appraisal', 'add new on admin bar', 'expert-appraiser-ai'),
            'add_new'            => _x('Add New', 'appraisal', 'expert-appraiser-ai'),
            'add_new_item'       => __('Add New Appraisal', 'expert-appraiser-ai'),
            'new_item'           => __('New Appraisal', 'expert-appraiser-ai'),
            'edit_item'          => __('Edit Appraisal', 'expert-appraiser-ai'),
            'view_item'          => __('View Appraisal', 'expert-appraiser-ai'),
            'all_items'          => __('All Appraisals', 'expert-appraiser-ai'),
            'search_items'       => __('Search Appraisals', 'expert-appraiser-ai'),
            'parent_item_colon'  => __('Parent Appraisals:', 'expert-appraiser-ai'),
            'not_found'          => __('No appraisals found.', 'expert-appraiser-ai'),
            'not_found_in_trash' => __('No appraisals found in Trash.', 'expert-appraiser-ai')
        );
        
        $args = array(
            'labels'             => $labels,
            'description'        => __('AI-generated appraisals of antiques and collectibles', 'expert-appraiser-ai'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false, // We'll handle this with our custom admin menu
            'query_var'          => true,
            'rewrite'            => array('slug' => 'appraisal'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'thumbnail', 'author', 'excerpt'),
            'menu_icon'          => 'dashicons-search'
        );
        
        register_post_type('expert_appraisal', $args);
    }
}
