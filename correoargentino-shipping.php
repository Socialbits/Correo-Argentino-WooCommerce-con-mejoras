<?php

/**
 * Plugin Name: Correo Argentino oficial WooCommerce
 * Plugin URL: https://www.correoargentino.com.ar/integraciones/woocommerce
 * Description: Permita a sus compradores realizar envíos a través de Correo Argentino
 * Author: Correo Argentino.
 * Author URI: www.correoargentino.com.ar/
 * Version: 3.0.3.rc-241028-4
 * Text Domain: correoargentino
 *
 *
 * Woo:
 */
if (!defined('WPINC')) {
    die('security by preventing any direct access to your plugin file');
}

require_once plugin_dir_path(__FILE__) . 'loader.php';

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    die('security by preventing any direct access to your plugin file');
}


register_activation_hook(__FILE__, function () {
    $settingsInitialValues = [
        'customer_id' => null,
        'first_name' => null,
        'last_name' => null,
        'business_name' => null,
        'email' => null,
        'password' => null,
        'state' => null,
        'state_code' => null,
        'city_name' => null,
        'department' => null,
        'floor' => null,
        'street_name' => null,
        'street_number' => null,
        'zip_code' => null,
        'cellphone' => null,
        'phone' => null,
        'observation' => null,
    ];

    $credentialsInitialValues = [
        "sandbox" => 0,
        "api_key" => null,
        "agreement" => null,
        "auth_hash" => base64_encode(CA_USERNAME_MI_CORREO . ':' . CA_PASSWORD_MI_CORREO),
        "access_token" => null,
        "expire" => null
    ];

    //Add customer with a proper role to see the rates on the fly
    $role = get_role('customer'); // Use 'customer' as the role for customers.

    if ($role) {
        $role->add_cap('manage_' . CA_PLUGIN_ID);
    }

    add_option(CA_SETTINGS, serialize($settingsInitialValues));
    add_option(CA_CREDENTIALS, serialize($credentialsInitialValues));
    add_option(CA_SERVICE_TYPE, null);
    add_option(CA_USE_RATES, null);
    add_option(CA_IS_CONNECTED, 0);
    add_option(CA_BUSINESS_DETAILS_FILLED, 0);
    update_option(WC_SHIPPING_DEBUG_MODE, 'yes');
    update_option(WC_SHIPPING_TO_DESTINATION, 'shipping');
});

register_activation_hook(__FILE__, 'override_woocommerce_pages_activation');

function override_woocommerce_pages_activation()
{
    if (class_exists('WooCommerce')) {
        $pages = array(
            'cart' => '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->',
            'checkout' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->',
        );

        foreach ($pages as $page_slug => $default_content) {
            $page_id = wc_get_page_id($page_slug);

            if ($page_id > 0) {
                $page_data = array(
                    'ID' => $page_id,
                    'post_content' => $default_content,
                );

                wp_update_post($page_data);
            }
        }
    }
}


register_uninstall_hook(__FILE__, 'override_woocommerce_pages_deactivation');

function override_woocommerce_pages_deactivation()
{
    if (class_exists('WooCommerce')) {
        $pages = array(
            'cart' => '[woocommerce_cart]',
            'checkout' => '[woocommerce_checkout]',
        );

        foreach ($pages as $page_slug => $default_content) {
            $page_id = wc_get_page_id($page_slug);

            if ($page_id > 0 && get_post_meta($page_id, '_override_created', true) === 'yes') {
                $page_data = array(
                    'ID' => $page_id,
                    'post_content' => $default_content,
                );

                wp_update_post($page_data);
            }
        }
    }
}


// This deletes all the initial plugin settings
register_deactivation_hook(__FILE__, function () {
    // Remove custom capability on plugin deactivation.
    $role = get_role('customer');
    $role->remove_cap('manage_' . CA_PLUGIN_ID);

    delete_option(CA_SETTINGS);
    delete_option(CA_CREDENTIALS);
    delete_option(CA_SERVICE_TYPE);
    delete_option(CA_USE_RATES);
    delete_option(CA_IS_CONNECTED);
    delete_option(CA_BUSINESS_DETAILS_FILLED);
});


function Correo_Argentino_Shipping_Method()
{
    require_once plugin_dir_path(__FILE__) . '/Classes/correoargentino-shipping-method.php';
}

function correoargentino_validate_order($posted)
{
    $packages = WC()->shipping->get_packages();
    $chosen_methods = WC()->session->get('chosen_shipping_methods');

    if (is_array($chosen_methods) && in_array('correoargentino', $chosen_methods)) {
        foreach ($packages as $i => $package) {
            if ($chosen_methods[$i] != "correoargentino") {
                continue;
            }
            $Correo_Argentino_Shipping_Method = new WC_Correo_Argentino_Shipping_Method();
            $weightLimit = (int)$Correo_Argentino_Shipping_Method->settings['weight'];
            $weight = 0;
            foreach ($package['contents'] as $values) {
                $item = $values['data'];
                $weight = $weight + $item->get_weight() * $values['quantity'];
            }
            $weight = wc_get_weight($weight, 'kg');
            if ($weight > $weightLimit) {
                $message = sprintf(__('OOPS, %d kg increase the maximum weight of %d kg for %s', 'correoargentino'), $weight, $weightLimit, $Correo_Argentino_Shipping_Method->title);
                $messageType = "error";
                if (!wc_has_notice($message, $messageType)) {
                    wc_add_notice($message, $messageType);
                }
            }
        }
    }
}


function add_select2_scripts()
{
    if (is_cart() | is_checkout()) {
        wp_enqueue_style('select2-css', plugin_dir_url(__FILE__) . 'js/select2-4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0-rc.0',);
        wp_enqueue_style('select2-override-css', plugin_dir_url(__FILE__) . 'css/select2-override.css', array(), '4.1.0-rc.0',);
        wp_enqueue_script('select2-js', plugin_dir_url(__FILE__) . 'js/select2-4.1.0-rc.0/dist/js/select2.full.min.js', ['jquery'], '4.1.0-rc.0', true);
    }
}


function enqueue_admin_scripts()
{
    if (is_admin()) {
        wp_enqueue_style('validate-css', plugin_dir_url(__FILE__) . 'css/validate.css', array(), time());
        wp_enqueue_script('validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js', ['jquery'], '4.1.0-rc.0', true);
        wp_enqueue_script('validate-methods', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/additional-methods.min.js', 'jquery', '4.1.0-rc.0', true);
        wp_enqueue_script('masked-input', plugin_dir_url(__FILE__) . 'js/jquery.mask.min.js', ['jquery'], '4.1.0-rc.0', true);
        wp_enqueue_script('validate-config-form', plugin_dir_url(__FILE__) . 'js/validate-config-form.js', ['jquery'], time(), true);
        wp_enqueue_script('state-select2', plugin_dir_url(__FILE__) . 'js/select2-init.js', ['jquery'], time(), true);
        wp_enqueue_script('service-selector', plugin_dir_url(__FILE__) . 'js/service-selector.js', ['jquery'], time(), true);
        wp_enqueue_style('woocommerce-correoargentino-css', plugin_dir_url(__FILE__) . 'css/woocommerce-correoargentino.css', array(), time());
    }
}

function get_edit_address_url()
{
    $user_id = get_current_user_id();

    if ($user_id) {
        $endpoint = 'edit-address/shipping/';
        return wc_get_account_endpoint_url($endpoint);
    }

    return false;
}

/**
 * This method sets the branch ID through
 */
function choose_branch_handler()
{
    if (is_cart() | is_checkout()) {
        $addressUrl = get_edit_address_url();
?>
        <script type="text/javascript">
            jQuery(function($) {

                /**
                 * Set Shipping message
                 */
                function branchSelected(branch) {
                    const {
                        id,
                        zip_code,
                        name,
                        street_name,
                        street_number,
                        city,
                        state
                    } = branch
                    $('#woocommerce_correoargentino_branch_zip_code_hidden').val(zip_code);
                    let shipping_address_message = 'Cargando sucursal...'
                    if (name) {
                        shipping_address_message = `Enviar a sucursal <strong>${name}, ${street_name} ${street_number}, ${city}, ${state} (${id}), ${zip_code}</strong>.`;
                    }
                    if (id !== 'NONE') {
                        $('.woocommerce-shipping-destination').empty().html(shipping_address_message).show();
                        $('#correo_argentino_review_order_section_address').empty().html(shipping_address_message).show();
                    } else {
                        $('.woocommerce-shipping-destination').empty().show();
                        $('#correo_argentino_review_order_section_address').empty().show();
                    }
                }

                const path = "<?php echo admin_url('admin-ajax.php'); ?>";
                const province_select = '.correoargentino_province_select';
                const agency_select = '.correoargentino_branch_select';
                const edit_address_path = "<?php echo $addressUrl; ?>";

                function loadProvincesSelect2() {
                    $(province_select).select2().on('select2:select', function(e) {
                        const province_code = $(province_select).val();

                        if (!province_code || province_code === "NONE") {
                            throw new Error('None province selected');
                        }

                        $(agency_select).attr("disabled", true);
                        jQuery.ajax({
                            type: "post",
                            dataType: "json",
                            url: path,
                            data: {
                                action: "update_province_handler",
                                province_code,
                            },
                            success: function(response) {
                                $(agency_select).empty();
                                let selectedAgency = null
                                if (response) {
                                    response.province_list.forEach((data) => {
                                        let newOption = new Option(data.text, data.id, false, false);
                                        newOption.dataset.branchName = data.agency_name;
                                        newOption.dataset.branchZipCode = data.agency_zip_code;
                                        newOption.dataset.branchCity = data.agency_city;
                                        newOption.dataset.branchState = data.agency_state;
                                        newOption.dataset.branchStreetName = data.agency_street_name;
                                        newOption.dataset.branchStreetNumber = data.agency_street_number;
                                        newOption.dataset.branchChosenProductType = data.agency_chosen_product_type;
                                        const selected = data.id === response.branch_id
                                        newOption.selected = selected
                                        if (selected) {
                                            selectedAgency = data
                                        }
                                        $(agency_select).append(newOption).trigger('change');
                                    });
                                    if (selectedAgency) {
                                        branchSelected({
                                            id: selectedAgency.id,
                                            zip_code: selectedAgency.agency_zip_code,
                                            name: selectedAgency.agency_name,
                                            street_name: selectedAgency.agency_street_name,
                                            street_number: selectedAgency.agency_street_number,
                                            city: selectedAgency.agency_city,
                                            state: selectedAgency.agency_state
                                        })
                                    } else {
                                        branchSelected({
                                            id: 'NONE'
                                        })
                                    }
                                }
                                $(agency_select).attr("disabled", false);
                            },
                            error: function(error) {
                                console.error(error);
                            }
                        });

                    });

                    if ($(province_select).val() !== 'NONE') {
                        $(province_select).trigger('select2:select');
                    }
                }

                function loadAgencySelect2() {
                    $(agency_select).select2().on('select2:select', function(e) {
                        const id = $(agency_select).val();
                        const name = getBranchName();
                        const zip_code = getBranchZipCode();
                        const city = getBranchCity();
                        const state = getBranchState();
                        const street_name = getBranchStreetName();
                        const street_number = getBranchStreetNumber();
                        const chosen_product_type = getChosenProductType();

                        try {
                            validateArguments({
                                id,
                                name,
                                zip_code,
                                city,
                                state,
                                street_name,
                                street_number
                            });
                        } catch (error) {
                            console.log(error.message);
                        }

                        $('#shipping_postcode').val(zip_code);          
                        $('#shipping_city').val(city);                 
                        $('#shipping_address_1').val(street_name);      
                        $('#shipping_address_2').val(street_number);    
                        $('#shipping_company').val(name);
                        const provinceMap = {
                            "capital federal": "C",
                            "buenos aires": "B",
                            "catamarca": "K",
                            "chaco": "H",
                            "chubut": "U",
                            "cordoba": "X",
                            "corrientes": "W",
                            "entre ríos": "E",
                            "formosa": "P",
                            "jujuy": "Y",
                            "la pampa": "L",
                            "la rioja": "F",
                            "mendoza": "M",
                            "misiones": "N",
                            "neuquen": "Q",
                            "rio negro": "R",
                            "salta": "A",
                            "san juan": "J",
                            "san luis": "D",
                            "santa cruz": "Z",
                            "santa fe": "S",
                            "santiago del estero": "G",
                            "tierra del fuego": "V",
                            "tucuman": "T"
                        };
                        const formattedState = state.toLowerCase().trim(); 
                     
                        const selectedProvinceValue = provinceMap[formattedState] || "";

                        $('#shipping_state').val(selectedProvinceValue).trigger('change'); 
                                    
                        $('#calc_shipping_postcode').val(zip_code);
                        // Loading for setting shipping address
                        $('.woocommerce-shipping-destination').html('Cargando...');
                        jQuery.ajax({
                            type: "post",
                            dataType: "json",
                            url: path,
                            data: {
                                action: "update_branch_handler",
                                branch_id: id,
                                branch_name: name,
                                branch_city: city,
                                branch_state: state,
                                branch_zip_code: zip_code,
                                branch_street_name: street_name,
                                branch_street_number: street_number,
                                branch_chosen_product_type: chosen_product_type
                            },
                            success: function(response) {
                                branchSelected(
                                    id,
                                    zip_code,
                                    name,
                                    street_name,
                                    street_number,
                                    city,
                                    state
                                )
                                <?php if (is_cart()) { ?>
                                    $('.shipping_method:checked').change();
                                <?php } else { ?>
                                    $(document.body).trigger('update_checkout')
                                <?php } ?>
                            },
                            error: function(error) {
                                console.error(error);
                                $('.woocommerce-shipping-destination').empty();
                            }
                        });
                    });

                    function getBranchName() {
                        return $(agency_select + ' option:selected').data('branch-name');
                    }

                    function getBranchStreetName() {
                        return $(agency_select + ' option:selected').data('branch-street-name');
                    }

                    function getBranchStreetNumber() {
                        return $(agency_select + ' option:selected').data('branch-street-number');
                    }

                    function getBranchCity() {
                        return $(agency_select + ' option:selected').data('branch-city');
                    }

                    function getBranchState() {
                        return $(agency_select + ' option:selected').data('branch-state');
                    }

                    function getBranchZipCode() {
                        return $(agency_select + ' option:selected').data('branch-zip-code');
                    }

                    function getChosenProductType() {
                        return $(agency_select + ' option:selected').data('branch-chosen-product-type');
                    }

                    function validateArguments({
                        id,
                        name,
                        zip_code,
                        city,
                        state,
                        street_name,
                        street_number
                    }) {
                        const requiredArguments = ['id', 'name', 'zip_code', 'city', 'state', 'street_name', 'street_number'];
                        const missingArguments = [];

                        requiredArguments.forEach(arg => {
                            if (!eval(arg)) {
                                missingArguments.push(arg);
                            }
                        });

                        if (missingArguments.length > 0) {
                            const errorMessage = 'Error on arguments. Missing: ' + missingArguments.join(', ');
                            throw new Error(errorMessage);
                        }
                    }
                }

                loadProvincesSelect2();
                loadAgencySelect2();

                $(document.body).on('updated_cart_totals updated_checkout', function() {
                    loadProvincesSelect2();
                    loadAgencySelect2();
                });

            });
        </script>
<?php
    }
}


/**
 *
 */
function update_branch_handler()
{
    $branch_id = null;
    $branch_name = null;
    $branch_street_name = null;
    $branch_street_number = null;
    $branch_zip_code = null;
    $branch_city = null;
    $branch_state = null;
    $current_service_type = null;
    $chosen_product_type = null;

    if (isset($_POST['branch_id']) && isset($_POST["branch_name"]) && isset($_POST["branch_zip_code"])) {
        $branch_id = sanitize_text_field($_POST['branch_id']);
        $branch_name = sanitize_text_field($_POST['branch_name']);
        $branch_zip_code = sanitize_text_field($_POST['branch_zip_code']);
        $branch_city = sanitize_text_field($_POST['branch_city']);
        $branch_state = sanitize_text_field($_POST['branch_state']);
        $branch_street_name = sanitize_text_field($_POST['branch_street_name']);
        $branch_street_number = sanitize_text_field($_POST['branch_street_number']);
        $chosen_product_type = sanitize_text_field($_POST['branch_chosen_product_type']);
        $current_service_type = Utils::getCurrentServiceType();
    }

    WC()->session->set(CA_CHOSEN_BRANCH, $branch_id);
    WC()->session->set(CA_CHOSEN_BRANCH_NAME, $branch_name);
    WC()->session->set(CA_CHOSEN_BRANCH_STREET_NAME, $branch_street_name);
    WC()->session->set(CA_CHOSEN_BRANCH_STREET_NUMBER, $branch_street_number);
    WC()->session->set(CA_CHOSEN_BRANCH_CITY, $branch_city);
    WC()->session->set(CA_CHOSEN_BRANCH_STATE, $branch_state);
    WC()->session->set(CA_CHOSEN_BRANCH_ZIP_CODE, $branch_zip_code);
    WC()->session->set(CA_CHOSEN_SERVICE_TYPE, $current_service_type);
    WC()->session->set(CA_CHOSEN_PRODUCT_TYPE, $chosen_product_type);

    $totals = WC()->cart->get_totals();
    wp_send_json([
        'data' => 'ok',
        'branch_id' => $branch_id,
        "branch_name" => $branch_name,
        "branch_city" => $branch_city,
        "branch_state" => $branch_state,
        "branch_zip_code" => $branch_zip_code,
        "branch_street_name" => $branch_street_name,
        "branch_street_number" => $branch_street_number,
        "branch_chosen_product_type" => $chosen_product_type,
        "current_service_type" => $current_service_type,
        "shipping_total" => wc_price($totals["shipping_total"]),
        "fee_total" => wc_price($totals["fee_total"]),
        "cart_total" => wc_price($totals["total"]),
        "totals" => $totals
    ]);
    wp_die();
}

function update_province_handler()
{
    $province_code = null;
    $province_list = [];
    if (isset($_POST['province_code'])) {
        $province_code = sanitize_text_field($_POST['province_code']);
        if ($province_code != WC()->session->get(CA_CHOSEN_PROVINCE_CODE)) {
            // State chenged
            Utils::unset_wc_chosen_branch_values();
        }
        WC()->session->set(CA_CHOSEN_PROVINCE_CODE, $province_code);
        $province_list = CorreoArgentinoBranch::listBranches();
    }
    $response = count($province_list) > 0 ? [
        'data' => 'ok',
        'province_code' => $province_code,
        'province_list' => $province_list,
        'branch_id' => WC()->session->get(CA_CHOSEN_BRANCH)
    ] : [
        'data' => 'fail',
        'province_code' => $province_code,
        'province_list' => $province_list,
        'branch_id' => WC()->session->get(CA_CHOSEN_BRANCH)
    ];
    wp_send_json($response);
    wp_die();
}

function process_orders_handler()
{
    $orderId = $_POST['order_id'];
    $service = (new CorreoArgentinoServiceFactory())->get();
    $response = $service->registerOrder($orderId);

    $data = [
        'status' => 'ok',
        'message' => 'Successful operation',
        'reference' => $response['reference'],
        'order_id' => $orderId,
        'api_error_message' => $response['api_error_message']
    ];

    if (isset($response) && $response["error"] === true) {
        $error = json_decode($response["error_body"]);
        $errorMessage = $error->message;

        $data = [
            'status' => 'fail',
            'message' => $errorMessage,
            'reference' => null,
            'order_id' => $orderId,
            'api_error_message' => $response['api_error_message']
        ];
    }

    wp_send_json(array(
        'data' => $data,
    ), $data['status'] === 'ok' ? 201 : 400);
    wp_die();
}

/**
 * @param $order_id
 */
function new_order_handler($order_id)
{
    $order = new WC_Order($order_id);
    $current_branch = WC()->session->get(CA_CHOSEN_BRANCH);
    $current_branch_name = WC()->session->get(CA_CHOSEN_BRANCH_NAME);
    $current_branch_zip_code = WC()->session->get(CA_CHOSEN_BRANCH_ZIP_CODE);
    $current_service_type = WC()->session->get(CA_CHOSEN_SERVICE_TYPE);
    $chosen_product_type = WC()->session->get(CA_CHOSEN_PRODUCT_TYPE);
    $order->update_meta_data(CA_CHOSEN_BRANCH, $current_branch);
    $order->update_meta_data(CA_CHOSEN_BRANCH_NAME, $current_branch_name);
    $order->update_meta_data(CA_CHOSEN_BRANCH_ZIP_CODE, $current_branch_zip_code);
    $order->update_meta_data(CA_CHOSEN_SERVICE_TYPE, $current_service_type);
    $order->update_meta_data(CA_CHOSEN_PRODUCT_TYPE, $chosen_product_type);
    $order->save();
    Utils::unset_wc_chosen_branch_values();
}

/**
 * @param $order_id
 * @return bool | void
 * @throws Exception
 */

function on_payment_complete_handler($order_id)
{
    if (!Utils::shouldProcessOrder($order_id)) {
        return;
    }

    $service = (new CorreoArgentinoServiceFactory())->get();
    $response = $service->registerOrder($order_id);

    if (isset($response) && $response["error"] === true) {
        $error_body = json_decode($response["error_body"]);
        $ca_error = $error_body->message;
        WC_Admin_Notices::add_custom_notice('ca_error', "Error al preimponer la orden: " . $ca_error);
        return false;
    } else {
        return true;
    }
}

/**
 * @param $order_id
 * @return bool
 */
function on_order_cancelled_handler($order_id)
{
    if (Utils::getCurrentServiceType() === MI_CORREO) {
        return true;
    }
    $order = wc_get_order($order_id);
    $tracking = $order->get_meta(CA_TRACKING_NUMBER);
    if ($tracking) {
        $service = (new CorreoArgentinoServiceFactory())->get();
        $result = $service->cancel($tracking);
        if ($result) {
            return true;
        }
        return false;
    }
    return false;
}
