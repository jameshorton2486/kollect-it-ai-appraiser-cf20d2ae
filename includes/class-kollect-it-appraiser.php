
<?php
class Kollect_It_Appraiser {
    private $handlers;
    private $menu;
    
    public function __construct() {
        $this->handlers = new Kollect_It_Handlers();
        $this->menu = new Kollect_It_Menu();
    }
    
    public function init() {
        // Initialize AJAX handlers
        $this->handlers->init();
        
        // Add admin menu
        add_action('admin_menu', array($this->menu, 'add_admin_menu'));
    }
}
