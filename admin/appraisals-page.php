
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get appraisals
$args = array(
    'post_type' => 'kollect_it_appraisal',
    'posts_per_page' => 20,
    'paged' => isset($_GET['paged']) ? intval($_GET['paged']) : 1
);

$appraisals = new WP_Query($args);
?>
<div class="wrap kollect-it-admin-container">
    <div class="kollect-it-admin-header">
        <h1><?php _e('Appraisals', 'kollect-it-appraiser'); ?></h1>
    </div>
    
    <div class="kollect-it-admin-card">
        <h2><?php _e('Saved Appraisals', 'kollect-it-appraiser'); ?></h2>
        
        <?php if ($appraisals->have_posts()) : ?>
            <table class="kollect-it-admin-table">
                <thead>
                    <tr>
                        <th><?php _e('Image', 'kollect-it-appraiser'); ?></th>
                        <th><?php _e('Title', 'kollect-it-appraiser'); ?></th>
                        <th><?php _e('Date', 'kollect-it-appraiser'); ?></th>
                        <th><?php _e('Type', 'kollect-it-appraiser'); ?></th>
                        <th><?php _e('Actions', 'kollect-it-appraiser'); ?></th>
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
                                $templates = include KOLLECT_IT_PLUGIN_DIR . 'includes/prompt-templates.php';
                                
                                if (isset($templates[$template_id])) {
                                    echo ucfirst($template_id);
                                } else {
                                    _e('Standard', 'kollect-it-appraiser');
                                }
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo get_edit_post_link(); ?>" class="button">
                                    <?php _e('View', 'kollect-it-appraiser'); ?>
                                </a>
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
                        'current' => max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1)
                    ));
                    ?>
                </div>
            </div>
            
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <p><?php _e('No appraisals found.', 'kollect-it-appraiser'); ?></p>
        <?php endif; ?>
    </div>
</div>
