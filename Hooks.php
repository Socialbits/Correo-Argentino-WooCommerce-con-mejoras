<?php
defined('ABSPATH') || exit;
const CA_PLUGIN_ID = 'correoargentino';
const CA_CHOSEN_BRANCH = '_correoargentino_chosen_branch';
const CA_CHOSEN_BRANCH_NAME = '_correoargentino_chosen_branch_name';
const CA_TRACKING_NUMBER = '_correoargentino_tracking_number';
const CA_TOKEN_NAME = 'woocommerce_correoargentino_token';
const CA_TOKEN_EXPIRES = 'woocommerce_correoargentino_token_expires';
const CA_TOKEN_USERNAME = 'woocommerce_correoargentino_token_username';
const CA_TRACKING_URL = 'https://www.correoargentino.com.ar/formularios/e-commerce?id=';

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
add_action('woocommerce_order_status_completed', 'on_payment_complete_handler');
// When order is cancelled we need to cancel the shipping as well
add_action('woocommerce_order_status_cancelled', 'on_order_cancelled_handler');
// Shows a notices when the token needs to be updated
add_action('admin_notices', 'token_info_handler');

add_action('woocommerce_update_options_shipping_' . CA_PLUGIN_ID, 'on_settings_updated_handler');

