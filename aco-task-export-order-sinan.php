<?php
/**
 * Plugin Name: ACO Task Export WooCommerce Orders
 * Plugin URI: https://github.com/de-er-kid/aco-task-export-order-sinan
 * Description: A plugin to export WooCommerce orders by date range with customizable fields to CSV or PDF.
 * Version: 1.0.0
 * Author: Sinan
 * Author URI: https://github.com/de-er-kid
 * Text Domain: aco-task-export-order-sinan
 * WC requires at least: 3.0.0
 * WC tested up to: 7.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('ACO_EXPORT_ORDERS_VERSION', '1.0.0');
define('ACO_EXPORT_ORDERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ACO_EXPORT_ORDERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ACO_EXPORT_ORDERS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once ACO_EXPORT_ORDERS_PLUGIN_DIR . 'includes/class-aco-export-orders-activator.php';
require_once ACO_EXPORT_ORDERS_PLUGIN_DIR . 'includes/class-aco-export-orders-deactivator.php';
require_once ACO_EXPORT_ORDERS_PLUGIN_DIR . 'includes/class-aco-export-orders.php';

// Register activation/deactivation hooks
register_activation_hook(__FILE__, array('ACO_Export_Orders_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('ACO_Export_Orders_Deactivator', 'deactivate'));

// Initialize the plugin
function run_aco_export_orders() {
    $plugin = new ACO_Export_Orders();
    $plugin->run();
}
run_aco_export_orders();