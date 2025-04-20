
<?php
class Kollect_It_Menu {
    public function add_admin_menu() {
        add_menu_page(
            __('Kollect-It Appraiser', 'kollect-it-appraiser'),
            __('Kollect-It', 'kollect-it-appraiser'),
            'manage_options',
            'kollect-it-appraiser',
            array($this, 'render_admin_page'),
            'dashicons-search',
            30
        );
        
        add_submenu_page(
            'kollect-it-appraiser',
            __('Appraisals', 'kollect-it-appraiser'),
            __('Appraisals', 'kollect-it-appraiser'),
            'manage_options',
            'kollect-it-appraisals',
            array($this, 'render_appraisals_page')
        );
        
        add_submenu_page(
            'kollect-it-appraiser',
            __('Settings', 'kollect-it-appraiser'),
            __('Settings', 'kollect-it-appraiser'),
            'manage_options',
            'kollect-it-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function render_admin_page() {
        include KOLLECT_IT_PLUGIN_DIR . 'admin/admin-page.php';
    }
    
    public function render_appraisals_page() {
        include KOLLECT_IT_PLUGIN_DIR . 'admin/appraisals-page.php';
    }
    
    public function render_settings_page() {
        include KOLLECT_IT_PLUGIN_DIR . 'admin/settings-page.php';
    }
}
