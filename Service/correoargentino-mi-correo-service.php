<?php

class CorreoArgentinoMiCorreoService implements CorreoArgentinoServiceMiCorreoInterface
{
    public $client;
    public $url;
    private $username;
    private $password;
    private $authHash;
    private $accessToken;

    public function __construct()
    {
        $credentials = maybe_unserialize(get_option(CA_CREDENTIALS));
        $this->url = (int)$credentials['sandbox'] === 1 ? CA_MI_CORREO_API_SANDBOX_URL : CA_MI_CORREO_API_URL;
        $this->authHash = $credentials['auth_hash'];
        if (isset($credentials['access_token']) && !$this->isValidToken()) {
            $this->renewToken();
        }
        $this->accessToken = $credentials['access_token'];
    }

    /**
     * @throws Exception
     */
    public static function isValidToken(): bool
    {
        $credentials = maybe_unserialize(get_option(CA_CREDENTIALS));
        if (!isset($credentials['access_token']) || !isset($credentials['expire'])) {
            return false;
        }
        $now = new DateTime('now');
        $expires = new DateTime($credentials['expire']);
        if ($expires < $now) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function renewToken(): bool
    {
        $response = $this->login();
        $credentials = Utils::getCredentials();
        if ($response && isset($response['token'])) {
            $credentials["access_token"] = $response["token"];
            $credentials["expire"] = $response["expire"];
            $data = maybe_serialize($credentials);
            update_option(CA_CREDENTIALS, $data);
            return true;
        }
        return false;
    }

    public function login()
    {
        $headers = $this->setHeaders([
            "Authorization" => "Basic " . $this->authHash
        ]);

        $request = wp_remote_post(
            $this->url . '/token',
            [
                'method' => 'POST',
                'headers' => $headers,
            ]
        );

        $response = json_decode(wp_remote_retrieve_body($request), true);
        if (wp_remote_retrieve_response_code($request) != 200) {
            return $response;
        }
        return $response;
    }

    public function setHeaders($headers = [])
    {
        $defaultHeaders = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "Connection" => "keep-alive",
        ];
        return array_merge($defaultHeaders, $headers);
    }

    public function registerOrder($orderId)
    {
        $order = wc_get_order($orderId);
        $shippingItems = $order->get_items('shipping');
        $shippingMethod = reset($shippingItems);
        $ca_data = $order->get_meta('ca_data');
        $orderData = $order->get_data();
        $shippingData = $orderData['shipping'];
        $billing = $orderData['billing'];
        $branchId = $order->get_meta(CA_CHOSEN_BRANCH);
        $shippingMethods = $order->get_shipping_methods();
        $chosenShippingMethod = reset($shippingMethods);
        $productType = $chosenShippingMethod->get_meta(CA_CHOSEN_PRODUCT_TYPE);
        $items = $order->get_items();
        $shippingMethod = $order->get_shipping_method();
        
        // Obtener y concatenar los datos de dirección
        $streetName = $shippingData['address_1']; // Calle
        $altura = $shippingData['address_2'] ? $shippingData['address_2'] : '0'; // Altura, Piso y Departamento
    
        $fullAddress = trim("{$streetName} {$altura}");
    
        $altura = '0';
    
        $isBranch = stripos($shippingMethod, BRANCH);
        if (!is_array($ca_data)) {
            $ca_data = [];
        }
        $settings = array_merge(Utils::getSettings(), $ca_data);
    
        $recipient = [
            "name" => $billing['first_name'] . ' ' . $billing['last_name'],
            "email" => $billing['email'],
            "phone" => $shippingData['phone'],
            "cellPhone" => $shippingData['phone'],
        ];
    
        $weight = 0;
        $height = 0;
        $restDimensions = [];
    
        foreach ($items as $item) {
            $product = $item->get_product();
            $quantity = $item->get_quantity();
    
            $productWeight = floatval($product->get_weight()) * 1000;
            $dimensions = [
                floatval($product->get_length()),
                floatval($product->get_width()),
                floatval($product->get_height())
            ];
            $dimensions = array_map('ceil', $dimensions);
            sort($dimensions);
    
            $weight += $productWeight * $quantity;
            $height += $dimensions[0] * $quantity;
    
            for ($i = 0; $i < $quantity; $i++) {
                $restDimensions[] = [$dimensions[1], $dimensions[2]];
            }
        }
    
        $widths = array_column($restDimensions, 0);
        $lengths = array_column($restDimensions, 1);
    
        $width = max($widths);
        $length = max($lengths);
    
        $declaredValueTotal = array_reduce($items, function ($carry, $item) {
            $carry += (float)$item->get_total();
            return $carry;
        });
    
        $shipping = [
            "deliveryType" => $isBranch ? "S" : "D",
            "productType" => $productType,
            "declaredValue" => $declaredValueTotal,
            "weight" => $weight,
            "height" => $height,
            "length" => $length,
            "width" => $width,
            "agency" => "$branchId",
            "address" => [
                "streetName" => $fullAddress,  // Usar la dirección concatenada
                "streetNumber" => $altura,     // Altura en 0 como requerido
                "floor" => $shippingData['floor'],
                "apartment" => $shippingData["department"],
                "city" => $shippingData['city'],
                "provinceCode" => $shippingData['state'],
                "postalCode" => Utils::normalizeZipCode($shippingData['postcode'])
            ],
        ];
    
        $sender = [
            "name" => $settings['first_name'] . " " . $settings['last_name'],
            "phone" => Utils::cleanPhone($settings['phone']),
            "cellPhone" => Utils::cleanPhone($settings['cellphone']),
            "email" => $settings['email'],
            "originAddress" => [
                "streetName" => $settings['street_name'],
                "streetNumber" => $settings['street_number'],
                "floor" => $settings['floor'],
                "apartment" => $settings['department'],
                "city" => $settings['city_name'],
                "provinceCode" => $settings['state'],
                "postalCode" => Utils::normalizeZipCode($settings['zip_code'])
            ]
        ];
    
        $extOrderId = $orderId;
        $body = [
            "customerId" => $settings['customer_id'],
            "extOrderId" => strval($extOrderId),
            "orderNumber" => $orderId,
            "sender" => $sender,
            "recipient" => $recipient,
            "shipping" => $shipping
        ];
    
        $headers = $this->setHeaders([
            "Authorization" => "Bearer " . $this->accessToken,
            "Content-Type" => "application/json"
        ]);
    
        $request = wp_remote_post(
            $this->url . '/shipping/import',
            [
                'method' => 'POST',
                'headers' => $headers,
                'body' => json_encode($body),
            ]
        );
    
        $response = json_decode(wp_remote_retrieve_body($request), true);
        $response["status"] = wp_remote_retrieve_response_code($request);
        if ($response && $response["status"] == 200) {
            $response['reference'] = $extOrderId;
            $order->update_meta_data(CA_TRACKING_NUMBER, $extOrderId);
            $order->delete_meta_data(CA_ORDER_API_ERROR);
            $order->save();
        } else {
            $order->update_meta_data(CA_ORDER_API_ERROR, $response['message']);
            $order->save();
            $response['api_error_message'] = $response['message'];
        }
    
        return $response;
    }
    
    

    /**
     * @throws Exception
     */
    public function getRates($postalCode, $deliveryType, $dimensions, $settings = null)
    {
        if (get_option(CA_USE_RATES) == 0) {
            // No use rates
            return $this->emptyRates($deliveryType);
        }
        return $this->getCalculatedRates($postalCode, $deliveryType, $dimensions, $settings);
    }

    private function emptyRates(string $deliveryType): array
    {
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

    public function getCalculatedRates($postalCode, $deliveryType, $dimensions, $settings = null)
    {
        $dimensions = array_map(function ($dimension) {
            return Utils::toInteger($dimension);
        }, $dimensions);


        $settings = array_merge(Utils::getSettings(), $settings);

        if (!isset($this->accessToken)) {
            throw new Exception('access token no válido o indefinido');
        }

        $headers = $this->setHeaders([
            "Authorization" => "Bearer " . $this->accessToken
        ]);

        $body = [
            "customerId" => $settings["customer_id"],
            "postalCodeOrigin" => Utils::normalizeZipCode($settings["zip_code"]),
            "postalCodeDestination" => Utils::normalizeZipCode($postalCode),
            "deliveredType" => $deliveryType,
            "dimensions" => $dimensions
        ];

        $request = wp_safe_remote_post(
            $this->url . '/rates',
            [
                'method' => 'POST',
                'headers' => $headers,
                'body' => json_encode($body)
            ]
        );

        $response = json_decode(wp_remote_retrieve_body($request), true);
        $response['status'] = wp_remote_retrieve_response_code($request);
        $response['message'] = wp_remote_retrieve_response_message($request);
        return $response;
    }

    public function getBranches($query = [])
    {
        $defaultQuery = ["services" => 'pickup_availability'];
        $query = array_merge($defaultQuery, $query);
        $headers = $this->setHeaders([
            "Authorization" => "Bearer " . $this->accessToken
        ]);
        $request = wp_remote_get(
            add_query_arg($query, $this->url . '/agencies'),
            [
                'method' => 'GET',
                'headers' => $headers
            ]
        );
        $response['status'] = wp_remote_retrieve_response_code($request);
        $response['message'] = wp_remote_retrieve_response_message($request);
        $response = json_decode(wp_remote_retrieve_body($request), true);
        return $response;
    }

    public function createAccount($body)
    {
        $headers = $this->setHeaders([
            "Authorization" => "Bearer " . $this->accessToken,
            "Content-Type" => "application/json"
        ]);

        $request = wp_remote_post(
            $this->url . '/register?t=' . time(),
            [
                'method' => 'POST',
                'headers' => $headers,
                'body' => json_encode($body),
            ]
        );
        $response = json_decode(wp_remote_retrieve_body($request), true);
        $statusCode = wp_remote_retrieve_response_code($request);

        return [
            'status' => $statusCode,
            'message' => $response["message"]
        ];
       
    }

    public function userValidate($email, $password)
    {

        $headers = $this->setHeaders([
            "Authorization" => "Bearer " . $this->accessToken
        ]);

        $request = wp_remote_post(
            $this->url . '/users/validate',
            [
                'method' => 'POST',
                'headers' => $headers,
                'body' => json_encode(["email" => $email, "password" => $password])
            ]
        );

        $response = json_decode(wp_remote_retrieve_body($request), true);
        $response["status"] = wp_remote_retrieve_response_code($request);
        $response["message"] = wp_remote_retrieve_response_message($request);
        return $response;
    }
}
