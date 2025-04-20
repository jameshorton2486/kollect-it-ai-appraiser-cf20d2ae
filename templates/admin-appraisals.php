
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check user capabilities for security
if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'expert-appraiser-ai'));
}

// Get appraisals with pagination and security measures
$current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
$args = array(
    'post_type' => 'expert_appraisal',
    'posts_per_page' => 20,
    'paged' => $current_page,
    // Add author restriction based on user role
    'author' => current_user_can('manage_options') ? '' : get_current_user_id()
);

// Apply author filter if set
if (isset($_GET['author']) && !empty($_GET['author']) && current_user_can('manage_options')) {
    $args['author'] = absint($_GET['author']);
}

// Apply nonce verification for any actions
$action_nonce = wp_create_nonce('expert_appraiser_action');

$appraisals = new WP_Query($args);
?>
<div class="wrap expert-appraiser-admin-container">
    <div class="expert-appraiser-admin-header">
        <h1><?php _e('Appraisals', 'expert-appraiser-ai'); ?></h1>
    </div>
    
    <div class="expert-appraiser-admin-card">
        <h2><?php _e('Saved Appraisals', 'expert-appraiser-ai'); ?></h2>
        
        <?php if ($appraisals->have_posts()) : ?>
            <div class="tablenav top">
                <div class="alignleft actions">
                    <form method="get">
                        <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
                        <?php
                        // Add user filter for admins only
                        if (current_user_can('manage_options')) {
                            $users = get_users(array('fields' => array('ID', 'display_name')));
                            echo '<select name="author">';
                            echo '<option value="">' . __('All Users', 'expert-appraiser-ai') . '</option>';
                            foreach ($users as $user) {
                                $selected = isset($_GET['author']) && $_GET['author'] == $user->ID ? 'selected' : '';
                                echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
                            }
                            echo '</select>';
                        }
                        ?>
                        <input type="submit" class="button" value="<?php _e('Filter', 'expert-appraiser-ai'); ?>">
                    </form>
                </div>
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php echo sprintf(_n('%s item', '%s items', $appraisals->found_posts, 'expert-appraiser-ai'), 
                                number_format_i18n($appraisals->found_posts)); ?>
                    </span>
                </div>
                <br class="clear">
            </div>
        
            <table class="expert-appraiser-admin-table">
                <thead>
                    <tr>
                        <th><?php _e('Image', 'expert-appraiser-ai'); ?></th>
                        <th><?php _e('Title', 'expert-appraiser-ai'); ?></th>
                        <th><?php _e('Date', 'expert-appraiser-ai'); ?></th>
                        <th><?php _e('Type', 'expert-appraiser-ai'); ?></th>
                        <th><?php _e('User', 'expert-appraiser-ai'); ?></th>
                        <th><?php _e('Actions', 'expert-appraiser-ai'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($appraisals->have_posts()) : $appraisals->the_post(); ?>
                        <tr>
                            <td>
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('thumbnail', array('class' => 'thumbnail')); ?>
                                <?php else : ?>
                                    <div class="no-thumbnail">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td><?php the_title(); ?></td>
                            <td><?php echo get_the_date(); ?></td>
                            <td>
                                <?php
                                $template_id = get_post_meta(get_the_ID(), '_template_id', true);
                                $templates = expert_appraiser_get_templates();
                                
                                if (isset($templates[$template_id])) {
                                    echo esc_html($templates[$template_id]);
                                } else {
                                    _e('Standard', 'expert-appraiser-ai');
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                // Show author info only to admins
                                if (current_user_can('manage_options')) {
                                    $author = get_the_author();
                                    echo esc_html($author);
                                } else {
                                    echo esc_html(get_the_author_meta('display_name', get_current_user_id()));
                                }
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo get_edit_post_link(); ?>" class="button">
                                    <?php _e('View', 'expert-appraiser-ai'); ?>
                                </a>
                                
                                <?php 
                                // Only allow delete if user is admin or post author
                                if (current_user_can('manage_options') || get_current_user_id() == get_post_field('post_author', get_the_ID())) : 
                                ?>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=expert_appraiser_delete_appraisal&post_id=' . get_the_ID()), 'delete_appraisal_' . get_the_ID()); ?>" 
                                       class="button delete-appraisal" 
                                       onclick="return confirm('<?php _e('Are you sure you want to delete this appraisal?', 'expert-appraiser-ai'); ?>');">
                                        <?php _e('Delete', 'expert-appraiser-ai'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $appraisals->max_num_pages,
                        'current' => max(1, $current_page)
                    ));
                    ?>
                </div>
            </div>
            
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <p><?php _e('No appraisals found.', 'expert-appraiser-ai'); ?></p>
        <?php endif; ?>
    </div>
</div>
