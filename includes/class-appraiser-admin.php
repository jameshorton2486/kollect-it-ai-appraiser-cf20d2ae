<?php
/**
 * Class for admin functionality
 */
class Expert_Appraiser_Admin {
    /**
     * Constructor
     */
    public function __construct() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add settings link to plugins page
        add_filter('plugin_action_links_expert-appraiser-ai/expert-appraiser-ai.php', array($this, 'add_settings_link'));
    }
    
    /**
     * Add settings link to plugin listing
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=expert-appraiser-settings') . '">' . __('Settings', 'expert-appraiser-ai') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Expert Appraiser', 'expert-appraiser-ai'),
            __('Expert Appraiser', 'expert-appraiser-ai'),
            'use_appraiser',
            'expert-appraiser',
            array($this, 'admin_page'),
            'dashicons-welcome-learn-more',
            30
        );
        
        add_submenu_page(
            'expert-appraiser',
            __('Settings', 'expert-appraiser-ai'),
            __('Settings', 'expert-appraiser-ai'),
            'manage_options',
            'expert-appraiser-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'expert-appraiser',
            __('Appraisals', 'expert-appraiser-ai'),
            __('Appraisals', 'expert-appraiser-ai'),
            'use_appraiser',
            'expert-appraiser-items',
            array($this, 'appraisals_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('expert_appraiser_settings', 'appraiser_openai_key');
        register_setting('expert_appraiser_settings', 'appraiser_usage_limit', array(
            'default' => 10,
            'sanitize_callback' => 'absint'
        ));
        register_setting('expert_appraiser_settings', 'appraiser_rate_limit', array(
            'default' => 5,
            'sanitize_callback' => 'absint'
        ));
        register_setting('expert_appraiser_settings', 'appraiser_require_login', array(
            'default' => 'no'
        ));
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Expert Appraiser AI', 'expert-appraiser-ai'); ?></h1>
            
            <div class="card">
                <h2><?php echo esc_html__('Overview', 'expert-appraiser-ai'); ?></h2>
                <p><?php echo esc_html__('Expert Appraiser AI uses OpenAI\'s GPT-4 Vision to generate professional appraisals for antiques and collectibles.', 'expert-appraiser-ai'); ?></p>
                
                <h3><?php echo esc_html__('Usage', 'expert-appraiser-ai'); ?></h3>
                <p><?php echo esc_html__('Add the shortcode [expert_appraiser] to any page or post where you want the appraisal tool to appear.', 'expert-appraiser-ai'); ?></p>
                
                <h3><?php echo esc_html__('API Key Required', 'expert-appraiser-ai'); ?></h3>
                <p><?php echo esc_html__('You must configure your OpenAI API key in the settings to use this plugin.', 'expert-appraiser-ai'); ?></p>
                
                <p><a href="<?php echo esc_url(admin_url('admin.php?page=expert-appraiser-settings')); ?>" class="button button-primary"><?php echo esc_html__('Configure Settings', 'expert-appraiser-ai'); ?></a></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Expert Appraiser Settings', 'expert-appraiser-ai'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('expert_appraiser_settings'); ?>
                <?php do_settings_sections('expert_appraiser_settings'); ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('OpenAI API Key', 'expert-appraiser-ai'); ?></th>
                        <td>
                            <input type="password" name="appraiser_openai_key" value="<?php echo esc_attr(get_option('appraiser_openai_key')); ?>" class="regular-text" />
                            <p class="description"><?php echo esc_html__('Enter your OpenAI API key.', 'expert-appraiser-ai'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Daily Usage Limit', 'expert-appraiser-ai'); ?></th>
                        <td>
                            <input type="number" name="appraiser_usage_limit" value="<?php echo esc_attr(get_option('appraiser_usage_limit', 10)); ?>" class="small-text" min="1" />
                            <p class="description"><?php echo esc_html__('Maximum number of appraisals per user per day.', 'expert-appraiser-ai'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Rate Limit (per minute)', 'expert-appraiser-ai'); ?></th>
                        <td>
                            <input type="number" name="appraiser_rate_limit" value="<?php echo esc_attr(get_option('appraiser_rate_limit', 5)); ?>" class="small-text" min="1" />
                            <p class="description"><?php echo esc_html__('Maximum number of requests per user per minute.', 'expert-appraiser-ai'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Require Login', 'expert-appraiser-ai'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="appraiser_require_login" value="yes" <?php checked('yes', get_option('appraiser_require_login', 'no')); ?> />
                                <?php echo esc_html__('Users must be logged in to use the appraisal tool', 'expert-appraiser-ai'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Appraisals page
     */
    public function appraisals_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expert_appraisals';
        
        // Determine if viewing a single appraisal
        $appraisal_id = isset($_GET['appraisal']) ? intval($_GET['appraisal']) : 0;
        
        if ($appraisal_id > 0) {
            $this->view_single_appraisal($appraisal_id);
            return;
        }
        
        // Get appraisals with pagination
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $appraisals = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $total_pages = ceil($total_items / $per_page);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Appraisals', 'expert-appraiser-ai'); ?></h1>
            
            <?php if (empty($appraisals)): ?>
                <p><?php echo esc_html__('No appraisals found.', 'expert-appraiser-ai'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('ID', 'expert-appraiser-ai'); ?></th>
                            <th><?php echo esc_html__('Item', 'expert-appraiser-ai'); ?></th>
                            <th><?php echo esc_html__('User', 'expert-appraiser-ai'); ?></th>
                            <th><?php echo esc_html__('Date', 'expert-appraiser-ai'); ?></th>
                            <th><?php echo esc_html__('Actions', 'expert-appraiser-ai'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appraisals as $appraisal): ?>
                            <?php
                            $user_info = get_userdata($appraisal->user_id);
                            $username = $user_info ? $user_info->display_name : __('Unknown', 'expert-appraiser-ai');
                            $view_url = add_query_arg('appraisal', $appraisal->id, admin_url('admin.php?page=expert-appraiser-items'));
                            ?>
                            <tr>
                                <td><?php echo esc_html($appraisal->id); ?></td>
                                <td><?php echo esc_html($appraisal->item_name); ?></td>
                                <td><?php echo esc_html($username); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($appraisal->created_at))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url($view_url); ?>" class="button button-small"><?php echo esc_html__('View', 'expert-appraiser-ai'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php
                // Pagination
                echo '<div class="tablenav">';
                echo '<div class="tablenav-pages">';
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $page
                ));
                echo '</div>';
                echo '</div>';
                ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * View single appraisal
     *
     * @param int $appraisal_id
     */
    private function view_single_appraisal($appraisal_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expert_appraisals';
        
        $appraisal = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $appraisal_id
        ));
        
        if (!$appraisal) {
            wp_die(__('Appraisal not found.', 'expert-appraiser-ai'));
        }
        
        $user_info = get_userdata($appraisal->user_id);
        $username = $user_info ? $user_info->display_name : __('Unknown', 'expert-appraiser-ai');
        $upload_dir = wp_upload_dir();
        $image_url = $upload_dir['baseurl'] . '/' . $appraisal->image_path;
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($appraisal->item_name); ?></h1>
            
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=expert-appraiser-items')); ?>" class="button"><?php echo esc_html__('Back to List', 'expert-appraiser-ai'); ?></a>
            </p>
            
            <div class="card">
                <h3><?php echo esc_html__('Appraisal Details', 'expert-appraiser-ai'); ?></h3>
                <p>
                    <strong><?php echo esc_html__('User:', 'expert-appraiser-ai'); ?></strong> <?php echo esc_html($username); ?><br>
                    <strong><?php echo esc_html__('Date:', 'expert-appraiser-ai'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($appraisal->created_at))); ?>
                </p>
            </div>
            
            <div class="card">
                <div style="float: right; margin: 0 0 20px 20px; max-width: 300px;">
                    <img src="<?php echo esc_url($image_url); ?>" style="max-width: 100%; height: auto;" />
                </div>
                
                <div class="appraisal-content">
                    <?php echo wpautop(wp_kses_post($appraisal->appraisal_text)); ?>
                </div>
                
                <div style="clear: both;"></div>
            </div>
        </div>
        <?php
    }
}
