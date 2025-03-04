<?php
/**
 * Handles the PDF export functionality with dynamic page size
 */
class ACO_Export_Orders_PDF {
    private $order_ids;
    private $fields;
    private $filename;
    
    public function __construct($order_ids, $fields, $filename) {
        $this->order_ids = $order_ids;
        $this->fields = $fields;
        $this->filename = $filename . '.pdf';
    }
    
    public function generate() {
        // Check if TCPDF or FPDF is already included
        if (!class_exists('TCPDF') && !class_exists('FPDF')) {
            // Include TCPDF library
            require_once ACO_EXPORT_ORDERS_PLUGIN_DIR . 'vendor/tcpdf/tcpdf.php';
            
            // If TCPDF is not available, return an error
            if (!class_exists('TCPDF')) {
                return new WP_Error('tcpdf_missing', __('TCPDF library is required for PDF exports', 'aco-task-export-order-sinan'));
            }
        }
        
        // Determine page format and orientation based on number of fields
        $page_format = 'A4';
        $orientation = 'P'; // Default to portrait
        
        $field_count = count($this->fields);
        
        // Dynamic format selection based on field count
        if ($field_count <= 5) {
            // Use A4 portrait for small number of columns
            $page_format = 'A4';
            $orientation = 'P';
        } elseif ($field_count <= 8) {
            // Use A4 landscape for medium number of columns
            $page_format = 'A4';
            $orientation = 'L';
        } else {
            // Use A3 landscape for large number of columns
            $page_format = 'A3';
            $orientation = 'L';
        }
        
        // Create new PDF document with dynamic format and orientation
        $pdf = new TCPDF($orientation, PDF_UNIT, $page_format, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(get_bloginfo('name'));
        $pdf->SetAuthor(wp_get_current_user()->display_name);
        $pdf->SetTitle(__('WooCommerce Orders Export', 'aco-task-export-order-sinan'));
        $pdf->SetSubject(__('WooCommerce Orders Export', 'aco-task-export-order-sinan'));
        
        // Set default header and footer data
        $pdf->SetHeaderData('', 0, get_bloginfo('name'), __('WooCommerce Orders Export', 'aco-task-export-order-sinan'));
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Add a page
        $pdf->AddPage();
        
        // Get all available fields
        $available_fields = $this->get_all_available_fields();
        
        // Prepare headers
        $headers = array();
        foreach ($this->fields as $field) {
            if (isset($available_fields['standard'][$field])) {
                $headers[] = $available_fields['standard'][$field];
            } elseif (isset($available_fields['addons'][$field])) {
                $headers[] = $available_fields['addons'][$field];
            } else {
                $headers[] = $field; // Use the field key if label not found
            }
        }
        
        // Calculate content width based on page format and orientation
        $content_width = $this->get_content_width($page_format, $orientation);
        
        // Calculate column width based on the available content width
        $num_fields = count($headers);
        $column_width = $content_width / $num_fields;
        
        // Create the table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        
        $x_pos = $pdf->GetX();
        $y_pos = $pdf->GetY();
        
        foreach ($headers as $header) {
            $pdf->MultiCell($column_width, 10, $header, 1, 'C', true, 0, '', '', true, 0, false, true, 10, 'M');
        }
        $pdf->Ln();
        
        // Reset font
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetFillColor(255, 255, 255);
        
        // Add data rows
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
                
                foreach ($row_data as $cell) {
                    $pdf->MultiCell($column_width, 10, $cell, 1, 'L', false, 0, '', '', true, 0, false, true, 10, 'M');
                }
                $pdf->Ln();
            } else {
                foreach ($items as $item) {
                    $row_data = $this->prepare_order_data($order, $item);
                    
                    foreach ($row_data as $cell) {
                        $pdf->MultiCell($column_width, 10, $cell, 1, 'L', false, 0, '', '', true, 0, false, true, 10, 'M');
                    }
                    $pdf->Ln();
                    
                    // Check if we need a new page
                    if ($pdf->GetY() > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();
                    }
                }
            }
        }
        
        // Save PDF to file
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/wc-exports';
        $file_path = $export_dir . '/' . $this->filename;
        
        $pdf->Output($file_path, 'F');
        
        // Return the URL to download the file
        return $upload_dir['baseurl'] . '/wc-exports/' . $this->filename;
    }
    
    /**
     * Get content width in millimeters based on page format and orientation
     * 
     * @param string $format Page format (A4, A3, etc.)
     * @param string $orientation Page orientation (P or L)
     * @return float Content width in millimeters
     */
    private function get_content_width($format, $orientation) {
        // Standard page dimensions (width x height) in mm
        $page_dimensions = array(
            'A4' => array(210, 297),
            'A3' => array(297, 420),
            'A2' => array(420, 594),
            'A1' => array(594, 841),
        );
        
        // Get page dimensions
        $dimensions = isset($page_dimensions[$format]) ? $page_dimensions[$format] : $page_dimensions['A4'];
        
        // Determine page width based on orientation
        $page_width = ($orientation === 'P') ? $dimensions[0] : $dimensions[1];
        
        // Subtract margins to get content width
        // Assuming left and right margins are both PDF_MARGIN_LEFT and PDF_MARGIN_RIGHT respectively
        // Using approximate values here: 15mm for each side
        $margin_left = defined('PDF_MARGIN_LEFT') ? PDF_MARGIN_LEFT : 15;
        $margin_right = defined('PDF_MARGIN_RIGHT') ? PDF_MARGIN_RIGHT : 15;
        
        return $page_width - $margin_left - $margin_right;
    }
    
    private function get_all_available_fields() {
        $api = new ACO_Export_Orders_API();
        $response = $api->get_available_fields();
        return $response->get_data();
    }
    
    private function prepare_order_data($order, $item) {
        // This is the same as in the CSV class
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
                        // Ensure the value is properly formatted for PDF
                        if (is_array($meta_value) || is_object($meta_value)) {
                            $data[] = json_encode($meta_value);
                        } else {
                            $data[] = strval($meta_value);
                        }
                    } else {
                        // Handle custom order item meta
                        if ($item) {
                            $meta_value = wc_get_order_item_meta($item->get_id(), $field, true);
                            if (is_array($meta_value) || is_object($meta_value)) {
                                $data[] = json_encode($meta_value);
                            } elseif ($meta_value !== null && $meta_value !== '') {
                                $data[] = strval($meta_value);
                            } else {
                                $data[] = '';
                            }
                        } else {
                            $data[] = '';
                        }
                    }
                    break;
            }
        } 
        
        return $data;
    }
}