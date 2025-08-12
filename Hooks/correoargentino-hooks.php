<?php
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', 'woo_dequeue_select2', 100);
/**
 * Remove Woocommerce Select2 - Woocommerce 3.2.1+
 */
function woo_dequeue_select2()
{
    if (class_exists('woocommerce')) {
        wp_dequeue_style('select2');
        wp_deregister_style('select2');

        wp_dequeue_script('selectWoo');
        wp_deregister_script('selectWoo');
    }
}

add_action('woocommerce_review_order_before_cart_contents', 'correoargentino_validate_order', 10);
add_action('woocommerce_after_checkout_validation', 'correoargentino_validate_order', 10);
add_filter('woocommerce_shipping_methods', function ($methods) {
    $methods['correoargentino_shipping_method'] = 'WC_Correo_Argentino_Shipping_Method';
    return $methods;
});
add_action('woocommerce_shipping_init', 'Correo_Argentino_Shipping_Method');
function add_Correo_Argentino_Shipping_Method($methods)
{
    $methods['correoargentino_shipping'] = 'Correo_Argentino_Shipping_Method';
    return $methods;
}

add_action('add_meta_boxes', [CorreoArgentinoMetabox::class, 'create']);
add_action('rest_api_init', [CorreoArgentinoLabel::class, 'create']);
add_action('admin_menu', [CorreoArgentinoOrder::class, 'build_admin_menu']);
add_action('woocommerce_after_shipping_rate', [CorreoArgentinoBranch::class, 'showBranches']);
add_action('woocommerce_review_order_before_payment', function () {
    echo "
        <div class='correo_argentino_review_order_section'>
            <table class='shop_table'>
                <tbody>
                    <tr>
                        <td>
                            <address id='correo_argentino_review_order_section_address' ></address>
                        </td>
                    </tr>
                </tbody>
            </table>        
        </div>
    ";
});

add_action('wp_ajax_update_branch_handler', 'update_branch_handler');
add_action('wp_ajax_nopriv_update_branch_handler', 'update_branch_handler');

add_action('wp_ajax_update_province_handler', 'update_province_handler');
add_action('wp_ajax_nopriv_update_province_handler', 'update_province_handler');

add_action('wp_ajax_process_orders_handler', 'process_orders_handler');
add_action('wp_ajax_nopriv_process_orders_handler', 'process_orders_handler');

// add_action("wp_enqueue_scripts", "add_select2_scripts");
add_action("wp_footer", "add_select2_scripts");

add_action('wp_footer', 'choose_branch_handler');

// add_action('wp_footer', 'enqueue_global_scripts');

add_action('woocommerce_new_order', 'new_order_handler');



add_filter('woocommerce_checkout_fields', 'bbloomer_checkout_fields_trigger_refresh', 9999);

function bbloomer_checkout_fields_trigger_refresh($fields)
{
    $fields['shipping']['shipping_state']['class'][] = 'update_totals_on_change';

    return $fields;
}

add_filter('woocommerce_default_address_fields', 'custom_override_default_address_fields');

function custom_override_default_address_fields($fields) {
    if (Utils::getCurrentServiceType() === MI_CORREO) {
        $fields['address_1']['label'] = 'Calle';
        $fields['address_1']['placeholder'] = 'Calle';
    }
    return $fields;
}


add_action('woocommerce_checkout_create_order_shipping_item', 'action_checkout_create_order_shipping_item', 10, 4);
function action_checkout_create_order_shipping_item($item, $package_key, $package, $order)
{
    $current_branch = WC()->session->get(CA_CHOSEN_BRANCH);
    $current_branch_name = WC()->session->get(CA_CHOSEN_BRANCH_NAME);
    $current_branch_zip_code = WC()->session->get(CA_CHOSEN_BRANCH_ZIP_CODE);

    $shipping_method_id = $item->get_method_id();

    if ($shipping_method_id == CA_PLUGIN_ID) {
        $shipping_instance_id = (int) $item->get_instance_id();
        $shipping_method = new WC_Correo_Argentino_Shipping_Method($shipping_instance_id);
        if (isset($shipping_method->ca_data) && isset($shipping_method->ca_data[$item->get_instance_id()])) {
            $item->update_meta_data('ca_data', $shipping_method->ca_data[$shipping_instance_id]);
        }
    }

    $item->update_meta_data(CA_CHOSEN_BRANCH, $current_branch, true);
    $item->update_meta_data(CA_CHOSEN_BRANCH_NAME, $current_branch_name, true);
    $item->update_meta_data(CA_CHOSEN_BRANCH_ZIP_CODE, $current_branch_zip_code, true);
}

add_action('woocommerce_order_status_completed', 'on_payment_complete_handler', 10);

add_action('woocommerce_order_status_cancelled', 'on_order_cancelled_handler', 10);

add_action('woocommerce_update_options_shipping_' . CA_PLUGIN_ID, 'on_settings_updated_handler');
/**
 * @throws Exception
 */
function on_settings_updated_handler()
{
    $redirectUrlBase = '?page=wc-settings&tab=shipping&section=correoargentino_shipping_method&form=';
    try {

        // Service type selector
        if (isset($_GET['form']) && $_GET['form'] == CA_SERVICE_SELECTOR) {
            $serviceType = $_POST[CA_SERVICE_TYPE_FIELD];
            $useRates = $_POST[CA_USE_RATES];
            if (isset($serviceType)) {
                update_option(CA_SERVICE_TYPE, $serviceType);
                update_option(CA_USE_RATES, $useRates);
                $service = (new CorreoArgentinoServiceFactory())->get();
                $response = $service->login();

                if (isset($response['token'])) {
                    $credentials = Utils::getCredentials();
                    $credentials["access_token"] = $response["token"];
                    $credentials["expire"] = $response["expire"];
                    $data = maybe_serialize($credentials);
                    update_option(CA_CREDENTIALS, $data);
                }
                $formType = $serviceType == PAQ_AR ? CA_LOGIN_PAQ_AR_FORM : CA_USER_VALIDATE_MI_CORREO_FORM;
                WC_Admin_Settings::add_message(CA_SUCCESS_ON_CONNECTING_TO_MI_CORREO);
                wp_safe_redirect($redirectUrlBase . $formType);
                return true;
            }
            WC_Admin_Settings::add_error(CA_ERROR_ON_CONNECTING_TO_MI_CORREO);
            return false;
        }

        // Setting credentials: MiCorreo
        if (isset($_GET['form']) && $_GET['form'] == CA_USER_VALIDATE_MI_CORREO_FORM) {
            if (isset($_POST[CA_EMAIL_FIELD]) && isset($_POST[CA_PASSWORD_FIELD])) {
                $settings = Utils::getSettings();
                $credentials = Utils::getCredentials();
                $businessDetailsFilled = Utils::businessDetailsFilled();
                $email = sanitize_text_field($_POST[CA_EMAIL_FIELD]);
                $password = sanitize_text_field($_POST[CA_PASSWORD_FIELD]);
                $service = (new CorreoArgentinoServiceFactory())->get();

                if (isset($credentials['access_token'])) {
                    $userValidateResponse = $service->userValidate($email, $password);

                    if (!isset($userValidateResponse) || $userValidateResponse["status"] != 200) {
                        WC_Admin_Settings::add_error(CA_ERROR_ON_CONNECTING_TO_MI_CORREO);
                        return false;
                    }
                    $_POST[CA_CUSTOMER_ID_FIELD] = $userValidateResponse["customerId"];
                    $settingsData = maybe_serialize($settings);
                    update_option(CA_SETTINGS, $settingsData);
                    update_option(CA_IS_CONNECTED, 1);
                    WC_Admin_Settings::add_message(CA_SUCCESS_ON_CONNECTING_TO_MI_CORREO);
                    if (!$businessDetailsFilled) {
                        wp_safe_redirect($redirectUrlBase . CA_BUSINESS_DETAILS_FORM . '&&setup=1');
                    }
                    return true;
                }
                WC_Admin_Settings::add_error(CA_ERROR_ON_CONNECTING_TO_MI_CORREO);
                return false;
            }
        }

        // Setting credentials: Paq.Ar
        if (isset($_GET['form']) && $_GET['form'] == CA_LOGIN_PAQ_AR_FORM) {
            $credentials = Utils::getCredentials();
            if (isset($_POST[CA_AGREEMENT_FIELD]) && isset($_POST[CA_APIKEY_FIELD])) {
                $businessDetailsFilled = Utils::businessDetailsFilled();
                $agreement = $_POST[CA_AGREEMENT_FIELD];
                $apiKey = $_POST[CA_APIKEY_FIELD];
                $credentials['agreement'] = $agreement;
                $credentials['api_key'] = $apiKey;
                $credentialsData = maybe_serialize($credentials);
                update_option(CA_CREDENTIALS, $credentialsData);
                $service = (new CorreoArgentinoServiceFactory())->get();
                $response = $service->login();
                if ($response && $response['status'] == 204) {
                    update_option(CA_IS_CONNECTED, 1);
                    WC_Admin_Settings::add_message(CA_SUCCESS_ON_CONNECTING_TO_PAQ_AR);
                    if (!$businessDetailsFilled) {
                        wp_safe_redirect($redirectUrlBase . CA_BUSINESS_DETAILS_FORM . '&&setup=1');
                    }
                    return true;
                }
                WC_Admin_Settings::add_error(CA_ERROR_ON_CONNECTING_TO_PAQ_AR . " \nError: " . $response['message']);
                return false;
            }
        }

        // Handle Create Account
        if (isset($_GET['form']) && $_GET['form'] == CA_BUSINESS_MI_CORREO_FORM) {
            $credentials = maybe_unserialize(get_option(CA_CREDENTIALS));
            $settings = maybe_unserialize(get_option(CA_SETTINGS));
            if (isset($_POST[CA_FORM_SUBMIT])) {
                if (!isset($credentials["access_token"])) {
                    WC_Admin_Settings::add_error(CA_NO_ACCESS_TOKEN_MESSAGE);
                }

                $service = (new CorreoArgentinoServiceFactory())->get();
                $address = [
                    "streetName" => sanitize_text_field($_POST[CA_STREET_NAME_FIELD]),
                    "streetNumber" => sanitize_text_field($_POST[CA_STREET_NUMBER_FIELD]),
                    "floor" => sanitize_text_field($_POST[CA_FLOOR_FIELD]),
                    "apartment" => sanitize_text_field($_POST[CA_DEPARTMENT_FIELD]),
                    "city" => sanitize_text_field($_POST[CA_CITY_NAME_FIELD]),
                    "provinceCode" => sanitize_text_field($_POST[CA_STATE_CODE_FIELD]),
                    "postalCode" => sanitize_text_field(Utils::normalizeZipCode($_POST[CA_ZIP_CODE_FIELD]))
                ];

                $body = [
                    "firstName" => sanitize_text_field($_POST[CA_FIRST_NAME_FIELD]),
                    "lastName" => sanitize_text_field($_POST[CA_LAST_NAME_FIELD]),
                    "email" => sanitize_text_field($_POST[CA_EMAIL_FIELD]),
                    "password" => sanitize_text_field($_POST[CA_PASSWORD_FIELD]),
                    "documentType" => sanitize_text_field($_POST[CA_DOCUMENT_TYPE_FIELD]),
                    "documentId" => sanitize_text_field($_POST[CA_DOCUMENT_ID_FIELD]),
                    "phone" => Utils::cleanPhone(sanitize_text_field($_POST[CA_PHONE_FIELD])),
                    "cellPhone" => Utils::cleanPhone(sanitize_text_field($_POST[CA_CELLPHONE_FIELD])),
                    "address" => $address,
                ];

                $response = $service->createAccount($body);
                if ($response && $response["status"] == 200) {
                    $_POST[CA_CUSTOMER_ID_FIELD] = $response["customerId"];
                    $data = maybe_serialize($settings);
                    update_option(CA_SETTINGS, $data);
                    WC_Admin_Settings::add_message(CA_SUCCESS_ON_REGISTER_TO_MI_CORREO);
                    return true;
                }
                $errorMessage = isset($response["body"]['message']) ? $response["body"]['message'] : $response['message'];
                WC_Admin_Settings::add_error($errorMessage);
            }
        }

        // Handle business details form
        if (isset($_GET['form']) && $_GET['form'] == CA_BUSINESS_DETAILS_FORM) {
            if (isset($_POST[CA_FORM_SUBMIT])) {
                update_option(CA_BUSINESS_DETAILS_FILLED, 1);
            }
        }
    } catch (\Exception $e) {
        WC_Admin_Settings::add_error('Error: ' . $e->getMessage());
    }
}

add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');

// Keep this to ensure the request works properly
add_filter('http_request_timeout', function ($timeout) {
    return 15;
});

function __set_curl_to_follow(&$handle)
{
    curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
}


add_action('http_api_curl', '__set_curl_to_follow');

// Billing and shipping addresses fields
add_filter('woocommerce_default_address_fields', 'filter_default_address_fields', 20, 1);
function filter_default_address_fields($address_fields)
{
    unset($address_fields['address_2']);
    $address_fields['address_2']['required'] =  true;
    $address_fields['address_2']['priority'] = 60;
    $address_fields['address_2']['placeholder'] = STREET_NUMBER;
    $address_fields['address_2']['label'] = STREET_NUMBER;

    return $address_fields;
}

add_filter('woocommerce_billing_fields', 'filter_billing_fields', 20, 1);
function filter_billing_fields($billing_fields)
{
    $billing_fields['billing_address_2']['required'] = true;
    $billing_fields['billing_address_2']['placeholder'] = STREET_NUMBER;
    $billing_fields['billing_address_2']['label'] = STREET_NUMBER;
    return $billing_fields;
}

function varnish_safe_http_headers()
{
    header('X-UA-Compatible: IE=edge,chrome=1');
    session_cache_limiter('');
    header("Cache-Control: public, s-maxage=120");
    if (!session_id()) {
        session_start();
    }
}

add_action('send_headers', 'varnish_safe_http_headers');

add_filter('woocommerce_before_checkout', 'clear_wc_shipping_rates_cache');

function clear_wc_shipping_rates_cache()
{
    $packages = WC()->cart->get_shipping_packages();

    foreach ($packages as $key => $value) {
        $shipping_session = "shipping_for_package_$key";

        unset(WC()->session->$shipping_session);
    }
}

function enforce_update_shipping_methods()
{
    if (is_cart()) {
        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();
    }
}

add_action('woocommerce_before_cart', 'enforce_update_shipping_methods');

function custom_admin_styles()
{
    echo '<style>
        .wp-list-table .column-column_cb {
            width: 18px; 
        }
    </style>';
}

add_action('admin_head', 'custom_admin_styles');

function process_bulk_orders_handler()
{
    if (Utils::getCurrentServiceType() === MI_CORREO) {
?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                const mainCheckboxes = $('.wc-correoargentino-all-orders');
                const orderCheckboxes = $('input[name="order_id[]"]');

                mainCheckboxes.on('change', function() {
                    const isChecked = $(this).prop('checked');
                    mainCheckboxes.prop('checked', isChecked);
                    orderCheckboxes.prop('checked', isChecked);
                });

                orderCheckboxes.on('change', function() {
                    const allChecked = orderCheckboxes.filter(':checked').length === orderCheckboxes.length;
                    mainCheckboxes.prop('checked', allChecked);
                });


                $('.bulk-actions-button').on('click', function() {
                    $(this).attr('disabled', true);
                    console.log('Processing...');
                    const selectedAction = $('.bulk-actions-top option:selected').val();

                    if (selectedAction == -1) return

                    const selectedOrderIds = [];


                    $('input[name="order_id[]"]:checked').each(function() {
                        selectedOrderIds.push($(this).val());
                    });

                    if (selectedOrderIds.length > 10) {
                        alert('Solo se permiten 10 registros por operación');
                        return;
                    }

                    const path = "<?php echo admin_url('admin-ajax.php'); ?>";

                    const promises = selectedOrderIds.map(orderId => {
                        showLoadingMessage(orderId);
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                type: 'POST',
                                url: path,
                                data: {
                                    action: 'process_orders_handler',
                                    order_id: orderId,
                                },
                                success: function(response) {
                                    resolve(response);
                                },
                                error: function(error) {
                                    reject(error);
                                },
                                complete: function() {
                                    hideLoadingMessage(orderId);
                                }
                            });
                        });
                    });

                    Promise.all(promises)
                        .then(results => {
                            results.forEach(result => {
                                const {
                                    order_id,
                                    reference,
                                    api_error_message
                                } = result.data;
                                if (!reference) {
                                    handleError(order_id, 'Error: ' + api_error_message);
                                    return
                                }
                                handleSuccess(order_id, reference);
                            });

                            new Promise(resolve => setTimeout(resolve, 3000)).then(() => {
                                window.location.reload();
                            });
                        }).catch(errors => {
                            console.error('Error in processing orders:', errors);
                            errors.forEach(error => {
                                console.log(error)
                            });
                        });

                    function handleSuccess(orderId, reference) {
                        const trackingColumn = $('.ph-tracking_number-' + orderId);
                        const url = reference;
                        // Find the checkbox in the same table row and hide it
                        const checkbox = trackingColumn.closest('tr').find('input[type="checkbox"]');
                        checkbox.hide();
                        trackingColumn.empty();
                        trackingColumn.html(url);
                    }

                    function handleError(orderId, status) {
                        const statusColumn = $('.ph-tracking_number-' + orderId);
                        statusColumn.html('<span style="color: #ff645c;">' + status + '</span>');
                    }

                    function showLoadingMessage(orderId) {
                        $('.ph-tracking_number-' + orderId).text('Procesando...');
                    }

                    function hideLoadingMessage(orderId) {
                        $('.ph-tracking_number-' + orderId).text('');
                    }
                });
            });
        </script>
<?php
    }
}

add_action('admin_footer', 'process_bulk_orders_handler');

add_action('admin_init', function () {
    if (empty(Utils::getCurrentServiceType()) || !Utils::isConnected() || isset($_GET['form']) && $_GET['form'] === CA_BUSINESS_DETAILS_FORM) {
        return;
    }
    if (!Utils::canListRates()) {
        Utils::message(CA_BUSINESS_DETAILS_NOT_FILLED_MESSAGE, 'warning');
    }
});

add_filter('woocommerce_checkout_get_value', 'custom_edit_shipping_default_values', 10, 2);

function custom_edit_shipping_default_values($value, $input)
{
    $chosenShippingMethod = WC()->session->get('chosen_shipping_methods');
    $isBranch = Utils::isBranch($chosenShippingMethod[0]);
    $fields_to_modify = array(
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_company',
        'shipping_address_1',
        'shipping_address_2'
    );

    if (!$isBranch) {
        Utils::unset_wc_chosen_branch_values();

        if (in_array($input, $fields_to_modify) && $input === 'shipping_company') {
            $value = '';
        }
        return $value;
    }

    if (in_array($input, $fields_to_modify)) {
        switch ($input) {
            case 'shipping_postcode':
                // PostalCode
                $value = WC()->session->get(CA_CHOSEN_BRANCH_ZIP_CODE);
                break;
            case 'shipping_city':
                // City
                $value = WC()->session->get(CA_CHOSEN_BRANCH_CITY);
                break;
            case 'shipping_state':
                // State
                $value = WC()->session->get(CA_CHOSEN_PROVINCE_CODE);
                break;
            case 'shipping_address_1':
                // StreetName
                $value = WC()->session->get(CA_CHOSEN_BRANCH_STREET_NAME) . ' (' . WC()->session->get(CA_CHOSEN_BRANCH) . ')';
                break;
            case 'shipping_address_2':
                // StreetNumber
                $value = WC()->session->get(CA_CHOSEN_BRANCH_STREET_NUMBER);
                break;
            case 'shipping_company':
                // AgencyName
                $value = WC()->session->get(CA_CHOSEN_BRANCH_NAME);
                break;
        }
    }

    return $value;
}

add_action('woocommerce_thankyou', 'flush_chosen_branch_data');

function flush_chosen_branch_data($order_id)
{
    if (is_wc_endpoint_url('order-received')) {
        Utils::unset_wc_chosen_branch_values();
        restore_wc_shipping_address($order_id);
    }
}

function update_wc_customer_shipping_address($customer_id)
{
    update_user_meta($customer_id, 'shipping_address_1', WC()->session->get(CA_CUSTOMER_SHIPPING_ADDRESS_1));
    update_user_meta($customer_id, 'shipping_address_2', WC()->session->get(CA_CUSTOMER_SHIPPING_ADDRESS_2));
    update_user_meta($customer_id, 'shipping_city', WC()->session->get(CA_CUSTOMER_SHIPPING_CITY));
    update_user_meta($customer_id, 'shipping_state', WC()->session->get(CA_CUSTOMER_SHIPPING_STATE));
    update_user_meta($customer_id, 'shipping_postcode', WC()->session->get(CA_CUSTOMER_SHIPPING_POSTCODE));
    update_user_meta($customer_id, 'shipping_company', WC()->session->get(CA_CUSTOMER_SHIPPING_COMPANY));

    wp_update_user(array('ID' => $customer_id));
}

function restore_wc_shipping_address($order_id)
{
    $order = wc_get_order($order_id);
    $customer_id = $order->get_customer_id();

    update_wc_customer_shipping_address($customer_id);
}

function set_temp_shipping_address($data)
{
    $user = wp_get_current_user();

    $shipping_address = array(
        'address_1' => get_user_meta($user->ID, 'shipping_address_1', true),
        'address_2' => get_user_meta($user->ID, 'shipping_address_2', true),
        'city' => get_user_meta($user->ID, 'shipping_city', true),
        'state' => get_user_meta($user->ID, 'shipping_state', true),
        'postcode' => get_user_meta($user->ID, 'shipping_postcode', true),
        'company' => get_user_meta($user->ID, 'shipping_company', true),
    );

    WC()->session->set(CA_CUSTOMER_SHIPPING_ADDRESS_1, $shipping_address['address_1']);
    WC()->session->set(CA_CUSTOMER_SHIPPING_ADDRESS_2, $shipping_address['address_2']);
    WC()->session->set(CA_CUSTOMER_SHIPPING_CITY, $shipping_address['city']);
    WC()->session->set(CA_CUSTOMER_SHIPPING_STATE, $shipping_address['state']);
    WC()->session->set(CA_CUSTOMER_SHIPPING_POSTCODE, $shipping_address['postcode']);
    WC()->session->set(CA_CUSTOMER_SHIPPING_COMPANY, $shipping_address['company']);
}

add_action('woocommerce_before_cart', 'set_temp_shipping_address');

//add_filter('woocommerce_cart_shipping_packages', 'restore_initial_shipping_address');

function restore_initial_shipping_address($packages)
{
    $user = wp_get_current_user();
    $customer_id = (string)$user->ID;

    if (!empty($packages) && is_cart()) {
        update_wc_customer_shipping_address($customer_id);
    }

    return $packages;
}

add_action('woocommerce_customer_save_address', 'save_wc_customer_shipping_address', 10, 2);

function save_wc_customer_shipping_address($customer_id, $load_address)
{
    if ($load_address === 'shipping') {
        set_temp_shipping_address($customer_id);
    }
}

add_action('admin_enqueue_scripts', 'enqueue_woocommerce_admin_styles');

function enqueue_woocommerce_admin_styles()
{
    // Check if WooCommerce is active
    if (class_exists('WooCommerce')) {
        // Enqueue WooCommerce admin stylesheets
        wp_enqueue_style('woocommerce_admin_styles', plugins_url('woocommerce/assets/css/admin.css'));
    }
}

add_filter('woocommerce_get_cart_url', 'custom_cart_page_url');
function custom_cart_page_url($url)
{
    $new_cart_page_slug = 'cart-2';

    $new_cart_page_id = url_to_postid(home_url('/') . $new_cart_page_slug);

    if ($new_cart_page_id) {
        $new_cart_page_permalink = get_permalink($new_cart_page_id);

        if ($new_cart_page_permalink) {
            $url = $new_cart_page_permalink;
        }
    }

    return $url;
}

add_action('woocommerce_checkout_update_order_review', 'update_order_review', 10, 0);

function update_order_review()
{
    $chosenShippingMethod = WC()->session->get('chosen_shipping_methods');
    $isBranch = Utils::isBranch($chosenShippingMethod[0]);
    if (!$isBranch) {
        Utils::unset_wc_chosen_branch_values();
    }
}



/**
 * Check if branches are selected
 */
add_action('woocommerce_checkout_process', function () {
    $chosenShippingMethod = WC()->session->get('chosen_shipping_methods');
    $isBranch = Utils::isBranch($chosenShippingMethod[0]);
    if (
        $isBranch &&
        (
            (WC()->session->get(CA_CHOSEN_BRANCH) === null) ||
            (WC()->session->get(CA_CHOSEN_PROVINCE_CODE) === null)
        )
    ) {
        wc_add_notice(__('Correo Argentino. Al seleccionar envio a sucursal, debe seleccionar una provincia y una sucursal de destino', 'woocommerce'), 'error');
        return false;
    }
    return true;
}, 20);


add_action('wp_footer', function() {
    ?>
    <style>
        .non-interactive {
            pointer-events: none;
            background-color: #f0f0f0;
        }
        .disabled-checkbox {
            pointer-events: none;
            opacity: 0.5;
        }
    </style>
    <script type="text/javascript">
    jQuery(document).ready(function($) {

        let wasProvinceOrBranchVisible = false;

        function toggleShippingAddress() {
            var provinceSelectVisible = $('#ca_container_select_provinces').is(':visible');
            var branchSelectVisible = $('#ca_container_select_branches').is(':visible');

            if (provinceSelectVisible || branchSelectVisible) {
                
                wasProvinceOrBranchVisible = true; 
               
                $('#ship-to-different-address-checkbox').prop('checked', true); 
                $('.shipping_address').show(); 
                $('.shipping_address :input:not([type="checkbox"]), .shipping_address textarea').prop('readonly', true);
                $('.shipping_address select').addClass('non-interactive');
                $('#shipping_first_name, #shipping_last_name').prop('readonly', false); 
            } else {
                
                $('.shipping_address :input:not([type="checkbox"]), .shipping_address textarea').prop('readonly', false);
                $('.shipping_address select').removeClass('non-interactive');
                $('#correo_argentino_review_order_section_address').hide();

                if (wasProvinceOrBranchVisible) {
                    loadShippingAddressData(); 
                    wasProvinceOrBranchVisible = false;
                }
            }
        }

        function loadShippingAddressData() {
            <?php if (is_user_logged_in()) : ?>
                $('#shipping_first_name').val('<?php echo esc_js(get_user_meta(get_current_user_id(), 'shipping_first_name', true)); ?>');
                $('#shipping_last_name').val('<?php echo esc_js(get_user_meta(get_current_user_id(), 'shipping_last_name', true)); ?>');
                $('#shipping_company').val('<?php echo esc_js(get_user_meta(get_current_user_id(), 'shipping_company', true)); ?>');
                $('#shipping_address_1').val('<?php echo esc_js(get_user_meta(get_current_user_id(), 'shipping_address_1', true)); ?>');
                $('#shipping_address_2').val('<?php echo esc_js(get_user_meta(get_current_user_id(), 'shipping_address_2', true)); ?>');
                $('#shipping_city').val('<?php echo esc_js(get_user_meta(get_current_user_id(), 'shipping_city', true)); ?>');
                $('#shipping_state').val('<?php echo esc_js(get_user_meta(get_current_user_id(), 'shipping_state', true)); ?>').trigger('change');
                $('#shipping_postcode').val('<?php echo esc_js(get_user_meta(get_current_user_id(), 'shipping_postcode', true)); ?>');
                $('#shipping_phone').val('<?php echo esc_js(get_user_meta(get_current_user_id(), 'shipping_phone', true)); ?>');
            <?php else : ?>
                $('#shipping_first_name').val($('#billing_first_name').val());
                $('#shipping_last_name').val($('#billing_last_name').val());
                $('#shipping_company').val($('#billing_company').val());
                $('#shipping_address_1').val($('#billing_address_1').val());
                $('#shipping_address_2').val($('#billing_address_2').val());
                $('#shipping_city').val($('#billing_city').val());
                $('#shipping_state').val($('#billing_state').val()).trigger('change');
                $('#shipping_postcode').val($('#billing_postcode').val());
                $('#shipping_phone').val($('#billing_phone').val());
            <?php endif; ?>
        }

        
        toggleShippingAddress();

        $('#ca_container_select_provinces, #ca_container_select_branches').on('change', function() {
            toggleShippingAddress();
        });

        $('#ship-to-different-address-checkbox').on('change', function() {
            if (!$('#ca_container_select_provinces').is(':visible') && !$('#ca_container_select_branches').is(':visible')) {
                toggleShippingAddress();
            }
        });

        $(document.body).on('updated_checkout', function() {
            toggleShippingAddress();
        });
    });
    </script>
    <?php
});

/**
 * Mostrar mensaje de envío gratuito en el carrito
 */
add_action('woocommerce_before_cart_table', 'display_free_shipping_message_cart');
function display_free_shipping_message_cart() {
    if (!WC()->cart->needs_shipping()) {
        return;
    }
    
    $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
    if (empty($chosen_shipping_methods)) {
        return;
    }
    
    $chosen_method = $chosen_shipping_methods[0];
    if (strpos($chosen_method, CA_PLUGIN_ID) === 0) {
        $shipping_method_id = (int) substr($chosen_method, strlen(CA_PLUGIN_ID) + 1);
        $shipping_method = new WC_Correo_Argentino_Shipping_Method($shipping_method_id);
        $free_shipping_threshold = $shipping_method->get_option('free_shipping_threshold');
        
        if (!empty($free_shipping_threshold) && is_numeric($free_shipping_threshold)) {
            $cart_subtotal = WC()->cart->get_subtotal();
            $remaining = floatval($free_shipping_threshold) - $cart_subtotal;
            
            if ($remaining > 0) {
                echo '<div class="woocommerce-info correoargentino-free-shipping-info">';
                echo '<span class="dashicons dashicons-info"></span> ';
                printf(
                    __('¡Agregá productos por %s más y obtené envío gratuito con Correo Argentino!', 'correoargentino'),
                    wc_price($remaining)
                );
                echo '</div>';
            } else {
                echo '<div class="woocommerce-message correoargentino-free-shipping-success">';
                echo '<span class="dashicons dashicons-yes-alt"></span> ';
                echo __('¡Felicitaciones! Tu pedido califica para envío gratuito con Correo Argentino.', 'correoargentino');
                echo '</div>';
            }
        }
    }
}

/**
 * Mostrar mensaje de envío gratuito en el checkout
 */
add_action('woocommerce_before_checkout_form', 'display_free_shipping_message_checkout');
function display_free_shipping_message_checkout() {
    if (!WC()->cart->needs_shipping()) {
        return;
    }
    
    // Solo mostrar si hay métodos de envío de Correo Argentino disponibles
    $available_methods = WC()->shipping()->get_shipping_methods();
    $has_ca_methods = false;
    $free_shipping_threshold = 0;
    
    foreach ($available_methods as $method) {
        if ($method instanceof WC_Correo_Argentino_Shipping_Method) {
            $has_ca_methods = true;
            $threshold = $method->get_option('free_shipping_threshold');
            if (!empty($threshold) && is_numeric($threshold)) {
                $free_shipping_threshold = max($free_shipping_threshold, floatval($threshold));
            }
        }
    }
    
    if ($has_ca_methods && $free_shipping_threshold > 0) {
        $cart_subtotal = WC()->cart->get_subtotal();
        $remaining = $free_shipping_threshold - $cart_subtotal;
        
        if ($remaining > 0) {
            echo '<div class="woocommerce-info correoargentino-free-shipping-info checkout">';
            echo '<span class="dashicons dashicons-info"></span> ';
            printf(
                __('¡Agregá productos por %s más y obtené envío gratuito con Correo Argentino!', 'correoargentino'),
                wc_price($remaining)
            );
            echo '</div>';
        } else {
            echo '<div class="woocommerce-message correoargentino-free-shipping-success checkout">';
            echo '<span class="dashicons dashicons-yes-alt"></span> ';
            echo __('¡Tu pedido califica para envío gratuito con Correo Argentino!', 'correoargentino');
            echo '</div>';
        }
    }
}

/**
 * Actualizar mensajes de envío gratuito dinámicamente
 */
add_action('woocommerce_cart_updated', 'update_free_shipping_messages');
function update_free_shipping_messages() {
    if (!WC()->cart->needs_shipping()) {
        return;
    }
    
    // Solo ejecutar en AJAX
    if (!wp_doing_ajax()) {
        return;
    }
    
    $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
    if (empty($chosen_shipping_methods)) {
        return;
    }
    
    $chosen_method = $chosen_shipping_methods[0];
    if (strpos($chosen_method, CA_PLUGIN_ID) === 0) {
        $shipping_method_id = (int) substr($chosen_method, strlen(CA_PLUGIN_ID) + 1);
        $shipping_method = new WC_Correo_Argentino_Shipping_Method($shipping_method_id);
        $free_shipping_threshold = $shipping_method->get_option('free_shipping_threshold');
        
        if (!empty($free_shipping_threshold) && is_numeric($free_shipping_threshold)) {
            $cart_subtotal = WC()->cart->get_subtotal();
            $remaining = floatval($free_shipping_threshold) - $cart_subtotal;
            
            // Enviar datos para actualización dinámica
            wp_send_json([
                'free_shipping_enabled' => true,
                'threshold' => $free_shipping_threshold,
                'subtotal' => $cart_subtotal,
                'remaining' => $remaining,
                'qualifies' => $remaining <= 0,
                'remaining_formatted' => wc_price($remaining)
            ]);
        }
    }
}

/**
 * Endpoint AJAX para obtener información del envío gratuito
 */
add_action('wp_ajax_get_free_shipping_info', 'ajax_get_free_shipping_info');
add_action('wp_ajax_nopriv_get_free_shipping_info', 'ajax_get_free_shipping_info');
function ajax_get_free_shipping_info() {
    // Verificar nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wc_correoargentino_nonce')) {
        wp_send_json_error('Nonce inválido');
    }
    
    if (!WC()->cart->needs_shipping()) {
        wp_send_json_error('Carrito no necesita envío');
    }
    
    $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
    if (empty($chosen_shipping_methods)) {
        wp_send_json_error('No hay método de envío seleccionado');
    }
    
    $chosen_method = $chosen_shipping_methods[0];
    if (strpos($chosen_method, CA_PLUGIN_ID) === 0) {
        $shipping_method_id = (int) substr($chosen_method, strlen(CA_PLUGIN_ID) + 1);
        $shipping_method = new WC_Correo_Argentino_Shipping_Method($shipping_method_id);
        $free_shipping_threshold = $shipping_method->get_option('free_shipping_threshold');
        
        if (!empty($free_shipping_threshold) && is_numeric($free_shipping_threshold)) {
            $cart_subtotal = WC()->cart->get_subtotal();
            $remaining = floatval($free_shipping_threshold) - $cart_subtotal;
            
            wp_send_json_success([
                'free_shipping_enabled' => true,
                'threshold' => $free_shipping_threshold,
                'subtotal' => $cart_subtotal,
                'remaining' => $remaining,
                'qualifies' => $remaining <= 0,
                'remaining_formatted' => wc_price($remaining)
            ]);
        } else {
            wp_send_json_error('Envío gratuito no configurado');
        }
    } else {
        wp_send_json_error('Método de envío no es Correo Argentino');
    }
}

/**
 * Mostrar información del envío gratuito en el panel de administración
 */
add_action('woocommerce_admin_order_data_after_shipping_address', 'display_free_shipping_info_admin');
function display_free_shipping_info_admin($order) {
    $shipping_methods = $order->get_shipping_methods();
    
    foreach ($shipping_methods as $shipping_method) {
        if (strpos($shipping_method->get_method_id(), CA_PLUGIN_ID) === 0) {
            $is_free_shipping = $shipping_method->get_meta('is_free_shipping');
            $original_cost = $shipping_method->get_meta('original_cost');
            $free_shipping_threshold = $shipping_method->get_meta('free_shipping_threshold');
            
            if ($is_free_shipping && !empty($original_cost)) {
                echo '<div class="correoargentino-admin-info">';
                echo '<h4>' . __('Información de Envío Gratuito', 'correoargentino') . '</h4>';
                echo '<p><strong>' . __('Costo original del envío:', 'correoargentino') . '</strong> ' . wc_price($original_cost) . '</p>';
                echo '<p><strong>' . __('Umbral para envío gratuito:', 'correoargentino') . '</strong> ' . wc_price($free_shipping_threshold) . '</p>';
                echo '<p><strong>' . __('Subtotal de la orden:', 'correoargentino') . '</strong> ' . wc_price($order->get_subtotal()) . '</p>';
                echo '<p><strong>' . __('Estado:', 'correoargentino') . '</strong> <span style="color: green;">✓ ' . __('Envío gratuito aplicado', 'correoargentino') . '</span></p>';
                echo '</div>';
            }
            break;
        }
    }
}



