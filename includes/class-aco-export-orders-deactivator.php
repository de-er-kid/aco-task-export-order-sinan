<?php
/**
 * Fired during plugin deactivation
 */
class ACO_Export_Orders_Deactivator {
    public static function deactivate() {
        // Remove custom capabilities
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->remove_cap('export_woocommerce_orders');
        }
        
        // Clean up transients
        delete_transient('aco_export_orders_field_options');
    }
}