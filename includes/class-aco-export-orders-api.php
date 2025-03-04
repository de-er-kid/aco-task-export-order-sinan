<?php
/**
 * Handles the REST API functionality
 */
class ACO_Export_Orders_API {
    
    public function register_routes() {
        // Get available fields for export
        register_rest_route('aco-export/v1', '/fields', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_available_fields'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Export orders
        register_rest_route('aco-export/v1', '/export', array(
            'methods' => 'POST',
            'callback' => array($this, 'export_orders'),
            'permission_callback' => array($this, 'check_permissions')
        ));
    }
    
    public function check_permissions() {
        return current_user_can('manage_woocommerce');
    }
    
    public function get_available_fields() {
        $fields = get_transient('aco_export_orders_field_options');
        
        if (false === $fields) {
            $standard_fields = array(
                'order_number' => __('Order Number', 'aco-task-export-order-sinan'),
                'order_status' => __('Order Status', 'aco-task-export-order-sinan'),
                'order_date' => __('Order Date', 'aco-task-export-order-sinan'),
                'customer_id' => __('Customer ID', 'aco-task-export-order-sinan'),
                'customer_name' => __('Customer Name', 'aco-task-export-order-sinan'),
                'customer_email' => __('Customer Email', 'aco-task-export-order-sinan'),
                'customer_phone' => __('Customer Phone', 'aco-task-export-order-sinan'),
                'customer_note' => __('Customer Note', 'aco-task-export-order-sinan'),
                'payment_method' => __('Payment Method', 'aco-task-export-order-sinan'),
                'billing_first_name' => __('Billing First Name', 'aco-task-export-order-sinan'),
                'billing_last_name' => __('Billing Last Name', 'aco-task-export-order-sinan'),
                'billing_address_1' => __('Billing Address 1', 'aco-task-export-order-sinan'),
                'billing_address_2' => __('Billing Address 2', 'aco-task-export-order-sinan'),
                'billing_city' => __('Billing City', 'aco-task-export-order-sinan'),
                'billing_state' => __('Billing State', 'aco-task-export-order-sinan'),
                'billing_postcode' => __('Billing Postcode', 'aco-task-export-order-sinan'),
                'billing_country' => __('Billing Country', 'aco-task-export-order-sinan'),
                'billing_phone' => __('Billing Phone', 'aco-task-export-order-sinan'),
                'billing_email' => __('Billing Email', 'aco-task-export-order-sinan'),
                'shipping_first_name' => __('Shipping First Name', 'aco-task-export-order-sinan'),
                'shipping_last_name' => __('Shipping Last Name', 'aco-task-export-order-sinan'),
                'shipping_address_1' => __('Shipping Address 1', 'aco-task-export-order-sinan'),
                'shipping_address_2' => __('Shipping Address 2', 'aco-task-export-order-sinan'),
                'shipping_city' => __('Shipping City', 'aco-task-export-order-sinan'),
                'shipping_state' => __('Shipping State', 'aco-task-export-order-sinan'),
                'shipping_postcode' => __('Shipping Postcode', 'aco-task-export-order-sinan'),
                'shipping_country' => __('Shipping Country', 'aco-task-export-order-sinan'),
                'product_name' => __('Product Name', 'aco-task-export-order-sinan'),
                'sku' => __('SKU', 'aco-task-export-order-sinan'),
                'quantity' => __('Quantity', 'aco-task-export-order-sinan'),
                'item_cost' => __('Item Cost', 'aco-task-export-order-sinan'),
                'cart_discount_amount' => __('Cart Discount Amount', 'aco-task-export-order-sinan'),
                'shipping_method_title' => __('Shipping Method Title', 'aco-task-export-order-sinan'),
                'product_addons' => __('Product Add-ons', 'aco-task-export-order-sinan'),
                'subtotal' => __('Subtotal', 'aco-task-export-order-sinan'),
                'total' => __('Total', 'aco-task-export-order-sinan'),
                'discount_total' => __('Discount Total', 'aco-task-export-order-sinan'),
                'tax_total' => __('Tax Total', 'aco-task-export-order-sinan'),
                'shipping_total' => __('Shipping Total', 'aco-task-export-order-sinan')
            );
            
            $custom_addon_fields = $this->get_custom_addon_fields();
            
            $fields = array(
                'standard' => $standard_fields,
                'addons' => $custom_addon_fields
            );
            
            set_transient('aco_export_orders_field_options', $fields, HOUR_IN_SECONDS);
        }
        
        return new WP_REST_Response($fields, 200);
    }
    
    private function get_custom_addon_fields() {
        global $wpdb;
        $addon_fields = array();
        
        // Query for both product addons and custom order item meta
        $query = "
            SELECT DISTINCT meta_key 
            FROM {$wpdb->prefix}woocommerce_order_itemmeta 
            WHERE meta_key NOT LIKE '\_%'
            AND meta_key NOT IN ('_product_id', '_variation_id', '_qty', '_tax_class', '_line_subtotal', '_line_subtotal_tax', '_line_total', '_line_tax', '_line_tax_data')
            UNION
            SELECT DISTINCT meta_key 
            FROM {$wpdb->prefix}woocommerce_order_itemmeta 
            WHERE meta_key LIKE '%_wcpa_%' 
            OR meta_key LIKE '%addon%' 
            OR meta_key LIKE '%custom_field%' 
            OR meta_key LIKE '%wcpb_%'
            OR meta_key LIKE '%_custom_%'
        ";
        
        $results = $wpdb->get_results($query);
        
        if ($results) {
            foreach ($results as $result) {
                $meta_key = $result->meta_key;
                // Clean up the label
                $label = preg_replace('/^_/', '', $meta_key); // Remove leading underscore
                $label = str_replace(array('wcpa_', 'addon_', 'custom_field_', 'wcpb_', 'custom_'), '', $label);
                $label = ucwords(str_replace(array('_', '-'), ' ', $label));
                $addon_fields[$meta_key] = $label;
            }
            
            // Sort fields alphabetically by label
            asort($addon_fields);
        }
        
        return $addon_fields;
    }
    
    private function get_order_item_meta_value($item_id, $meta_key) {
        global $wpdb;
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value 
            FROM {$wpdb->prefix}woocommerce_order_itemmeta 
            WHERE order_item_id = %d 
            AND meta_key = %s",
            $item_id,
            $meta_key
        ));
        
        return maybe_unserialize($value);
    }
    
    public function export_orders($request) {
        $params = $request->get_params();
        
        // Validate required parameters
        if (empty($params['start_date']) || empty($params['end_date']) || empty($params['fields']) || empty($params['format'])) {
            return new WP_Error('missing_params', __('Missing required parameters', 'aco-task-export-order-sinan'), array('status' => 400));
        }
        
        $start_date = sanitize_text_field($params['start_date']);
        $end_date = sanitize_text_field($params['end_date']);
        $fields = array_map('sanitize_text_field', $params['fields']);
        $format = sanitize_text_field($params['format']);
        
        // Validate date format
        if (!strtotime($start_date) || !strtotime($end_date)) {
            return new WP_Error('invalid_date', __('Invalid date format', 'aco-task-export-order-sinan'), array('status' => 400));
        }
        
        // Get orders within date range
        $args = array(
            'date_created' => strtotime($start_date) . '...' . strtotime($end_date . ' 23:59:59'),
            'limit' => -1,
            'return' => 'ids',
        );
        
        $order_ids = wc_get_orders($args);
        
        if (empty($order_ids)) {
            return new WP_Error('no_orders', __('No orders found within the selected date range', 'aco-task-export-order-sinan'), array('status' => 404));
        }
        
        // Generate filename
        $timestamp = current_time('timestamp');
        $filename = 'wc-orders-export-' . date('Y-m-d-H-i-s', $timestamp);
        
        // Process the export based on format
        switch ($format) {
            case 'csv':
                $exporter = new ACO_Export_Orders_CSV($order_ids, $fields, $filename);
                $result = $exporter->generate();
                break;
                
            case 'pdf':
                $exporter = new ACO_Export_Orders_PDF($order_ids, $fields, $filename);
                $result = $exporter->generate();
                break;
                
            default:
                return new WP_Error('invalid_format', __('Invalid export format', 'aco-task-export-order-sinan'), array('status' => 400));
        }
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'file_url' => $result,
            'message' => __('Export completed successfully', 'aco-task-export-order-sinan')
        ), 200);
    }
}