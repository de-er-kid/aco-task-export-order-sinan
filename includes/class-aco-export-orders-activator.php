<?php
/**
 * Fired during plugin activation
 */
class ACO_Export_Orders_Activator {
    public static function activate() {
        // Check if WooCommerce is active
        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('This plugin requires WooCommerce to be installed and active.', 'aco-task-export-order-sinan'));
        }
        
        // Add custom capabilities
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('export_woocommerce_orders');
        }
        
        // Create necessary directories
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/wc-exports';
        if (!file_exists($export_dir)) {
            mkdir($export_dir, 0755, true);
        }
        
        // Create index.php file in the export directory for security
        if (!file_exists($export_dir . '/index.php')) {
            $index_file = fopen($export_dir . '/index.php', 'w');
            fwrite($index_file, '<?php // Silence is golden');
            fclose($index_file);
        }
    }
}