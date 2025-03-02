<?php
/**
 * The core plugin class
 */
class ACO_Export_Orders {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'aco-task-export-order-sinan';
        $this->version = ACO_EXPORT_ORDERS_VERSION;
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_api_hooks();
    }

    private function load_dependencies() {
        require_once ACO_EXPORT_ORDERS_PLUGIN_DIR . 'includes/class-aco-export-orders-api.php';
        require_once ACO_EXPORT_ORDERS_PLUGIN_DIR . 'includes/class-aco-export-orders-csv.php';
        require_once ACO_EXPORT_ORDERS_PLUGIN_DIR . 'includes/class-aco-export-orders-pdf.php';
    }

    private function define_admin_hooks() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add Export Orders button to WooCommerce Orders page
        add_action('admin_head', array($this, 'add_export_orders_button'));
    }

    private function define_api_hooks() {
        $api = new ACO_Export_Orders_API();
        add_action('rest_api_init', array($api, 'register_routes'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Export Orders', 'aco-task-export-order-sinan'),
            __('Export Orders', 'aco-task-export-order-sinan'),
            'export_woocommerce_orders',
            'aco-export-orders',
            array($this, 'display_admin_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        if ('woocommerce_page_aco-export-orders' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'aco-export-orders-admin',
            ACO_EXPORT_ORDERS_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            $this->version
        );

        wp_enqueue_script(
            'aco-export-orders-admin',
            ACO_EXPORT_ORDERS_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery', 'wp-element', 'wp-api-fetch'),
            $this->version,
            true
        );

        wp_localize_script('aco-export-orders-admin', 'acoExportOrdersSettings', array(
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }

    public function display_admin_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Export WooCommerce Orders', 'aco-task-export-order-sinan') . '</h1>';
        echo '<div id="aco-export-orders-app"></div>';
        echo '</div>';
    }

    public function add_export_orders_button() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'woocommerce_page_wc-orders') {
            echo '<style>
                .aco-export-orders-button {
                    margin-left: 10px;
                }
            </style>';
            echo '<a href="' . admin_url('admin.php?page=aco-export-orders') . '" class="page-title-action aco-export-orders-button">' . esc_html__('Export Orders', 'aco-task-export-order-sinan') . '</a>';
        }
    }

    public function run() {
        // The plugin is now running
    }
}
