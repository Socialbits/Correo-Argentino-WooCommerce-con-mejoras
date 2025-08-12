<?php
defined('ABSPATH') || exit;
const CA_IS_FIRST_TIME = 'woocommerce_correoargentino_is_first_time';
const CA_SETTINGS = 'woocommerce_correoargentino_settings';
const CA_IS_CONNECTED = 'woocommerce_correoargentino_is_connected';
const CA_AGREEMENT_FIELD = 'woocommerce_correoargentino_agreement';
const CA_APIKEY_FIELD = 'woocommerce_correoargentino_apiKey';
const CA_SANDBOX_FIELD = 'woocommerce_correoargentino_sandbox';
const CA_CREDENTIALS = 'woocommerce_correoargentino_credentials';
const CA_PLUGIN_ID = 'correoargentino';
const CA_CHOSEN_BRANCH = '_correoargentino_chosen_branch';
const CA_CHOSEN_BRANCH_NAME = '_correoargentino_chosen_branch_name';
const CA_TRACKING_NUMBER = '_correoargentino_tracking_number';
const CA_TRACKING_URL = 'https://www.correoargentino.com.ar/formularios/e-commerce?id=';
const CA_API_URL = 'https://api.correoargentino.com.ar/paqar';
const CA_API_SANDBOX_URL = 'https://apitest.correoargentino.com.ar/paqar/api';

add_action('wp_enqueue_scripts', 'woo_dequeue_select2', 100);
add_action('woocommerce_review_order_before_cart_contents', 'correoargentino_validate_order', 10);
add_action('woocommerce_after_checkout_validation', 'correoargentino_validate_order', 10);
add_filter('woocommerce_shipping_methods', 'add_Correo_Argentino_Shipping_Method');
add_action('woocommerce_shipping_init', 'Correo_Argentino_Shipping_Method');
add_action('add_meta_boxes', [CorreoArgentinoMetabox::class, 'create']);
add_action('rest_api_init', [CorreoArgentinoLabel::class, 'create']);
add_action('admin_menu', [CorreoArgentinoOrder::class, 'created']);
add_action('woocommerce_after_shipping_rate', [CorreoArgentinoBranch::class, 'showBranches']);
add_action('woocommerce_review_order_before_payment', 'branch_info_handler');
add_action('wp_ajax_update_branch_handler', 'update_branch_handler');
add_action("wp_enqueue_scripts", "add_select2_scripts");
add_action('wp_footer', 'choose_branch_handler');


add_action('woocommerce_new_order', 'new_order_handler');
// Payment complete

add_action('woocommerce_order_status_completed', 'on_payment_complete_handler', 10);
// When order is cancelled we need to cancel the shipping as well
add_action('woocommerce_order_status_cancelled', 'on_order_cancelled_handler', 10);

add_action('woocommerce_update_options_shipping_' . CA_PLUGIN_ID, 'on_settings_updated_handler');

add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');

// Keep this to ensure the request works properly
add_filter('http_request_timeout', function ($timeout) {
    return 10;
});

function __set_curl_to_follow( &$handle ) {
    curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, true );
}
add_action( 'http_api_curl', '__set_curl_to_follow' );

add_filter('woocommerce_default_address_fields', 'filter_default_address_fields', 20, 1);
function filter_default_address_fields($address_fields)
{
    unset($address_fields['address_2']);
    $address_fields['address_2']['required'] = true;
    $address_fields['address_2']['priority'] = 60;
    $address_fields['address_2']['placeholder'] = 'Altura';
    $address_fields['address_2']['label'] = 'Altura';

    return $address_fields;
}

add_filter('woocommerce_billing_fields', 'filter_billing_fields', 20, 1);
function filter_billing_fields($billing_fields)
{
    $billing_fields['billing_address_2']['required'] = true;
    $billing_fields['billing_address_2']['placeholder'] = 'Altura';
    $billing_fields['billing_address_2']['label'] = 'Altura';
    return $billing_fields;
}



