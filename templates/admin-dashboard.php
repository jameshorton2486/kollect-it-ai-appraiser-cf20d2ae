
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap expert-appraiser-admin-container">
    <div class="expert-appraiser-admin-header">
        <h1><?php _e('Expert Appraiser AI', 'expert-appraiser-ai'); ?></h1>
    </div>
    
    <div class="expert-appraiser-admin-card">
        <h2><?php _e('Welcome to Expert Appraiser AI', 'expert-appraiser-ai'); ?></h2>
        <p><?php _e('Use this AI-powered tool to generate professional appraisals for antiques, collectibles, and art.', 'expert-appraiser-ai'); ?></p>
        
        <h3><?php _e('Getting Started', 'expert-appraiser-ai'); ?></h3>
        <ol>
            <li><?php _e('Configure your OpenAI API key in the Settings tab.', 'expert-appraiser-ai'); ?></li>
            <li><?php _e('Add the appraiser form to any page or post using the shortcode below.', 'expert-appraiser-ai'); ?></li>
            <li><?php _e('Upload images of items to generate AI appraisals.', 'expert-appraiser-ai'); ?></li>
            <li><?php _e('View saved appraisals in the Appraisals tab.', 'expert-appraiser-ai'); ?></li>
        </ol>
        
        <h3><?php _e('Shortcode', 'expert-appraiser-ai'); ?></h3>
        <p><?php _e('Use this shortcode to add the appraisal form to any page or post:', 'expert-appraiser-ai'); ?></p>
        <div class="expert-appraiser-shortcode-example">
            [expert_appraiser]
        </div>
        
        <p><?php _e('Optional parameters:', 'expert-appraiser-ai'); ?></p>
        <ul>
            <li><code>title</code> - <?php _e('Custom title for the form', 'expert-appraiser-ai'); ?></li>
            <li><code>show_save</code> - <?php _e('Set to "false" to hide the save button (default: "true")', 'expert-appraiser-ai'); ?></li>
        </ul>
        
        <div class="expert-appraiser-shortcode-example">
            [expert_appraiser title="Antique Appraisal Service" show_save="false"]
        </div>
    </div>
    
    <div class="expert-appraiser-admin-card">
        <h2><?php _e('Plugin Information', 'expert-appraiser-ai'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Version', 'expert-appraiser-ai'); ?></th>
                <td><?php echo EXPERT_APPRAISER_VERSION; ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('OpenAI Model', 'expert-appraiser-ai'); ?></th>
                <td><?php echo get_option('expert_appraiser_openai_model', 'gpt-4o-mini'); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('API Key Status', 'expert-appraiser-ai'); ?></th>
                <td>
                    <?php
                    $api_key = Appraiser_Admin::get_api_key();
                    if ($api_key) {
                        echo '<span style="color: green;">' . __('Configured', 'expert-appraiser-ai') . '</span>';
                    } else {
                        echo '<span style="color: red;">' . __('Not configured', 'expert-appraiser-ai') . '</span>';
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>
