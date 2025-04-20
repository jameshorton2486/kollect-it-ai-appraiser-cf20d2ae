
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap kollect-it-admin-container">
    <div class="kollect-it-admin-header">
        <h1><?php _e('Kollect-It Expert Appraiser', 'kollect-it-appraiser'); ?></h1>
    </div>
    
    <div class="kollect-it-admin-card">
        <h2><?php _e('Welcome to Kollect-It Appraiser', 'kollect-it-appraiser'); ?></h2>
        <p><?php _e('Use this AI-powered tool to generate professional appraisals for collectibles, antiques, and art.', 'kollect-it-appraiser'); ?></p>
        
        <h3><?php _e('Getting Started', 'kollect-it-appraiser'); ?></h3>
        <ol>
            <li><?php _e('Configure your OpenAI API key in the Settings tab.', 'kollect-it-appraiser'); ?></li>
            <li><?php _e('Add the appraiser form to any page or post using the shortcode below.', 'kollect-it-appraiser'); ?></li>
            <li><?php _e('Upload images of items to generate AI appraisals.', 'kollect-it-appraiser'); ?></li>
            <li><?php _e('View saved appraisals in the Appraisals tab.', 'kollect-it-appraiser'); ?></li>
        </ol>
        
        <h3><?php _e('Shortcode', 'kollect-it-appraiser'); ?></h3>
        <p><?php _e('Use this shortcode to add the appraisal form to any page or post:', 'kollect-it-appraiser'); ?></p>
        <div class="kollect-it-shortcode-example">
            [kollect_it_appraiser]
        </div>
        
        <p><?php _e('Optional parameters:', 'kollect-it-appraiser'); ?></p>
        <ul>
            <li><code>title</code> - <?php _e('Custom title for the form', 'kollect-it-appraiser'); ?></li>
            <li><code>show_save</code> - <?php _e('Set to "false" to hide the save button (default: "true")', 'kollect-it-appraiser'); ?></li>
        </ul>
        
        <div class="kollect-it-shortcode-example">
            [kollect_it_appraiser title="Art Appraisal Service" show_save="false"]
        </div>
    </div>
    
    <div class="kollect-it-admin-card">
        <h2><?php _e('Plugin Information', 'kollect-it-appraiser'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Version', 'kollect-it-appraiser'); ?></th>
                <td><?php echo KOLLECT_IT_VERSION; ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('OpenAI Model', 'kollect-it-appraiser'); ?></th>
                <td><?php echo get_option('kollect_it_openai_model', 'gpt-4o-mini'); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('API Key Status', 'kollect-it-appraiser'); ?></th>
                <td>
                    <?php
                    $api_key = Kollect_It_Settings::get_api_key();
                    if ($api_key) {
                        echo '<span style="color: green;">' . __('Configured', 'kollect-it-appraiser') . '</span>';
                    } else {
                        echo '<span style="color: red;">' . __('Not configured', 'kollect-it-appraiser') . '</span>';
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>
