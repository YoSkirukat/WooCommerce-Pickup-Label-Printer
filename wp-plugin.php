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

// Add custom action link in order list
add_filter( 'woocommerce_admin_order_actions', 'add_print_label_action', 10, 2 );
function add_print_label_action( $actions, $order ) {
    // Debug output to check if function is called
    error_log( 'Order #' . $order->get_id() . ' - Adding label button for all orders.' );
    $actions['print_label'] = array(
        'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=print_order_label&order_id=' . $order->get_id() ), 'print-order-label' ),
        'name'   => __( 'Этикетка', 'woocommerce' ),
        'action' => 'print_label',
    );
    return $actions;
}

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
    $pdf->SetFont( 'dejavusans', 'B', 20 );
    $pdf->Cell( 48, 10, '#' . $order_id, 0, 1, 'C' );
    
    // Set font for customer name
    $pdf->SetFont( 'dejavusans', '', 12 );
    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $pdf->Cell( 48, 10, $customer_name, 0, 1, 'C' );
    
    // Output the PDF
    $pdf->Output( 'order_label_' . $order_id . '.pdf', 'D' );
    
    wp_die();
}

// Add CSS for custom action button
add_action( 'admin_head', 'custom_order_actions_style' );
function custom_order_actions_style() {
    echo '<style>
        .wc-action-button-print_label::after {
            font-family: WooCommerce;
            content: "\e010";
        }
    </style>';
}

function my_custom_plugin_init() {
    // Initialization code here.
}
add_action( 'init', 'my_custom_plugin_init' );

// Add print label button to order edit page
add_action( 'woocommerce_order_actions', 'add_print_label_order_action' );
function add_print_label_order_action( $actions ) {
    global $the_order;
    if ( $the_order ) {
        $actions['print_label'] = array(
            'label'  => __( 'Печать этикетки', 'woocommerce' ),
            'action' => 'print_label',
            'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=print_order_label&order_id=' . $the_order->get_id() ), 'print-order-label' ),
        );
    }
    return $actions;
}

// Handle the button click on order edit page
add_action( 'woocommerce_order_action_print_label', 'handle_print_label_order_action' );
function handle_print_label_order_action( $order ) {
    // Redirect to AJAX URL to trigger PDF download
    $url = wp_nonce_url( admin_url( 'admin-ajax.php?action=print_order_label&order_id=' . $order->get_id() ), 'print-order-label' );
    wp_redirect( $url );
    exit;
}

// Add print label button to order edit page in the meta box
add_action( 'add_meta_boxes', 'add_print_label_meta_box' );
function add_print_label_meta_box() {
    add_meta_box(
        'print_label_meta_box',
        __( 'Печать этикетки', 'woocommerce' ),
        'render_print_label_meta_box',
        'shop_order',
        'side',
        'high'
    );
}

function render_print_label_meta_box( $post ) {
    $order = wc_get_order( $post->ID );
    if ( $order ) {
        $url = wp_nonce_url( admin_url( 'admin-ajax.php?action=print_order_label&order_id=' . $order->get_id() ), 'print-order-label' );
        echo '<a href="' . esc_url( $url ) . '" class="button">' . __( 'Печать этикетки', 'woocommerce' ) . '</a>';
    }
} 