<?php

class CorreoArgentinoOrderTable extends WP_List_Table
{
    private $ca_orders;

    /**
     *
     */
    function __construct()
    {
        parent::__construct(array(
            'singular' => 'Item',
            'plural'   => 'Items',
            'ajax'     => false,
        ));

        if (isset($_GET['action'])) {
            $action = sanitize_text_field($_GET['action']);
            $id = absint($_GET['id']);

            $actions = [
                'label' => 'label',
                'cancel' => 'cancel',
                'store' => 'store',
            ];

            $method = $actions[$action] ?? 'unknown';

            $this->$method($id);
        }

        $this->render_page_title();
    }


    public function label($order_id)
    {
        $order = wc_get_order($order_id);
        $service = (new CorreoArgentinoServiceFactory())->get();
        $response = $service->label($order->get_meta(CA_TRACKING_NUMBER));
        $filename = $response[0]['filename'];
        $data = $response[0]['fileBase64'];
        if ($response) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-type: application/octet-stream');
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=$filename");
            echo base64_decode($data);
        }
    }

    public function unknown()
    {
        Utils::message('Acción desconocida', 'warning', true, false);
    }

    /**
     *
     */
    public function prepare_items()
    {
        global $wpdb;

        $prefix = $wpdb->prefix;

        $columns = $this->get_columns();
        $hidden = array();
        $this->ca_orders = $this->get_ca_orders();
        $this->_column_headers = array($columns, $hidden);

        // Pagination
        $per_page = 20;
        $current_page = $this->get_pagenum();

        $offset = (($current_page - 1) * $per_page);

        // Prepare SQL query
        $sql = $wpdb->prepare(
            "SELECT * FROM `{$prefix}posts` AS _order " .
                "WHERE post_type LIKE 'shop_order%' AND _order.post_status != 'trash' " .
                "AND EXISTS ( " .
                "    SELECT order_id FROM {$prefix}woocommerce_order_items AS _item " .
                "    WHERE _item.order_item_type = 'shipping' " .
                "    AND EXISTS ( " .
                "        SELECT meta_id FROM {$prefix}woocommerce_order_itemmeta AS _meta " .
                "        WHERE _meta.meta_key = 'method_id' " .
                "        AND _meta.meta_value = %s" .
                "        AND _meta.order_item_id = _item.order_item_id " .
                "    )" .
                "    AND EXISTS ( " .
                "        SELECT meta_id FROM {$prefix}woocommerce_order_itemmeta AS _meta " .
                "        WHERE _meta.meta_key = '_correoargentino_chosen_service_type' " .
                "        AND _meta.meta_value = %s" .
                "        AND _meta.order_item_id = _item.order_item_id " .
                "    )" .
                "    AND _item.order_id = _order.ID " .
                ") ORDER BY _order.ID DESC LIMIT %d, %d",
            array(
                CA_PLUGIN_ID,
                Utils::getCurrentServiceType(),
                $offset,
                $per_page
            )
        );

        // Execute the query
        $keys = $wpdb->get_results($sql, ARRAY_A);

        // If keys found, load additional meta data
        if (!empty($keys)) {
            // Determine existence of tables
            $exists_wc_orders_meta = $wpdb->get_var("SHOW TABLES LIKE '{$prefix}wc_orders_meta'");
            $exists_wc_orders_addresses = $wpdb->get_var("SHOW TABLES LIKE '{$prefix}wc_order_addresses'");

            // Fetch meta data
            $meta_table = $exists_wc_orders_meta ? "{$prefix}wc_orders_meta" : "{$prefix}postmeta";
            $address_table = $exists_wc_orders_addresses ? "{$prefix}wc_order_addresses" : "{$prefix}postmeta";

            $ids = array_column($keys, 'ID');
            $ids_str = implode(",", $ids);

            $customer_sql = "SELECT * FROM $meta_table WHERE ";
            $customer_sql .= $exists_wc_orders_meta ? "order_id" : "post_id";
            $customer_sql .= " IN ($ids_str)";

            $address_sql = "SELECT * FROM $address_table WHERE ";
            $address_sql .= $exists_wc_orders_addresses ? "order_id" : "post_id";
            $address_sql .= " IN ($ids_str)";

            $customers = $wpdb->get_results($customer_sql, ARRAY_A);
            $addresses = $wpdb->get_results($address_sql, ARRAY_A);

            // Fetch WooCommerce order items
            $woo_order_items_sql = "SELECT order_id, order_item_type, order_item_name FROM {$prefix}woocommerce_order_items WHERE order_id IN ($ids_str) AND order_item_type = 'shipping'";
            $wooOrderItems = $wpdb->get_results($woo_order_items_sql, OBJECT_K);

            // Combine data and populate items
            $items = array_map(function ($item) use ($wooOrderItems, $customers, $addresses, $exists_wc_orders_addresses, $exists_wc_orders_meta) {
                $item["woo"] = $wooOrderItems[$item["ID"]] ?? null;

                $meta = [];
                foreach ($customers as $customer) {
                    if (($exists_wc_orders_meta && $customer['order_id'] == $item["ID"]) || (!$exists_wc_orders_meta && $customer['post_id'] == $item["ID"])) {
                        $meta[$customer['meta_key']] = $customer['meta_value'];
                    }
                }

                if ($exists_wc_orders_addresses) {
                    foreach ($addresses as $address) {
                        if ($address['order_id'] == $item["ID"]) {
                            $meta['_billing_first_name'] = $address['first_name'];
                            $meta['_billing_last_name'] = $address['last_name'];
                        }
                    }
                }

                $item['meta'] = $meta;
                return $item;
            }, $keys);

            $this->items = $items;
        }
    }


    /**
     *
     */
    public function get_columns()
    {
        return array(
            'column_cb' => Utils::getCurrentServiceType() !== MI_CORREO ? '' : '<input type="checkbox" name="wc-correoargentino-all-orders" class="wc-correoargentino-all-orders" value="all" />',
            'ID' => ORDER,
            'tracking_number' => Utils::getCurrentServiceType() === PAQ_AR ? TRACKING : REFERENCE,
            'shipping_type' => SHIPPING_TYPE,
            'status' => STATUS,
            'agency_name' => SHIPPING_AGENCY,
            'post_date' => DATE,
        );
    }

    private function get_ca_orders()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = " SELECT * FROM `{$prefix}posts` AS _order WHERE post_type LIKE 'shop_order' AND  _order.post_status != 'trash'" .
            " AND EXISTS( " .
            "     SELECT order_id FROM {$prefix}woocommerce_order_items AS _item" .
            "     WHERE _item.order_item_type = 'shipping' " .
            "     AND EXISTS ( " .
            "     SELECT meta_id FROM {$prefix}woocommerce_order_itemmeta _meta WHERE _meta.meta_key  = 'method_id' " .
            "          AND _meta.meta_value = '" . CA_PLUGIN_ID . "' " .
            "          AND _meta.order_item_id = _item.order_item_id " .
            "      ) AND _item.order_id = _order.id " .
            ") ORDER BY _order.id DESC";

        return $wpdb->get_results(
            $sql,
            ARRAY_A
        );
    }

    /**
     * Store in Correo Argentino
     */
    public function store($order_id)
    {
        $service = (new CorreoArgentinoServiceFactory())->get();
        $response = $service->registerOrder($order_id);
        $serviceType = Utils::getCurrentServiceType();
        $this->responseEffect($response, $serviceType);

        $error_body = isset($response["error_body"]) ? $response["error_body"] : null;

        $error = json_decode($error_body, true);
        $message = $serviceType == PAQ_AR ? "<b>Error " . $error["status"] . "</b> " . $error["message"] : "<b>Error " . $response["status"] . "</b> " . $response["message"];
        Utils::message($message, 'error', true, false);
    }

    public function responseEffect($response, $serviceType)
    {

        if ($serviceType === PAQ_AR) {
            if (!isset($response['error'])) {
                $adminUrl = esc_url(get_admin_url(null, 'admin.php?page=correoargentino-orders'));
                header("Location: $adminUrl");
                die;
            }
        }

        if ($serviceType === MI_CORREO) {
            if (isset($response['status']) && $response["status"] == 200) {
                $adminUrl = esc_url(get_admin_url(null, 'admin.php?page=correoargentino-orders'));
                header("Location: $adminUrl");
                die;
            }
        }
    }

    public function cancel($order_id)
    {

        $confirmation = $this->show_cancel_order_confirmation($order_id);

        if ($confirmation) {
            $order = wc_get_order($order_id);
            $service = (new CorreoArgentinoServiceFactory())->get();
            $response = $service->cancel($order->get_meta(CA_TRACKING_NUMBER));

            if ($response) {
                $order->update_status('cancelled');
                $order->save();
                Utils::message('<p>La orden fue cancelada correctamente.</p>', 'success', true, false);
            }
        }
    }

    private function show_cancel_order_confirmation($order_id)
    {
        if (isset($_POST['cancel_order_confirmation_nonce']) && isset($_POST['confirm_cancel_order']) && wp_verify_nonce($_POST['cancel_order_confirmation_nonce'], 'cancel_order_confirmation_nonce')) {
            return true;
        }
        $message = '<form method="post">' .
            wp_nonce_field('cancel_order_confirmation_nonce', 'cancel_order_confirmation_nonce', true, false) .
            'Vas a cancelar la orden <code>#' . (int)$order_id . '</code>, ¿deseas continuar? <br /><br />' .
            '<a href="' . admin_url('admin.php?page=correoargentino-orders') . '" class="button button-secondary">Descartar</a>&nbsp;' .
            '<input type="submit" name="confirm_cancel_order" class="button button-primary" value="Aceptar">' .
            '</form>';

        Utils::message($message, 'info', true, false);

        return false;
    }

    /**
     *
     * @throws Exception
     */
    function column_default($item, $column_name)
    {
        $trackingUrl = CA_TRACKING_URL;
        switch ($column_name) {
            case 'column_cb':
                return $this->column_cb($item);

            case 'tracking_number':
                $order = wc_get_order($item['ID']);
                $tracking = $order->get_meta(CA_TRACKING_NUMBER);
                if ($tracking) {
                    if (Utils::getCurrentServiceType() === PAQ_AR) {
                        return "<a href='" . $trackingUrl . $tracking . "' target='_blank' rel='noopener noreferrer'>" . $tracking . "</a>";
                    }
                    return $tracking;
                }
                elseif ($order->get_meta(CA_ORDER_API_ERROR)) {
                    return '<label class="error">' . $order->get_meta(CA_ORDER_API_ERROR) . '</label>';
                }
                return $this->placeholder('tracking_number', $item['ID']);

            case 'status':
                if (isset($item['post_status'])) {
                    $order = wc_get_order($item['ID']);
                    $order_status = $order->get_status();
                    $status = esc_html(wc_get_order_status_name($order_status), 'Order status', 'woocommerce');

                    // Encode the status to ensure proper rendering
                    $encoded_status = esc_html($status);

                    // Build the output with proper HTML structure
                    $output = '<mark class="order-status status-' . strtolower($order_status) . ' ph-status-' . esc_attr($item['ID']) . '">';
                    $output .= '<span>' . $encoded_status . '</span>';
                    $output .= '</mark>';
                    return $output;
                }


                return $this->placeholder('status', $item['ID']);

            case 'shipping_type':
                return $item['woo']->order_item_name;

            case 'post_date':
                $timezone = new DateTimeZone("America/Buenos_Aires");
                $date = new DateTime($item['post_date'], $timezone);
                return $date->format("d/m/Y H:i");

            case 'agency_name':
                if (array_key_exists(CA_CHOSEN_BRANCH, $item['meta']) && array_key_exists(CA_CHOSEN_BRANCH_NAME, $item['meta'])) {
                    return ucwords(wc_strtolower($item['meta'][CA_CHOSEN_BRANCH_NAME])) . ' <code>' . $item['meta'][CA_CHOSEN_BRANCH] . '</code>';
                }
                return '';

            default:
                return print_r($item, true);
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    public function column_cb($item): string
    {
        if (Utils::getCurrentServiceType() !== MI_CORREO || array_key_exists(CA_TRACKING_NUMBER, $item['meta'])) return '';

        return sprintf(
            '<input type="checkbox" name="order_id[]" value="%s" />',
            $item['ID']
        );
    }

    public function placeholder($column_name, $id)
    {
        $class_name = "ph-{$column_name}-{$id}";
        return "<span class=\"$class_name\"></span>";
    }

    /**
     *
     */
    public function column_ID($item): string
    {
        $tracking = "";
        $serviceType = Utils::getCurrentServiceType();
        if (array_key_exists(CA_TRACKING_NUMBER, $item['meta'])) {
            $tracking = $item['meta'][CA_TRACKING_NUMBER];
        }

        $id = $item['ID'];
        $page = $_REQUEST['page'];
        $actions = [];

        $order = wc_get_order($id);


        if (empty($tracking) && !self::statusStartsIn($order->get_status(), 'cancel')) {
            $actionName = Utils::getCurrentServiceType() == PAQ_AR ? sprintf('<a href="?%s">Preimponer</a>', http_build_query(['id' => $id, 'page' => $page, 'action' => 'store'])) : sprintf('<a href="?%s">Importar</a>', http_build_query(['id' => $id, 'page' => $page, 'action' => 'store']));
            $actions['store'] = $actionName;
        } else {
            if (!self::statusStartsIn($order->get_status(), 'cancel') && $serviceType == PAQ_AR) {
                $actions['label'] = sprintf('<a href="?%s">Etiqueta</a>', http_build_query(['id' => $id, 'page' => $page, 'action' => 'label']));
                $actions['cancel'] = sprintf('<a href="?%s">Cancelar</a>', http_build_query(['id' => $id, 'page' => $page, 'action' => 'cancel']));
            }
        }

        $customer = '#' . $item['ID'] . ' ' . $item['meta']['_billing_first_name'] . ' ' . $item['meta']['_billing_last_name'];

        return sprintf('%1$s %2$s', $customer, $this->row_actions($actions));
    }

    public static function statusStartsIn($status, $string): bool
    {
        // Convert both strings to lowercase for case-insensitive comparison
        $status = strtolower($status);
        $string = strtolower($string);

        // Check if $string is present inside $status
        return str_contains($status, $string);
    }


    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        return array(
            'ID' => array('ID', false),
            'post_date' => array('post_date', false)
        );
    }

    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    public function column_name($item): string
    {
        // create a nonce
        $delete_nonce = wp_create_nonce('sp_delete_customer');
        $title = '<strong>' . $item['name'] . '</strong>';
        $actions = [
            'delete' => sprintf('<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['ID']), $delete_nonce)
        ];

        return $title . $this->row_actions($actions);
    }

    public function display_tablenav($which)
    {
        if (Utils::getCurrentServiceType() !== MI_CORREO) return;
        if ('top' === $which || 'bottom' === $which) {
?>
            <div class="tablenav top">
                <div class="alignleft actions">
                    <label for="bulk-actions-top" class="screen-reader-text">Acciones en lote</label>
                    <select name="bulk-actions-top" class="bulk-actions-top">
                        <option value="-1">Acciones en lote</option>
                        <option value="importar">Importar</option>
                    </select>
                    <input type="submit" class="button action bulk-actions-button" value="Aplicar">
                </div>
                <?php $this->extra_tablenav($which); ?>
                <?php $this->pagination('top'); ?>
                <br class="clear" />
            </div>
<?php
        }
    }

    public function pagination($which)
    {
        return parent::pagination($which);
    }

    public function render_page_title()
    {
        echo '<h1 class="wp-heading-inline">Órdenes de Correo Argentino</h1>';
    }
}
