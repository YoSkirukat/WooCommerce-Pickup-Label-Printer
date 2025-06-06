<?php
/*
 * Plugin Name: WooCommerce Pickup Label Printer
 * Description: A plugin to print labels for pickup orders in WooCommerce.
 * Version: 1.0.1
 * Author: Ivan Malyshev
 * Author URI: https://yourwebsite.com
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include TCPDF library for PDF generation
require_once plugin_dir_path( __FILE__ ) . 'tcpdf/tcpdf.php';

// Add AJAX handler for printing label
add_action( 'wp_ajax_print_order_label', 'print_order_label_callback' );
function print_order_label_callback() {
    check_ajax_referer( 'print-order-label' );
    
    if ( ! current_user_can( 'edit_shop_orders' ) ) {
        wp_die( __( 'У вас нет прав для выполнения этого действия.', 'woocommerce' ) );
    }
    
    $order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
    $order = wc_get_order( $order_id );
    
    if ( ! $order ) {
        wp_die( __( 'Заказ не найден.', 'woocommerce' ) );
    }
    
    // Create new PDF document
    $pdf = new TCPDF( 'L', 'mm', array( 58, 40 ), true, 'UTF-8', false );
    
    // Set document information
    $pdf->SetCreator( PDF_CREATOR );
    $pdf->SetAuthor( 'WooCommerce Pickup Label Printer' );
    $pdf->SetTitle( 'Order Label #' . $order_id );
    
    // Remove default header/footer
    $pdf->setPrintHeader( false );
    $pdf->setPrintFooter( false );
    
    // Set margins
    $pdf->SetMargins( 5, 5, 5 );
    $pdf->SetAutoPageBreak( false );
    
    // Add a page
    $pdf->AddPage();
    
    // Set font for order number
    $pdf->SetFont( 'dejavusans', 'B', 24 );
    $pdf->Cell( 48, 10, $order_id, 0, 1, 'C' );
    
    // Set font for customer name
    $pdf->SetFont( 'dejavusans', '', 12 );
    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $pdf->Cell( 48, 8, $customer_name, 0, 1, 'C' );
    
    // Set font for payment method
    $pdf->SetFont( 'dejavusans', '', 9 );
    $payment_method = $order->get_payment_method_title();
    $pdf->MultiCell( 48, 6, $payment_method, 0, 'L', false, 1, '', '', true, 0, false, true, 12 );
    
    // Output the PDF
    $pdf->Output( 'order_label_' . $order_id . '.pdf', 'D' );
    
    wp_die();
}

// Add print label button to order edit page in the main content area
add_action( 'woocommerce_admin_order_data_after_order_details', 'render_print_label_button_main_area' );
function render_print_label_button_main_area( $order ) {
    $has_physical_product = false;
    foreach ( $order->get_items() as $item ) {
        $product = $item->get_product();
        if ( $product && ! $product->is_virtual() && ! $product->is_downloadable() ) {
            $has_physical_product = true;
            break;
        }
    }
    if ( $has_physical_product ) {
        $url = wp_nonce_url( admin_url( 'admin-ajax.php?action=print_order_label&order_id=' . $order->get_id() ), 'print-order-label' );
        echo '<p class="form-field form-field-wide"><a href="' . esc_url( $url ) . '" class="button alt">' . __( 'Печать этикетки', 'woocommerce' ) . '</a></p>';
    }
}

// Add CSS for styling the print label button
add_action( 'admin_head', 'style_print_label_button' );
function style_print_label_button() {
    echo '<style>
        .form-field-wide a.button.alt {
            margin-top: 25px;
        }
    </style>';
} 