# WooCommerce Pickup Label Printer

## Description

This plugin allows WooCommerce store administrators to print labels for orders containing physical products. The label includes the order number, customer name, and payment method. The plugin integrates seamlessly with WooCommerce, providing a 'Print Label' button on the order edit page for eligible orders.

## Features

- Prints labels in PDF format for orders with physical products.
- Displays order number, customer name, and payment method on the label.
- Label size is optimized for 58x40 mm thermal printer stickers.
- 'Print Label' button appears in the main content area of the order edit page.
- Custom styling for the button with a top margin for better visibility.

## Requirements

- WordPress 6.5.5 or higher
- WooCommerce 3.6.7 or higher
- TCPDF library (included in the plugin folder)

## Installation

1. Upload the plugin folder to the `wp-content/plugins` directory of your WordPress installation.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure the `tcpdf` folder is present in the plugin directory for PDF generation.

## Usage

1. Go to WooCommerce > Orders in the WordPress admin panel.
2. Open an order that contains physical products.
3. Look for the 'Print Label' button under the 'Status' and 'Customer' sections on the order edit page.
4. Click the button to download a PDF label formatted for a 58x40 mm thermal printer.

## License

This plugin is licensed under GPL2. See the plugin header for more details.

## Author

- Your Name
- Website: https://yourwebsite.com 