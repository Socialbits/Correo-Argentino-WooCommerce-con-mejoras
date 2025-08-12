<?php

//defined('ABSPATH') || exit;

/**
 * WooCommerce Order Metabox's base Class
 */
class CorreoArgentinoBranch
{

    /**
     * Method called by action woocommerce_after_shipping_rate.
     * 
     */
    public static function showBranches(WC_Shipping_Rate $method)
    {
        $shippingSelectIds = WC()->session->get('chosen_shipping_methods');
        $shippingSelected = reset($shippingSelectIds);

        $isCAShippingMethod = Utils::isCAShippingMethod($shippingSelected);
        if (!$isCAShippingMethod) {
            Utils::unset_wc_chosen_branch_values();
            return;
        }

        $id = $method->get_id();
        $isCAParamMethod = Utils::isCAShippingMethod($id);
        if (!$isCAParamMethod) {
            return;
        }

        $is_branch = Utils::isBranch($shippingSelected);
        if (!$is_branch) {
            Utils::unset_wc_chosen_branch_values();
            return;
        }

        if ($is_branch && in_array($id, $shippingSelectIds) && (is_cart() || is_checkout())) {

            // Show State select
            $provinces = Utils::getProvinces();
            // get shipping address
            $shipping_address = WC()->customer->get_shipping();
            $shipping_state = WC()->session->get(CA_CHOSEN_PROVINCE_CODE) ?? $shipping_address['state'];

            echo "<li>";
            echo "<div id=\"ca_container_select_provinces\">";
            echo "<select class=\"correoargentino_province_select\" >";
            echo "<option value=\"" . NONE . "\">" . CA_PLEASE_SELECT_A_DESTINATION_PROVINCE . "</option>";
            foreach ($provinces as $code => $name) {
                $selected = $shipping_state == $code ? "selected" : "";
                echo '<option value="' . $code . '" ' . $selected . '>' . $name . '</option>';
            }
            echo "</select>";
            echo "</div>";
            echo "</li>";

            // Show branches select
            $arr = $method->get_meta_data();
            $product_type = $arr[CA_CHOSEN_PRODUCT_TYPE];


            echo "<li>";
            echo "<div id=\"ca_container_select_$id\">";
            echo "<select disabled=\"disabled\" id=\"ca_branch_select_$id\" class=\"correoargentino_branch_select\" name=\"ca_branch_select\" data-product-type=\"$product_type\">";
            echo "</select>";
            echo "<input type=\"hidden\" name=\"" . CA_BRANCH_ZIP_CODE_HIDDEN_FIELD . "\" id=\"" . CA_BRANCH_ZIP_CODE_HIDDEN_FIELD . "\" value=\"" . NONE . "\"/>";
            echo "</div>";
            echo "</li>";

            WC()->session->set(CA_CHOSEN_PRODUCT_TYPE, $product_type);
        }
    }


    /**
     * @return array
     */
    public static function listBranches()
    {
        $settings = Utils::getSettings();
        $provinceCode = WC()->session->get(CA_CHOSEN_PROVINCE_CODE);
        $product_type = WC()->session->get(CA_CHOSEN_PRODUCT_TYPE);

        $service = (new CorreoArgentinoServiceFactory())->get();
        $serviceType = Utils::getCurrentServiceType();
        $options = $serviceType == MI_CORREO ? [
            "provinceCode" => $provinceCode,
            "customerId" => $settings["customer_id"],
        ] : [
            "stateId" => $provinceCode,
        ];
        $agencies = $service->getBranches($options);
        $results = [];
        if (!empty($agencies) && is_array($agencies)) {
            $results[] = [
                "id" => NONE,
                "text" => CA_CHOOSE_DESTINATION_AGENCY,
                "agency_name" => NONE,
                "agency_city" => NONE,
                "agency_state" => NONE,
                "agency_street_name" => NONE,
                "agency_street_number" => NONE,
                "agency_zip_code" => NONE,
                "agency_chosen_product_type" => NONE,
                "options" => $options
            ];
            foreach ($agencies as $value) {
                $text = self::getText($value);
                $results[] = self::getResults($value, $text, $product_type);
            }
        }
        return $results;
    }

    public static function getText($value)
    {
        $serviceType = Utils::getCurrentServiceType();
        $separator = ', ';
        $state = $serviceType == PAQ_AR ? $value['location']['state_name'] : $value['location']['address']['province'];
        $city = $serviceType == PAQ_AR ? $value['location']['city_name'] : $value['location']['address']['city'];
        $agency = $serviceType == PAQ_AR ? $value['agency_name'] : $value['name'];
        $streetName = $serviceType == PAQ_AR ? $value['location']['street_name'] : $value['location']['address']['streetName'];
        $streetNumber = $serviceType == PAQ_AR ? $value['location']['street_number'] : $value['location']['address']['streetNumber'];
        $zipCode = $serviceType == PAQ_AR ? $value['location']['zip_code'] : $value['location']['address']['postalCode'];

        return join(
            $separator,
            array(
                trim($state ?? ''),
                trim($city ?? ''),
                trim($agency ?? ''),
                trim($streetName ?? ''),
                trim($streetNumber ?? ''),
                "(" . trim($zipCode ?? '') . ")"
            ),
        );
    }

    public static function getResults($value, $text, $product_type)
    {
        $serviceType = Utils::getCurrentServiceType();
        $id = $serviceType == PAQ_AR ? $value['agency_id'] : $value['code'];
        $agencyName = $serviceType == PAQ_AR ? $value['agency_name'] : $value['name'];
        $agencyCity = $serviceType == PAQ_AR ? $value['location']['city_name'] : $value['location']['address']['city'];
        $agencyState = $serviceType == PAQ_AR ? $value['location']['state_name'] : $value['location']['address']['province'];
        $agencyStreetName = $serviceType == PAQ_AR ? $value['location']['street_name'] : $value['location']['address']['streetName'];
        $agencyStreetNumber = $serviceType == PAQ_AR ? $value['location']['street_number'] : $value['location']['address']['streetNumber'];
        $agencyZipCode = $serviceType == PAQ_AR ? $value['location']['zip_code'] : $value['location']['address']['postalCode'];

        return [
            "id" => trim($id),
            "text" => trim($text ?? ''),
            "agency_name" => trim($agencyName ?? ''),
            "agency_city" => trim($agencyCity ?? ''),
            "agency_state" => trim($agencyState ?? ''),
            "agency_zip_code" => trim($agencyZipCode ?? ''),
            "agency_street_name" => trim($agencyStreetName ?? ''),
            "agency_street_number" => trim($agencyStreetNumber ?? ''),
            "agency_chosen_product_type" => $product_type
        ];
    }

    /**
     * @param $branches
     * @return string
     */
    public static function buildOptions($branches)
    {
        $options = '';
        if (!empty($branches)) {
            foreach ($branches as $item) {
                $id = $item['id'];
                $text = $item['text'];
                $agency_name = $item["agency_name"];
                $agency_city = $item["agency_city"];
                $agency_street_name = $item["agency_street_name"];
                $agency_street_number = $item["agency_street_number"];
                $agency_state = $item["agency_state"];
                $agency_zip_code = $item["agency_zip_code"];
                $options .= '<option value="' . $id . '" data-branch-name="' . $agency_name . '" data-branch-zip-code="' . $agency_zip_code . '" data-branch-city="' . $agency_city . '"  data-branch-state="' . $agency_state . '" data-branch-street-name="' . $agency_street_name . '" data-branch-street-number="' . $agency_street_number . '">' . $text . '</option>';
            }
        }
        return $options;
    }
}
