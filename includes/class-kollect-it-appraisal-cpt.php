
<?php
/**
 * Custom Post Type for Appraisals
 */
class Kollect_It_Appraisal_CPT {
    /**
     * Register the custom post type
     */
    public function register() {
        $labels = array(
            'name'               => _x('Appraisals', 'post type general name', 'kollect-it-appraiser'),
            'singular_name'      => _x('Appraisal', 'post type singular name', 'kollect-it-appraiser'),
            'menu_name'          => _x('Appraisals', 'admin menu', 'kollect-it-appraiser'),
            'name_admin_bar'     => _x('Appraisal', 'add new on admin bar', 'kollect-it-appraiser'),
            'add_new'            => _x('Add New', 'appraisal', 'kollect-it-appraiser'),
            'add_new_item'       => __('Add New Appraisal', 'kollect-it-appraiser'),
            'new_item'           => __('New Appraisal', 'kollect-it-appraiser'),
            'edit_item'          => __('Edit Appraisal', 'kollect-it-appraiser'),
            'view_item'          => __('View Appraisal', 'kollect-it-appraiser'),
            'all_items'          => __('All Appraisals', 'kollect-it-appraiser'),
            'search_items'       => __('Search Appraisals', 'kollect-it-appraiser'),
            'parent_item_colon'  => __('Parent Appraisals:', 'kollect-it-appraiser'),
            'not_found'          => __('No appraisals found.', 'kollect-it-appraiser'),
            'not_found_in_trash' => __('No appraisals found in Trash.', 'kollect-it-appraiser')
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('AI-generated appraisals for collectibles', 'kollect-it-appraiser'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false, // We'll add it to our custom menu
            'query_var'          => true,
            'rewrite'            => array('slug' => 'appraisal'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'thumbnail', 'author')
        );

        register_post_type('kollect_it_appraisal', $args);
    }
}
