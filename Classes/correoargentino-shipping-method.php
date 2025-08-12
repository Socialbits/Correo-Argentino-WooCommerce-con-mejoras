<?php
error_reporting(0);

session_start();

if (!defined('ABSPATH')) {
    exit;
}


if (!class_exists('WC_Correo_Argentino_Shipping_Method')) {
    class WC_Correo_Argentino_Shipping_Method extends WC_Shipping_Method
    {

        public $ca_data = [];
        private $generalKey = 0;

        public function __construct($instance_id = 0)
        {


            $this->id = CA_PLUGIN_ID;
            $this->instance_id = absint($instance_id);
            $this->method_title = __('Correo Argentino', 'correoargentino');
            $this->method_description = __('Plugin oficial de Correo Argentino para Woocommerce', 'correoargentino');
            $this->supports = [
                'settings',
                'shipping-zones',
                'instance-settings',
                // 'instance-settings-modal',
            ];

            $this->init();
            $this->title = $this->ca_data[$instance_id]['title'] ? $this->ca_data[$instance_id]['title'] : $this->method_title;

            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }


        public function init()
        {
            try {
                $this->instance_form_fields = CorreoArgentinoAdminForm::getInstanceBusinessForm();

                global $ca_error;
                if (is_admin()) {
                    $this->handleAdminForms();
                }
                if ($ca_error) {
                    add_action('admin_notices', array($this, 'display_errors', $ca_error));
                }
                $this->init_settings();

                $this->loadData();
            } catch (Exception $e) {
                WC_Admin_Settings::add_error('Error: ' . $e->getMessage());
            }
        }

        private function loadData()
        {
            if (!isset($this->instance_id) && (get_option(CA_BUSINESS_DETAILS_FILLED) === 0)) {
                throw new Exception("Correo Argentino se encuentra activo y Datos de Negocio no completados");
            }

            $key = null;
            if (!isset($this->instance_id) && (get_option(CA_BUSINESS_DETAILS_FILLED) === 1)) {
                $key = $this->generalKey;
            }

            if (isset($this->instance_id)) {
                $key = $this->instance_id;
            }

            if (!isset($key)) {
                throw new Exception("Correo Argentino se encuentra activo y no se dispone de la clave para realizar consultas");
            }

            // All forms keys
            $keys = [
                'business_name',
                'email',
                'state',
                'city_name',
                'department',
                'floor',
                'street_name',
                'street_number',
                'zip_code',
                'cellphone',
                'phone',
                'title',
                'metodo_envio',
                'tipo_servicio',
                'observation'
            ];

            $row = [];
            foreach ($keys as $k) {
                $row[strtolower($k)] = $this->get_option($k);
            }
            $this->ca_data[$key] = $row;
        }

        private function handleAdminForms()
        {
            $isConnected = Utils::isConnected();
            $currentServiceType = Utils::getCurrentServiceType();

            $this->handleServiceSelectorForm($currentServiceType, $isConnected);
            $this->handleUserValidateMiCorreoForm();
            $this->handleLoginPaqArForm();
            $this->handleRegisterMiCorreoForm();
            $this->handleBusinessForm($isConnected);
            $this->display_errors();
        }


        /**
         * environment_check function.
         */
        private function environment_check()
        {
            if (WC()->countries->get_base_country() !== 'AR') {
                echo '
                    <div class="error">
        		        <p>' . __('Correo Argentino. Argentina tiene que ser el pais de Origen.', 'correoargentino') . '</p>
        	        </div>';
            }
        }

        /**
         * admin_options function.
         */
        public function admin_options()
        {
            // Check users environment supports this method
            $this->environment_check();

            // Show settings
            parent::admin_options();
        }


        private function handleServiceSelectorForm($currentServiceType, $isConnected)
        {
            if (isset($_GET['page'], $_GET['section'], $_GET['form']) && $_GET['page'] === 'wc-settings' && $_GET['section'] === CA_PLUGIN_ID && $_GET['form'] == 'service-selector') {
                if (isset($currentServiceType) && $isConnected) {
                    $formType = $currentServiceType == PAQ_AR ? CA_LOGIN_PAQ_AR_FORM : CA_USER_VALIDATE_MI_CORREO_FORM;
                    wp_safe_redirect("?page=wc-settings&tab=shipping&section={$this->id}&form={$formType}");
                    return;
                }
                $this->method_description = __('Ahora seleccioná con cuál servicio querés operar.', 'correoargentino');
                $this->form_fields = CorreoArgentinoAdminForm::getServiceSelectorForm();
            }
        }

        private function handleUserValidateMiCorreoForm()
        {
            $is_valid_request = isset($_GET['page'], $_GET['section'], $_GET['form'])
                && $_GET['page'] === 'wc-settings'
                && $_GET['section'] === CA_PLUGIN_ID
                && $_GET['form'] == CA_USER_VALIDATE_MI_CORREO_FORM;

            if ($is_valid_request) {
                if (($this->instance_id == 0) && CorreoArgentinoMiCorreoService::isValidToken()) {
                    WC_Admin_Settings::add_message('Ya podés operar en MiCorreo, actualizá estos datos solo si deseas renovar la sesión.');
                }
                $this->form_fields = CorreoArgentinoAdminForm::getUserValidateMiCorreoForm();
            }
        }

        private function handleLoginPaqArForm()
        {
            $is_valid_request = isset($_GET['page'], $_GET['section'], $_GET['form'])
                && $_GET['page'] === 'wc-settings'
                && $_GET['section'] === CA_PLUGIN_ID
                && $_GET['form'] == CA_LOGIN_PAQ_AR_FORM;

            if ($is_valid_request) {
                $this->form_fields = CorreoArgentinoAdminForm::getLoginPaqArForm();
            }
        }

        private function handleRegisterMiCorreoForm()
        {
            $is_valid_request = isset($_GET['page'], $_GET['section'], $_GET['form'])
                && $_GET['page'] === 'wc-settings'
                && $_GET['section'] === CA_PLUGIN_ID
                && $_GET['form'] == CA_BUSINESS_MI_CORREO_FORM;

            if ($is_valid_request) {
                $this->method_description = __('Ahora agregá los datos de tu negocio.', 'correoargentino');
                $this->form_fields = CorreoArgentinoAdminForm::getMiCorreoBusinessForm();
            }
        }

        private function handleBusinessForm($isConnected)
        {
            $is_valid_request = isset($_GET['page'], $_GET['section'], $_GET['form'])
                && $_GET['page'] === 'wc-settings'
                && $_GET['section'] === CA_PLUGIN_ID
                && $_GET['form'] == 'business';

            if ($is_valid_request) {
                if (!$isConnected) {
                    wp_safe_redirect("?page=wc-settings&tab=shipping&section={$this->id}");
                    exit;
                }
                $this->method_description = __('Ahora agregá los datos de tu negocio.', 'correoargentino');
                $this->form_fields = CorreoArgentinoAdminForm::getBusinessForm();
            }
        }

        private function getActiveForm(): array
        {
            if (isset($this->instance_id) && ($this->instance_id > 0) && (count($this->instance_form_fields) > 0)) {
                return $this->instance_form_fields;
            }
            return $this->form_fields;
        }

        public function validate_text_field($key, $value)
        {
            $form = $this->getActiveForm();
            if (isset($form[$key])) {
                // if title ends with $variable
                if (
                    strpos($form[$key]['title'], Utils::REQUIRED_SIGN) !== false &&
                    empty($value)
                ) {
                    $this->add_field_error($key);
                    return false;
                }
            }
            return $value;
        }

        public function validate_last_name_field($key, $value)
        {
            $form = $this->getActiveForm();
            if (
                isset($_POST['woocommerce_' . CA_PLUGIN_ID . '_document_type']) &&
                $_POST['woocommerce_' . CA_PLUGIN_ID . '_document_type'] == 'DNI'
            ) {
                if (isset($form[$key])) {
                    if (empty($value)) {
                        $this->add_field_error($key);
                    }
                }
            }
            return $value;
        }

        public function validate_password_field($key, $value)
        {
            $form = $this->getActiveForm();
            if (isset($form[$key])) {
                // if title ends with $variable
                if (empty($value)) {
                    $this->add_field_error($key);
                }
                // check if password is longer between 6 and 20 characters
                if (strlen($value) < 6) {
                    $this->add_field_error($key, 'La contraseña debe tener al menos 6 caracteres');
                }
                if (strlen($value) > 20) {
                    $this->add_field_error($key, 'La contraseña no puede tener más de 20 caracteres');
                }
            }
            return $value;
        }

        private function invalid_same_method_type($metodo_envio, $tipo_servicio): bool
        {
            $instance_id = $this->instance_id;
            $my_zone = WC_Shipping_Zones::get_zone_by('instance_id', $instance_id);
            $all_shipping_methods_in_zone = $my_zone->get_shipping_methods(true);
            $ca_shipping_methods_in_zone = array_filter(
                $all_shipping_methods_in_zone,
                function ($shipping_method) use ($instance_id) {
                    return $shipping_method->id == CA_PLUGIN_ID && $shipping_method->instance_id != $instance_id;
                }
            );

            $ca_shipping_methods_with_same_method_type = array_filter(
                $ca_shipping_methods_in_zone,
                function ($shipping_method) use ($metodo_envio, $tipo_servicio) {
                    $settings = $shipping_method->instance_settings;
                    if ($settings && $settings['metodo_envio'] && $settings['tipo_servicio']) {
                        return $settings['metodo_envio'] == $metodo_envio && $settings['tipo_servicio'] == $tipo_servicio;
                    }
                    return false;
                }
            );
            return $ca_shipping_methods_with_same_method_type && count($ca_shipping_methods_with_same_method_type) > 0;
        }

        public function validate_title_field($key, $value)
        {
            if (!$_POST['woocommerce_correoargentino_shipping_method_metodo_envio']) {
                $this->add_field_error($key, 'El método de envío debe ser seleccionado');
            }
            if (!$_POST['woocommerce_correoargentino_shipping_method_tipo_servicio']) {
                $this->add_field_error($key, 'El tipo de servicio debe ser seleccionado');
            }
            $metodo_envio = $_POST['woocommerce_correoargentino_shipping_method_metodo_envio'];
            $tipo_servicio = $_POST['woocommerce_correoargentino_shipping_method_tipo_servicio'];

            if ($this->invalid_same_method_type($metodo_envio, $tipo_servicio)) {
                $this->add_field_error($key, 'Existe otra instancia de Correo Argentino, con el mismo Tipo de Servicio y Método de envío');
                return false;
            }

            $labelTipoServicio = [
                'E' => 'expreso',
                'C' => 'clásico'
            ];
            $labelMetodoEnvio = [
                'D' => 'a domicilio',
                'S' => 'a sucursal'
            ];

            return "Correo Argentino {$labelTipoServicio[$tipo_servicio]} {$labelMetodoEnvio[$metodo_envio]}";
        }

        private function add_field_error($key, $message = '')
        {
            $form = $this->getActiveForm();
            if (isset($form[$key]['class'])) {
                $form[$key]['class'] .= ' error';
            } else {
                $form[$key]['class'] = 'error';
            }
            if ('' == $message) {
                $message = str_replace(Utils::REQUIRED_SIGN, '', $form[$key]['title']) . ' es requerido';
            }
            WC_Admin_Settings::add_error($message);
            throw new Exception($message);
        }


        // /**
        //  * @throws Exception
        //  */
        // public function get_rates()
        // {
        //     if ($this->shouldSkip()) {
        //         return;
        //     }

        //     $postalCode = $this->updateDestinationPostcode();
        //     $dimensions = $this->getDimensions();

        //     $service = (new CorreoArgentinoServiceFactory())->get();

        //     return $this->fetchRates($service, $postalCode, $dimensions);
        // }

        public function getDimensions()
        {
            $cart = WC()->cart;
            $packages = $cart->get_shipping_packages();
            $weight = 0;
            $height = 0;
            $restDimensions = [];
            foreach ($packages as $package) {
                foreach ($package['contents'] as $item) {
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];
                    $product = wc_get_product($productId);
                    $dimensions = Utils::getProductDimensions($product);
                    $weight += $dimensions['weight'] * $quantity;
                    $orderedDimensions = [$dimensions['height'], $dimensions['width'], $dimensions['length']];
                    sort($orderedDimensions);
                    $height += $orderedDimensions[0] * $quantity;
                    for ($i = 0; $i < $quantity; $i++) {
                        $restDimensions[] = [$orderedDimensions[1], $orderedDimensions[2]];
                    }
                }
            }

            $widths = array_column($restDimensions, 0);
            $lengths = array_column($restDimensions, 1);

            $width = max($widths);
            $length = max($lengths);

            return [
                "weight" => $weight,
                "height" => $height,
                "width" => $width,
                "length" => $length
            ];
        }




        // /**
        //  * @return bool
        //  */
        // private function shouldSkip()
        // {
        //     return is_admin() && !defined('DOING_AJAX') && !is_cart() && !is_checkout();
        // }

        private function updateDestinationPostcode()
        {
            $cart = WC()->cart;
            $package = $cart->get_shipping_packages();

            if (
                isset($this->ca_data) &&
                isset($this->ca_data[$this->instance_id]) &&
                isset($this->ca_data[$this->instance_id]['metodo_envio']) &&
                $this->ca_data[$this->instance_id]['metodo_envio'] == 'S'
            ) {
                if ((is_checkout() || (defined('DOING_AJAX') && DOING_AJAX)) && Utils::getUseRates() && WC()->session->get(CA_CHOSEN_BRANCH_ZIP_CODE) !== null) {
                    return Utils::normalizeZipCode(WC()->session->get(CA_CHOSEN_BRANCH_ZIP_CODE));
                }
            }

            return $package[0]['destination']['postcode'];
        }

        private function getMostValuedProductInCart()
        {
            $cart = WC()->cart;
            $packages = $cart->get_shipping_packages();
            $maxVolumetricWeight = 0;
            $mostValuedProduct = null;

            foreach ($packages as $package) {
                foreach ($package['contents'] as $item) {
                    $productId = $item['product_id'];
                    $product = wc_get_product($productId);

                    $dimensions = Utils::getProductDimensions($product);
                    $volumetricWeight = ($dimensions["length"] * $dimensions["width"] * $dimensions["height"]);

                    if ($volumetricWeight > $maxVolumetricWeight) {
                        $maxVolumetricWeight = $volumetricWeight;
                        $mostValuedProduct = $product;
                    }
                }
            }

            return $mostValuedProduct;
        }


        // private function getProductDimensions($product): array
        // {
        //     return [
        //         "weight" => Utils::fromKgToGrams($product->get_weight()),
        //         "height" => floatval($product->get_height()),
        //         "width" => floatval($product->get_width()),
        //         "length" => floatval($product->get_length())
        //     ];
        // }

        private function validateProductDimensions($dimensions): bool
        {

            $maxWeight = Utils::getCurrentServiceType() == PAQ_AR ? CA_MAX_WEIGHT_PAQ_AR : CA_MAX_WEIGHT_MI_CORREO;
            $maxLength = Utils::getCurrentServiceType() == PAQ_AR ? CA_MAX_LENGTH_PAQ_AR : CA_MAX_LENGTH_MI_CORREO;
            $maxVolumetric = CA_MAX_LENGTH_MI_CORREO;

            if (
                $dimensions["weight"] > $maxWeight ||
                $dimensions["length"] > $maxLength ||
                ($dimensions['height'] +
                    $dimensions['length'] +
                    $dimensions['width']) > $maxVolumetric
            ) {
                return false;
            }

            return true;
        }

        private function fetchRates($service, $postalCode, $dimensions, $settings = null, $instance_id = null)
        {
            $cache_key = md5(serialize([$postalCode, $dimensions, $settings]));
            $old_key = WC()->session->get('rateListKey_' . $instance_id);
            if ($old_key != $cache_key) {
                WC()->session->set('rateList_' . $instance_id, null);
            }

            $cached = WC()->session->get('rateList_' . $instance_id);

            if ($cached) {
                return $cached;
            }

            $rateList = [];

            if ('' == $postalCode) {
                return;
            }

            $ratesHome = $service->getRates($postalCode, 'D', $dimensions, $settings);

            // @todo: this disables Correo Argentino Hoy - Domicilio
            $ratesHomeFiltered = array_filter($ratesHome["rates"], function ($rate) {
                return $rate['productName'] !== "Correo Argentino Hoy";
            });
            $ratesHome["rates"] = $ratesHomeFiltered;

            $ratesAgency = $service->getRates($postalCode, 'S', $dimensions, $settings);

            if (!isset($ratesHome["rates"]) || !isset($ratesAgency["rates"])) {
                return $rateList;
            }

            $rates = [
                "rates" => array_merge($ratesHome["rates"], $ratesAgency["rates"])
            ];

            if (!empty($rates['rates'])) {
                foreach ($rates['rates'] as $i => $rate) {
                    if ($rate && $rate['productName']) {
                        $branchLabel = $rate['deliveredType'] === 'S' ? '_branch' : '';
                        $rateList[] = [
                            "ID" => md5($rate['productName'] . $rate['deliveredType']) . $branchLabel,
                            "serviceName" => $rate['productName'],
                            "description" => $rate['productName'],
                            "serviceCode" => $rate['productType'],
                            'deliveredType' => $rate['deliveredType'],
                            "currency" => "ARS",
                            "totalPrice" => (float) $rate['price']
                        ];
                    }
                }
            }

            WC()->session->set('rateList_' . $instance_id, $rateList);
            WC()->session->set('rateListKey_' . $instance_id, $cache_key);
            return $rateList;
        }

        private function getElementById($array, $id)
        {
            foreach ($array as $index => $element) {
                if (isset($element['ID']) && $element['ID'] == $id) {
                    return ['element' => $element, 'index' => $index];
                }
            }
            return null;
        }


        public function calculate_rates($package = array())
        {
            global $count_correoargentino_size_message;

            if (!Utils::canListRates($this->ca_data[$this->instance_id])) {
                return [];
            }

            $dimensions = $this->getDimensions();
            if (!$this->validateProductDimensions($dimensions)) {
                if (is_cart() || is_checkout()) {
                    if (!(defined('DOING_AJAX') && DOING_AJAX)) {
                        if ($count_correoargentino_size_message === null) {
                            Utils::message('Uno o más paquetes exceden el peso y/o medidas máximas permitidas por Correo Argentino', 'error', false, false);
                            $count_correoargentino_size_message = 1;
                        }
                    }
                }
                return [];
            }

            $postalCode = $this->updateDestinationPostcode();
            if (!$postalCode) {
                return [];
            }

            $weight = 0;
            foreach ($package['contents'] as $values) {
                $product = $values['data'];
                $weight += $product->get_weight() * $values['quantity'];
            }
            $weight = wc_get_weight($weight, 'g');

            $dimensions['weight'] = $weight;

            $service = (new CorreoArgentinoServiceFactory())->get();
            $rates = $this->fetchRates($service, $postalCode, $dimensions, $this->ca_data[$this->instance_id], $this->instance_id);

            if (!$rates) {
                return [];
            }

            $ratesArray = [];
            foreach ($rates as $rate) {
                $key = $rate['serviceCode'] . $rate['deliveredType'];
                $ratesArray[$key] = $rate;
            }

            return $ratesArray;
        }


        public function calculate_shipping($package = array())
        {
            $ratesArray = $this->calculate_rates($package);
            if (empty($ratesArray)) {
                return false;
            }

            $metodo_envio = $this->get_option('metodo_envio');
            $tipo_servicio = $this->get_option('tipo_servicio');

            if (isset($ratesArray[$tipo_servicio . 'P' . $metodo_envio])) {
                $rate = $ratesArray[$tipo_servicio . 'P' . $metodo_envio];
                // Register the rate
                $this->add_rate([
                    'id' => $this->id . '_' . $this->instance_id,
                    'label' => $this->title,
                    'cost' => $rate['totalPrice'],
                    'calc_tax' => '',
                    'meta_data' => array(
                        CA_IS_BRANCH => $rate['deliveredType'] === 'S' ? true : false,
                        CA_CHOSEN_SERVICE_TYPE => Utils::getCurrentServiceType(),
                        CA_CHOSEN_PRODUCT_TYPE => $rate['serviceCode'],
                        CA_CHOSEN_BRANCH => null,
                        CA_CHOSEN_BRANCH_NAME => null,
                        CA_CHOSEN_BRANCH_ZIP_CODE => null
                    )
                ]);
            }
        }

        // private function displayPrice($price)
        // {
        //     return Utils::getUseRates() && is_checkout() ? $price : 0;
        // }
    }
}
