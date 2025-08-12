<?php

class Utils
{
    const REQUIRED_SIGN = '<sup>*</sup>';

    static function getProvinces(): array
    {
        $provinces = json_decode(file_get_contents(plugin_dir_path(__FILE__) . '../Mock/provinces.json'), true);
        $results = [];
        foreach ($provinces["provincias"] as $item) {
            $code = $item['iso_id'];
            $results[$code] = $item['nombre'];
        }
        asort($results);
        return $results;
    }

    static function getDocumentTypes(): array
    {
        return array("DNI" => "DNI", "CUIT" => "CUIT");
    }

    static function getProductDimensions($product): array
    {
        return [
            "weight" => Utils::fromKgToGrams($product->get_weight()),
            "height" => ceil(floatval($product->get_height())),
            "width" => ceil(floatval($product->get_width())),
            "length" => ceil(floatval($product->get_length()))
        ];
    }

    static function getAvailableServices(): array
    {
        return array(
            "miCorreo" => "PAQ.AR API MiCorreo",
            "paq.ar" => "PAQ.AR API 2.0"
        );
    }

    /**
     * @param $code
     * @return array |string|null
     */
    public static function normalizeZipCode($code)
    {
        return preg_replace("/[^0-9]/", "", $code);
    }

    public static function cleanPhone($phone)
    {
        return preg_replace("/\-/", "", $phone);
    }


    public static function getCurrentServiceType()
    {
        return get_option(CA_SERVICE_TYPE);
    }

    public static function getUseRates()
    {
        return get_option(CA_USE_RATES);
    }

    public static function isConnected()
    {
        return get_option(CA_IS_CONNECTED);
    }

    public static function canListRates($settings = null)
    {

        if (get_option(CA_IS_CONNECTED) != 1) {
            // component is not login
            return false;
        }

        if (!$settings) {
            $settings = self::getSettings();
        }

        $requiredFields = array(
            'business_name',
            'state',
            'city_name',
            'street_name',
            'street_number',
            'zip_code',
        );

        foreach ($requiredFields as $field) {
            if (empty($settings[$field])) {
                return false;
            }
        }

        return true;
    }

    public static function getSettings()
    {
        return maybe_unserialize(get_option(CA_SETTINGS));
    }

    public static function businessDetailsFilled()
    {
        return get_option(CA_BUSINESS_DETAILS_FILLED);
    }

    public static function getCredentials()
    {
        return maybe_unserialize(get_option(CA_CREDENTIALS));
    }

    public static function trace($v, $die = true, $type = 'notice')
    {
        echo '<div class="' . $type . ' inline"><pre>';
        var_dump($v);
        echo '</pre></div>';
        if ($die)
            die();
    }

    public static function message($message, $type = 'message', $dismissible = false, $attach = true)
    {
        $classes = 'notice notice-' . esc_attr($type);
        if (!is_admin()) {
            $classes .= ' woocommerce-' . esc_attr($type);
        }
        if ($dismissible) {
            $classes .= ' is-dismissible';
        }
        if ($attach) {
            add_action('admin_notices', function () use ($message, $classes) {
                echo '<div class="' . esc_attr($classes) . '"><p>' . $message . '</p></div>';
            });
        } else {
            echo '<div class="' . esc_attr($classes) . '"><p>' . $message . '</p></div>';
        }
    }

    public static function unset_wc_chosen_branch_values()
    {
        WC()->session->__unset(CA_CHOSEN_BRANCH);
        WC()->session->__unset(CA_CHOSEN_BRANCH_NAME);
        WC()->session->__unset(CA_CHOSEN_BRANCH_ZIP_CODE);
        WC()->session->__unset(CA_CHOSEN_BRANCH_STREET_NAME);
        WC()->session->__unset(CA_CHOSEN_BRANCH_STREET_NUMBER);
        WC()->session->__unset(CA_CHOSEN_BRANCH_CITY);
        WC()->session->__unset(CA_CHOSEN_BRANCH_STATE);
        WC()->session->__unset(CA_CHOSEN_SERVICE_TYPE);
        WC()->session->__unset(CA_CHOSEN_PRODUCT_TYPE);
        WC()->session->__unset(CA_CHOSEN_PROVINCE_CODE);
    }

    public static function isCAShippingMethod($chosenShippingMethod)
    {
        return strpos($chosenShippingMethod, CA_PLUGIN_ID) === 0;
    }

    public static function isBranch($chosenShippingMethod)
    {
        require_once plugin_dir_path(__FILE__) . '../Classes/correoargentino-shipping-method.php';
        if (strpos($chosenShippingMethod, CA_PLUGIN_ID) === 0) {
            $shipping_method_id = (int) substr($chosenShippingMethod, strlen(CA_PLUGIN_ID) + 1);

            if ($shipping_method_id > 0) {
                $Correo_Argentino_Shipping_Method = new WC_Correo_Argentino_Shipping_Method($shipping_method_id);

                $metodo_envio = $Correo_Argentino_Shipping_Method->get_option('metodo_envio');

                if ($metodo_envio === 'S') {
                    return true;
                }
            }
        }
        return false;
    }

    public static function fromKgToGrams($kg)
    {
        // Remove thousand separators (,)
        $kg = str_replace(',', '', $kg);

        // Replace the last dot (.) with an empty string if multiple dots are present
        $kg = preg_replace('/\.(\d*)\./', '.$1', $kg);

        // Replace the first dot (.) with an empty string if at the beginning of the number
        $kg = preg_replace('/^(\.)/', '', $kg);

        // Convert the input to a float and then multiply by 1000
        return floatval($kg) * 1000;
    }

    public static function toInteger($num): int
    {
        $num = str_replace(',', '.', $num);

        $result = floatval($num);
        if ($result < 1) {
            $result = ceil($result);
        }

        return (int)$result;
    }

    public static function dimensionToInteger($num): int
    {
        $num = str_replace(',', '.', $num);

        $result = floatval($num);

        $result = ceil($result);

        return (int)$result;
    }

    public static function shouldProcessOrder($order_id)
    {
        $chosenServiceType = Utils::getOrderServiceType($order_id);
        $currentServiceType = Utils::getCurrentServiceType();

        return $chosenServiceType === $currentServiceType;
    }

    public static function getOrderServiceType($order_id)
    {
        global $wpdb;

        $prefix = $wpdb->prefix;

        $order_items_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$prefix}woocommerce_order_items'");
        $order_itemmeta_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$prefix}woocommerce_order_itemmeta'");

        if ($order_itemmeta_table_exists && $order_items_table_exists) {
            $query = $wpdb->prepare(
                "SELECT meta_value 
                FROM {$prefix}woocommerce_order_itemmeta AS itemmeta
                INNER JOIN {$prefix}woocommerce_order_items AS items ON itemmeta.order_item_id = items.order_item_id
                WHERE itemmeta.meta_key = %s 
                AND items.order_id = %d",
                CA_CHOSEN_SERVICE_TYPE,
                $order_id
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT meta_value 
                FROM {$prefix}postmeta 
                WHERE meta_key = %s 
                AND post_id = %d",
                CA_CHOSEN_SERVICE_TYPE,
                $order_id
            );
        }

        $result = $wpdb->get_var($query);

        return $result ? $result : '';
    }
}
