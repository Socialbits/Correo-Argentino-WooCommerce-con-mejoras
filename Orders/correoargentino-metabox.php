<?php

defined('ABSPATH') || exit;


class CorreoArgentinoMetabox
{
    public static function create($arg)
    {

        $order_types = wc_get_order_types('order-meta-boxes');
        $order_types[] = 'woocommerce_page_wc-orders';
        foreach ($order_types as $type) {
            add_meta_box(
                'correoargentino_metabox',
                __('Correo Argentino', 'correoargentino'),
                array(__CLASS__, 'content'),
                $type,
                'side',
                'high'
            );
        }

    }


    public static function content($post, $metabox)
    {
        $order = wc_get_order($post->ID);
        $currentServiceType = Utils::getCurrentServiceType();
        $chosenServiceType = $order->get_meta(CA_CHOSEN_SERVICE_TYPE);
        $orderServiceType = Utils::getOrderServiceType($post->ID);

        if (empty($order)) {
            return false;
        }

        $shippingMethods = $order->get_shipping_methods();

        if (empty($shippingMethods)) {
            echo esc_html__('The order does not have Correo Argentino as the shipping method', 'correoargentino');
            return true;
        }

        $shippingMethod = reset($shippingMethods);

        if ($shippingMethod->get_method_id() !== 'correoargentino_shipping_method') {
            echo esc_html__('The order does not have Correo Argentino as the shipping method.', 'correoargentino');
            return true;
        }

        $tracking = $order->get_meta(CA_TRACKING_NUMBER);

        if (empty($tracking)) {
            echo $orderServiceType === PAQ_AR
                ? esc_html__('La orden no ha sido preimpuesta aún.', 'correoargentino')
                : esc_html__('La orden no ha sido importada aún.', 'correoargentino');
        }

        $adminUrl = get_admin_url();
        $label = $orderServiceType === PAQ_AR ? esc_html__("Tracking", 'correoargentino') : esc_html__('Referencia', 'correoargentino');
        $actionUrl = $adminUrl . 'admin.php?id=' . $post->ID . '&page=correoargentino-orders&action=';

        $isBranch = $order->get_meta(CA_CHOSEN_BRANCH) !== '';
        if ($isBranch) {
            echo '<style>.order_data_column:nth-child(3) h3 a { display: none; }</style>';
        }

        echo "<div class=''>";

        if (!empty($tracking)) {
            echo "<p>$label: ";
            if ($orderServiceType === PAQ_AR) {
                $tracking = '<a href="' . CA_TRACKING_URL . $tracking . '" target="_blank" rel="noopener noreferrer">' . $tracking . '</a>';
            }
            echo $tracking;
            echo "</p>";
        }
        $buttons = "<p>";
        $statusStartsInCancel = CorreoArgentinoOrderTable::statusStartsIn($order->get_status(), 'cancel');
        $statusStartsInCompleted = CorreoArgentinoOrderTable::statusStartsIn($order->get_status(), 'complet');


        if ($statusStartsInCompleted && !$statusStartsInCancel && !empty($tracking) && $orderServiceType == PAQ_AR) {
            $buttons .= "<a class='button button-primary button-block' href='" . $actionUrl . "label' >" . esc_html__("Ver Rótulo", 'correoargentino') . "</a>&nbsp;";
            $buttons .= "<a class='button' href='" . $actionUrl . "cancel' >" . esc_html__("Cancelar", 'correoargentino') . "</a>";
        }
        $buttons .= "</p>";
        echo $buttons;

        echo "</div>";
    }

}