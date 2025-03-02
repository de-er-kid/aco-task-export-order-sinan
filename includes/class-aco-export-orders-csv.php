<?php
/**
 * Handles the CSV export functionality
 */
class ACO_Export_Orders_CSV {
    private $order_ids;
    private $fields;
    private $filename;
    
    public function __construct($order_ids, $fields, $filename) {
        $this->order_ids = $order_ids;
        $this->fields = $fields;
        $this->filename = $filename . '.csv';
    }
    
    public function generate() {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/wc-exports';
        $file_path = $export_dir . '/' . $this->filename;
        
        // Create file and write header
        $file = fopen($file_path, 'w');
        
        if (!$file) {
            return new WP_Error('file_creation_failed', __('Failed to create the export file', 'aco-task-export-order-sinan'));
        }
        
        // Write UTF-8 BOM
        fputs($file, "\xEF\xBB\xBF");
        
        // Prepare headers
        $headers = array();
        $available_fields = $this->get_all_available_fields();
        
        foreach ($this->fields as $field) {
            if (isset($available_fields['standard'][$field])) {
                $headers[] = $available_fields['standard'][$field];
            } elseif (isset($available_fields['addons'][$field])) {
                $headers[] = $available_fields['addons'][$field];
            } else {
                $headers[] = $field; // Use the field key if label not found
            }
        }
        
        // Write header row
        fputcsv($file, $headers);
        
        // Write order data
        foreach ($this->order_ids as $order_id) {
            $order = wc_get_order($order_id);
            
            if (!$order) {
                continue;
            }
            
            // For orders with multiple items, we'll have multiple rows
            $items = $order->get_items();
            
            if (empty($items)) {
                // Write a single row for the order without line items
                $row_data = $this->prepare_order_data($order, null);
                fputcsv($file, $row_data);
            } else {
                foreach ($items as $item) {
                    $row_data = $this->prepare_order_data($order, $item);
                    fputcsv($file, $row_data);
                }
            }
        }
        
        fclose($file);
        
        // Return the URL to download the file
        return $upload_dir['baseurl'] . '/wc-exports/' . $this->filename;
    }
    
    private function get_all_available_fields() {
        $api = new ACO_Export_Orders_API();
        $response = $api->get_available_fields();
        return $response->get_data();
    }
    
    private function prepare_order_data($order, $item) {
        $data = array();
        
        foreach ($this->fields as $field) {
            switch ($field) {
                case 'order_number':
                    $data[] = $order->get_order_number();
                    break;
        
                case 'order_status':
                    $data[] = $order->get_status();
                    break;
        
                case 'order_date':
                    $data[] = $order->get_date_created()->date('Y-m-d H:i:s');
                    break;
        
                case 'customer_id':
                    $data[] = $order->get_customer_id();
                    break;
        
                case 'customer_name':
                    $data[] = $order->get_formatted_billing_full_name();
                    break;
        
                case 'customer_email':
                    $data[] = $order->get_billing_email();
                    break;
        
                case 'customer_phone':
                    $data[] = $order->get_billing_phone();
                    break;
        
                case 'customer_note':
                    $data[] = $order->get_customer_note();
                    break;
        
                case 'payment_method':
                    $data[] = $order->get_payment_method_title();
                    break;
        
                case 'billing_first_name':
                    $data[] = $order->get_billing_first_name();
                    break;
        
                case 'billing_last_name':
                    $data[] = $order->get_billing_last_name();
                    break;
        
                case 'billing_address_1':
                    $data[] = $order->get_billing_address_1();
                    break;
        
                case 'billing_address_2':
                    $data[] = $order->get_billing_address_2();
                    break;
        
                case 'billing_city':
                    $data[] = $order->get_billing_city();
                    break;
        
                case 'billing_state':
                    $data[] = $order->get_billing_state();
                    break;
        
                case 'billing_postcode':
                    $data[] = $order->get_billing_postcode();
                    break;
        
                case 'billing_country':
                    $data[] = $order->get_billing_country();
                    break;
        
                case 'billing_phone':
                    $data[] = $order->get_billing_phone();
                    break;
        
                case 'billing_email':
                    $data[] = $order->get_billing_email();
                    break;
        
                case 'shipping_first_name':
                    $data[] = $order->get_shipping_first_name();
                    break;
        
                case 'shipping_last_name':
                    $data[] = $order->get_shipping_last_name();
                    break;
        
                case 'shipping_address_1':
                    $data[] = $order->get_shipping_address_1();
                    break;
        
                case 'shipping_address_2':
                    $data[] = $order->get_shipping_address_2();
                    break;
        
                case 'shipping_city':
                    $data[] = $order->get_shipping_city();
                    break;
        
                case 'shipping_state':
                    $data[] = $order->get_shipping_state();
                    break;
        
                case 'shipping_postcode':
                    $data[] = $order->get_shipping_postcode();
                    break;
        
                case 'shipping_country':
                    $data[] = $order->get_shipping_country();
                    break;
        
                case 'product_name':
                    $data[] = $item ? $item->get_name() : '';
                    break;
        
                case 'sku':
                    $product = $item ? $item->get_product() : null;
                    $data[] = $product ? $product->get_sku() : '';
                    break;
        
                case 'item':
                    $data[] = $item ? $item->get_id() : '';
                    break;
        
                case 'quantity':
                    $data[] = $item ? $item->get_quantity() : '';
                    break;
        
                case 'item_cost':
                    $data[] = $item ? wc_format_decimal($item->get_total(), 2) : '';
                    break;
        
                case 'cart_discount_amount':
                    $data[] = $order->get_discount_total();
                    break;
        
                case 'shipping_method_title':
                    $data[] = $order->get_shipping_method();
                    break;
        
                case 'subtotal':
                    $data[] = $order->get_subtotal();
                    break;
        
                case 'total':
                    $data[] = $order->get_total();
                    break;
        
                case 'discount_total':
                    $data[] = $order->get_discount_total();
                    break;
        
                case 'tax_total':
                    $data[] = $order->get_total_tax();
                    break;
        
                case 'shipping_total':
                    $data[] = $order->get_shipping_total();
                    break;
        
                case 'product_addons':
                    $addons = $item ? wc_get_order_item_meta($item->get_id(), '_wcpa_product_addons', true) : '';
                    $data[] = is_array($addons) ? implode(', ', $addons) : $addons;
                    break;
        
                default:
                    // Check if it's a custom addon field
                    if ($item && substr($field, 0, 1) === '_') {
                        $meta_value = wc_get_order_item_meta($item->get_id(), $field, true);
                        $data[] = $meta_value;
                    } elseif ($item) {
                        $meta_value = wc_get_order_item_meta($item->get_id(), $field, true);
                        $data[] = $meta_value;
                    } else {
                        $data[] = '';
                    }
                    break;
            }
        }        
        
        return $data;
    }
}