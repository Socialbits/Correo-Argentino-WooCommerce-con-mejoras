<?php

/**
 *
 */
class CorreoArgentinoPaqArService implements CorreoArgentinoServicePaqArInterface
{

    /**
     * @var
     */
    public $client;


    /**
     * @var string
     */
    public $url;

    /**
     * @var false mixed void
     */
    private $agreement;

    /**
     * @var false mixed void
     */
    private $apiKey;


    /**
     * CorreoArgentinoService constructor.
     */
    public function __construct()
    {
        $credentials = maybe_unserialize(get_option(CA_CREDENTIALS));
        $this->url = (int)$credentials['sandbox'] === 1 ? CA_PAQ_AR_API_SANDBOX_URL : CA_PAQ_AR_API_URL;

        if (isset($_POST[CA_AGREEMENT_FIELD]) && isset($_POST[CA_APIKEY_FIELD])) {
            $this->agreement = $_POST[CA_AGREEMENT_FIELD];
            $this->apiKey = $_POST[CA_APIKEY_FIELD];
            if (isset($_POST[CA_SANDBOX_FIELD])) {
                $this->url = CA_PAQ_AR_API_SANDBOX_URL;
            }
        } else {
            $this->agreement = $credentials['agreement'];
            $this->apiKey = $credentials['api_key'];
        }
    }


    /**
     * @return mixed|null
     */
    public function login()
    {
        $request = wp_remote_get(
            $this->url . '/auth',
            [
                'method' => 'GET',
                'headers' => $this->setHeaders(),
            ]
        );
        $response = json_decode(wp_remote_retrieve_body($request), true);
        $response['status'] = wp_remote_retrieve_response_code($request);
        $response['message'] = wp_remote_retrieve_response_message($request);

        return $response;
    }

    /**
     * @return array
     */
    public function setHeaders($headers = [])
    {
        $defaultHeaders = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "Connection" => "keep-alive",
            "Authorization" => "Apikey " . $this->apiKey,
            "Agreement" => $this->agreement
        ];

        return array_merge($defaultHeaders, $headers);
    }

    public function getBranches($query = [])
    {
        $defaultQuery = ["pickup_availability" => 1];
        $query = array_merge($defaultQuery, $query);
        $request = wp_remote_get(
            add_query_arg($query, $this->url . '/agencies'),
            [
                'method' => 'GET',
                'headers' => $this->setHeaders(),
            ]
        );

        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
            error_log(json_encode($request));
            return false;
        }

        return json_decode(wp_remote_retrieve_body($request), true);
    }

    public function getRates($postalCode, $deliveryType, $dimensions, $settings = null)
    {
        /*$body = [
            "agreement" => "string",
            "deliveryType" => "string",
            "parcels" => [
                [
                    "declaredValue" => "string",
                    "dimensions" => [
                        "depth" => "string",
                        "height" => "string",
                        "width" => "string"
                    ],
                    "weight" => "string"
                ]
            ],
            "senderData" => [
                "zipCode" => "string"
            ],
            "serviceType" => "string",
            "shippingData" => [
                "zipCode" => "string"
            ]
        ];
        $res = $this->client->post('/rates', ['body' => json_encode($body)]);
        $code = $res->getStatusCode();
        if ($code == 200) {
            return $res->json();
        }
        return $code;*/
        return [
            "customerId" => "",
            "validTo" => "",
            "rates" => [
                [
                    "deliveredType" => "$deliveryType",
                    "productType" => "CP",
                    "productName" => "Correo Argentino Clasico",
                    "price" => 0,
                    "deliveryTimeMin" => "2",
                    "deliveryTimeMax" => "5"
                ],
                [
                    "deliveredType" => "$deliveryType",
                    "productType" => "EP",
                    "productName" => "Correo Argentino Expreso",
                    "price" => 0,
                    "deliveryTimeMin" => "1",
                    "deliveryTimeMax" => "3"
                ],
            ]
        ];
    }

    /**
     * @param $tracking
     * @return mixed
     */
    public function label($tracking)
    {
        $body = [["trackingNumber" => $tracking]];
        $request = wp_remote_post(
            $this->url . '/labels',
            [
                'method' => 'POST',
                'headers' => $this->setHeaders(),
                'body' => json_encode(($body))
            ]
        );

        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
            error_log(json_encode($request));
            return false;
        }

        return json_decode(wp_remote_retrieve_body($request), true);
    }

    /**
     * @param $tracking
     * @return mixed
     */
    public function cancel($tracking)
    {

        $request = wp_remote_post(
            $this->url . '/orders/' . $tracking . '/cancel',
            [
                'method' => 'PATCH',
                'headers' => $this->setHeaders(),

            ]
        );

        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
            error_log(json_encode($request));
            return false;
        }

        return true;
    }

    /**
     * @param $orderId
     * @return array|mixed
     */
    public function registerOrder($orderId)
    {
        $order = wc_get_order($orderId);
        $shippingItems = $order->get_items('shipping');
        $shippingMethod = reset($shippingItems);
        $ca_data = $shippingMethod->get_meta('ca_data');
        $orderData = $order->get_data();
        $branch_id = $order->get_meta(CA_CHOSEN_BRANCH);
        $shipping = $orderData['shipping'];
        $billing = $orderData['billing'];
        $items = $order->get_items();
        $shippingMethod = $order->get_shipping_method();
        $isBranch = stripos($shippingMethod, BRANCH);
        $dataStore = array_merge(Utils::getSettings(), $ca_data);
        $products = array_values(
            array_map(function ($item) {
                $total = $item->get_total();
                $dimension = $item->get_product()->get_dimensions(false);
                $product_name = $item->get_name();
                $weight = $item->get_product()->get_weight();

                return [
                    "declaredValue" => floatval($total),
                    "dimensions" => [
                        "depth" => Utils::dimensionToInteger(empty($dimension['length']) ? 1 : $dimension['length']),
                        "height" => Utils::dimensionToInteger(empty($dimension['height']) ? 1 : $dimension['height']),
                        "width" => Utils::dimensionToInteger(empty($dimension['width']) ? 1 : $dimension['width']),
                    ],
                    "productCategory" => $product_name,
                    "productWeight" => Utils::fromKgToGrams($weight)
                ];
            }, $items)
        );

        $streetName = $shipping['address_1']; 
        $altura = !empty($shipping['address_2']) ? $shipping['address_2'] : '0'; 
        $fullAddress = trim("{$streetName} {$altura}");

        if ($isBranch) {
            $branch = $this->getBranch($branch_id);
            $location = $branch['location'];
            $shippingData = [
                "address" => [
                    "cityName" => $location['city_name'] ?? 'CABA',
                    "department" => "",
                    "floor" => "",
                    "state" => "B",
                    "streetName" => $fullAddress, 
                    "streetNumber" => 0,
                    "zipCode" => Utils::normalizeZipCode($location['zip_code'] ?? '1407')
                ],
                "areaCodeCellphone" => "54",
                "areaCodePhone" => "54",
                "phoneNumber" => $shipping['phone'],
                "cellphoneNumber" => $shipping['phone'],
                "email" => $billing['email'],
                "name" => $billing['first_name'] . ' ' . $billing['last_name'],
                "observation" => "agency",
            ];
        } else {
            $shippingData = [
                "address" => [
                    "cityName" => $shipping['city'] ?? 'CABA',
                    "department" => "",
                    "floor" => "",
                    "state" => $shipping['state'],
                    "streetName" => $fullAddress, 
                    "streetNumber" => 0,
                    "zipCode" => $shipping['postcode']
                ],
                "areaCodeCellphone" => "54",
                "areaCodePhone" => "54",
                "phoneNumber" => $shipping['phone'],
                "cellphoneNumber" => $shipping['phone'],
                "email" => $billing['email'],
                "name" => $billing['first_name'] . ' ' . $billing['last_name'],
                "observation" => $orderData['customer_note'],
            ];
        }
        $cellphone = !empty($dataStore['cellphone']) ? explode('-', $dataStore['cellphone']) : ['', ''];
        $phone = !empty($dataStore['phone']) ? explode('-', $dataStore['phone']) : ['', ''];
        $senderData = [
            "address" => [
                "cityName" => $dataStore['city_name'],
                "department" => $dataStore['department'],
                "floor" => $dataStore['floor'],
                "state" => $dataStore['state'],
                "streetName" => $dataStore['street_name'],
                "streetNumber" => $dataStore['street_number'],
                "zipCode" => Utils::normalizeZipCode($dataStore['zip_code']),
            ],
            "businessName" => $dataStore['business_name'],
            "areaCodeCellphone" => $cellphone[0],
            "cellphoneNumber" => $cellphone[1],
            "areaCodePhone" => $phone[0],
            "phoneNumber" => $phone[1],
            "email" => $dataStore['email'],
            "observation" => $dataStore['observation']
        ];

        $body = [
            "sellerId" => "",
            "trackingNumber" => "",
            "order" => [
                "agencyId" => $isBranch ? $branch_id : null,
                "deliveryType" => $isBranch ? AGENCY : HOME_DELIVERY,
                "parcels" => $products,
                "shipmentClientId" => "A0000",
                "serviceType" => "CP",
                "saleDate" => date('Y-m-d\TH:i:sO'),
                "senderData" => $senderData,
                "shippingData" => $shippingData,
            ]
        ];


        $request = wp_remote_post(
            $this->url . '/orders',
            [
                'method' => 'POST',
                'headers' => $this->setHeaders(),
                'body' => json_encode($body)
            ]
        );


        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
            $error_body = json_decode(wp_remote_retrieve_body($request));
            if (isset($error_body->message)) {
                $order->update_meta_data(CA_ORDER_API_ERROR, $error_body->message);
                $order->save();
            }

            error_log(wp_json_encode($request));
            return [
                "error" => true,
                "error_body" => wp_remote_retrieve_body($request),
            ];
        }
        $response = json_decode(wp_remote_retrieve_body($request), true);
        $order->update_meta_data(CA_TRACKING_NUMBER, $response["trackingNumber"]);
        $order->save();
        return $response;
    }

    public function getBranch($branch_id)
    {
        $agencies = json_decode(file_get_contents(plugin_dir_path(__FILE__) . '../Mock/agencies.json'), true);

        $find = null;

        for ($i = 0; $i < count($agencies); $i++) {
            $data = $agencies[$i];
            if ($branch_id == $data['agency_id']) {
                $find = $data;
                break;
            }
        }

        return $find;
    }
}
