# ACO Task Export WooCommerce Orders

A WordPress plugin to export WooCommerce orders by date range with customizable fields to CSV or PDF formats.

## Description

ACO Task Export WooCommerce Orders provides a user-friendly interface for WordPress administrators to export WooCommerce order data. The plugin allows for customization of export fields, date ranges, and output formats, making it a versatile tool for order management and reporting.

## Features

- Export WooCommerce orders to CSV or PDF formats
- Filter orders by custom date ranges
- Select specific order fields to include in exports
- Drag-and-drop interface for field arrangement
- Preset date ranges for quick selection
- Secure file handling with proper permissions
- Modern, responsive admin interface

## Requirements

- WordPress 5.0 or higher
- WooCommerce 3.0.0 or higher
- PHP 7.4 or higher

## Installation

1. Upload the `aco-task-export-order-sinan` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to WooCommerce > Export Orders to use the plugin

## Usage

### Accessing the Export Interface

4. Log in to your WordPress admin dashboard
5. Navigate to WooCommerce > Export Orders
6. Use the interface to configure your export settings

### Configuring Export Settings

7. **Select Date Range**: Choose a specific date range for orders to export
   - Use the date picker to select custom start and end dates
   - Or use preset options like "Last 7 days", "This month", etc.

8. **Select Fields**: Choose which order fields to include in your export
   - Standard fields (Order Number, Date, Status, etc.)
   - Billing information
   - Shipping information
   - Product details
   - Custom fields

9. **Arrange Fields**: Drag and drop fields to arrange them in your preferred order

10. **Choose Format**: Select CSV or PDF as your export format

11. **Generate Export**: Click the "Export Orders" button to generate and download your file

## API Endpoints

The plugin provides REST API endpoints for programmatic access to the export functionality. All endpoints require administrator privileges with the `manage_woocommerce` capability.

### Available Endpoints

#### 1. Get Available Fields

Retrieves all available fields that can be exported.

- **Endpoint**: `/wp-json/aco-export/v1/fields`
- **Method**: GET
- **Authentication**: Required (WordPress cookie authentication)
- **Response**: JSON array of available fields grouped by category
- **Example Response**:

```json
{
  "standard": {
    "order_number": "Order Number",
    "order_status": "Order Status",
    "order_date": "Order Date",
    "order_total": "Order Total"
  },
  "billing": {
    "billing_first_name": "Billing First Name",
    "billing_last_name": "Billing Last Name",
    "billing_email": "Billing Email"
  },
  "shipping": {
    "shipping_first_name": "Shipping First Name",
    "shipping_last_name": "Shipping Last Name"
  }
}```

#### 2. Export Orders
Generates an export file based on provided parameters.

- Endpoint : /wp-json/aco-export/v1/export
- Method : POST
- Authentication : Required (WordPress cookie authentication)
- Request Body Parameters :
  - start_date (string): Start date in Y-m-d format
  - end_date (string): End date in Y-m-d format
  - fields (array): Array of field keys to include in export
  - format (string): Export format ('csv' or 'pdf')
- Response : JSON object with export file information
- Example Request :

``` json
{
  "start_date": "2023-01-01",
  "end_date": "2023-01-31",
  "fields": ["order_number", "order_date", "order_total", "billing_email"],
  "format": "csv"
}
```

- Example Response :
```json
{
  "success": true,
  "file_url": "https://example.com/wp-content/uploads/wc-exports/orders-export-20230201-123456.csv",
  "count": 25
}
```

## Testing API Endpoints
You can test the API endpoints using various tools like Postman, cURL, or the WordPress built-in REST API testing tool.

### Using cURL Get Available Fields
```bash
curl -X GET \
  https://your-site.com/wp-json/aco-export/v1/fields \
  -H 'Content-Type: application/json' \
  -H 'X-WP-Nonce: your_nonce' \
  --cookie "wordpress_logged_in_cookie=your_auth_cookie"
 ```
 
 Export Orders
```bash
curl -X POST \
  https://your-site.com/wp-json/aco-export/v1/export \
  -H 'Content-Type: application/json' \
  -H 'X-WP-Nonce: your_nonce' \
  --cookie "wordpress_logged_in_cookie=your_auth_cookie" \
  -d '{
    "start_date": "2023-01-01",
    "end_date": "2023-01-31",
    "fields": ["order_number", "order_date", "order_total"],
    "format": "csv"
  }'
 ```
### Using the WordPress REST API Testing Tool
1. Install and activate the REST API Toolbox plugin
2. Navigate to Tools > REST API Toolbox
3. Use the interface to test your endpoints
## Troubleshooting

### Common Issues
1. Export button doesn't work
   
   - Ensure JavaScript is enabled in your browser
   - Check browser console for errors
   - Verify you have proper permissions (must be an administrator)
2. Empty export file
   
   - Verify there are orders within the selected date range
   - Check that you've selected at least one field to export
3. Permission errors
   
   - Ensure your uploads directory is writable
   - The plugin creates a 'wc-exports' directory in your uploads folder
4. API errors
   
   - Verify you're properly authenticated
   - Check that the request format matches the expected format
   - Ensure all required parameters are provided
## Extending the Plugin
Developers can extend the plugin using WordPress filters:

### Available Filters
- aco_export_orders_field_options : Modify available export fields
- aco_export_orders_csv_data : Modify CSV data before export
- aco_export_orders_pdf_data : Modify PDF data before export
- aco_export_orders_query_args : Modify WooCommerce order query arguments
## License
This plugin is licensed under the GPL v2 or later.

## Credits
Developed by Sinan for ACO Task.

## Changelog
### 1.0.0
- Initial release