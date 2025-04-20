
<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check user capabilities for security
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'expert-appraiser-ai'));
}

// Process form submission
$message = '';
$message_type = '';

if (isset($_POST['expert_appraiser_update_user_limits']) && check_admin_referer('expert_appraiser_user_limits')) {
    $user_id = absint($_POST['user_id']);
    $daily_limit = absint($_POST['daily_limit']);
    
    if ($user_id && $daily_limit) {
        // Get user's current usage data
        $user_usage = get_user_meta($user_id, 'expert_appraiser_usage', true);
        
        if (!is_array($user_usage)) {
            $user_usage = array(
                'minute_requests' => array(),
                'daily_count' => 0,
                'daily_reset' => strtotime('today midnight')
            );
        }
        
        // Update custom limit
        $user_usage['custom_daily_limit'] = $daily_limit;
        
        // Save updated usage data
        update_user_meta($user_id, 'expert_appraiser_usage', $user_usage);
        
        $message = __('User limits updated successfully.', 'expert-appraiser-ai');
        $message_type = 'updated';
    }
}

// Reset user usage if requested
if (isset($_GET['reset_user']) && check_admin_referer('reset_user_usage')) {
    $user_id = absint($_GET['reset_user']);
    
    if ($user_id) {
        $user_usage = array(
            'minute_requests' => array(),
            'daily_count' => 0,
            'daily_reset' => strtotime('today midnight')
        );
        
        update_user_meta($user_id, 'expert_appraiser_usage', $user_usage);
        
        $message = __('User usage reset successfully.', 'expert-appraiser-ai');
        $message_type = 'updated';
    }
}

// Get all users with the custom capability
$users = get_users(array(
    'capability' => 'use_appraiser'
));

// Get global limits
$global_daily_limit = get_option('expert_appraiser_daily_limit', 10);
$global_rate_limit = get_option('expert_appraiser_rate_limit', 5);
?>

<div class="wrap expert-appraiser-admin-container">
    <div class="expert-appraiser-admin-header">
        <h1><?php _e('User Management', 'expert-appraiser-ai'); ?></h1>
    </div>
    
    <?php if ($message) : ?>
        <div class="notice notice-<?php echo $message_type; ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="expert-appraiser-admin-card">
        <h2><?php _e('Global Limits', 'expert-appraiser-ai'); ?></h2>
        <p><?php _e('These limits apply to all users by default, but can be overridden for individual users.', 'expert-appraiser-ai'); ?></p>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Daily Limit', 'expert-appraiser-ai'); ?></th>
                <td><?php echo esc_html($global_daily_limit); ?> <?php _e('appraisals per day', 'expert-appraiser-ai'); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Rate Limit', 'expert-appraiser-ai'); ?></th>
                <td><?php echo esc_html($global_rate_limit); ?> <?php _e('requests per minute', 'expert-appraiser-ai'); ?></td>
            </tr>
        </table>
        <p class="description"><?php _e('To change these global limits, go to the Settings page.', 'expert-appraiser-ai'); ?></p>
    </div>
    
    <div class="expert-appraiser-admin-card">
        <h2><?php _e('User Limits', 'expert-appraiser-ai'); ?></h2>
        <p><?php _e('Manage usage limits for individual users.', 'expert-appraiser-ai'); ?></p>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Username', 'expert-appraiser-ai'); ?></th>
                    <th><?php _e('Role', 'expert-appraiser-ai'); ?></th>
                    <th><?php _e('Custom Daily Limit', 'expert-appraiser-ai'); ?></th>
                    <th><?php _e('Usage Today', 'expert-appraiser-ai'); ?></th>
                    <th><?php _e('Actions', 'expert-appraiser-ai'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : 
                    $user_roles = $user->roles;
                    $role_names = array();
                    
                    foreach ($user_roles as $role) {
                        $role_obj = get_role($role);
                        $role_names[] = translate_user_role($role);
                    }
                    
                    // Get user usage data
                    $user_usage = get_user_meta($user->ID, 'expert_appraiser_usage', true);
                    $custom_limit = isset($user_usage['custom_daily_limit']) ? $user_usage['custom_daily_limit'] : $global_daily_limit;
                    $usage_today = isset($user_usage['daily_count']) ? $user_usage['daily_count'] : 0;
                    
                    // Check if daily reset is for today
                    $today = strtotime('today midnight');
                    if (isset($user_usage['daily_reset']) && $user_usage['daily_reset'] < $today) {
                        $usage_today = 0;
                    }
                ?>
                    <tr>
                        <td><?php echo esc_html($user->display_name); ?> (<?php echo esc_html($user->user_login); ?>)</td>
                        <td><?php echo esc_html(implode(', ', $role_names)); ?></td>
                        <td>
                            <form method="post" action="">
                                <?php wp_nonce_field('expert_appraiser_user_limits'); ?>
                                <input type="hidden" name="user_id" value="<?php echo esc_attr($user->ID); ?>">
                                <input type="number" name="daily_limit" value="<?php echo esc_attr($custom_limit); ?>" min="1" max="1000" class="small-text">
                                <input type="submit" name="expert_appraiser_update_user_limits" class="button button-small" value="<?php _e('Update', 'expert-appraiser-ai'); ?>">
                            </form>
                        </td>
                        <td><?php echo esc_html($usage_today); ?> / <?php echo esc_html($custom_limit); ?></td>
                        <td>
                            <a href="<?php echo wp_nonce_url(add_query_arg(array('page' => 'expert-appraiser-users', 'reset_user' => $user->ID), admin_url('admin.php')), 'reset_user_usage'); ?>" class="button button-small">
                                <?php _e('Reset Usage', 'expert-appraiser-ai'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="expert-appraiser-admin-card">
        <h2><?php _e('User Role Capabilities', 'expert-appraiser-ai'); ?></h2>
        <p><?php _e('The following capabilities are automatically assigned to WordPress roles:', 'expert-appraiser-ai'); ?></p>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Role', 'expert-appraiser-ai'); ?></th>
                    <th><?php _e('Use Appraiser', 'expert-appraiser-ai'); ?></th>
                    <th><?php _e('Manage Appraisals', 'expert-appraiser-ai'); ?></th>
                    <th><?php _e('Manage Settings', 'expert-appraiser-ai'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php _e('Administrator', 'expert-appraiser-ai'); ?></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Editor', 'expert-appraiser-ai'); ?></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Author', 'expert-appraiser-ai'); ?></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Contributor', 'expert-appraiser-ai'); ?></td>
                    <td><span class="dashicons dashicons-yes"></span></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                    <td><?php _e('Subscriber', 'expert-appraiser-ai'); ?></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                    <td><span class="dashicons dashicons-no"></span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
