<?php

defined('ABSPATH') || exit;

/**
 * WooCommerce Order Metabox's base Class
 */
class CorreoArgentinoOrder
{
    /**
     * Creates Metabox
     *
     * @return void
     */
    public static function build_admin_menu(...$arg)
    {
        $isConnected = Utils::isConnected();
        $currentServiceType = Utils::getCurrentServiceType();
        $loginForm = $currentServiceType == PAQ_AR ? CA_LOGIN_PAQ_AR_FORM : CA_USER_VALIDATE_MI_CORREO_FORM;
        add_menu_page(
            'Correo Argentino',
            'Correo Argentino',
            'manage_options',
            sanitize_key('correoargentino-orders'),
            [__CLASS__, 'content'],
            plugin_dir_url(__FILE__) . '../img/logo-icon.svg',
            8,
        );
        $loginForm = $isConnected ? $loginForm : 'service-selector';
        $settingsApiConnection = 'admin.php?page=wc-settings&tab=shipping&section=correoargentino_shipping_method&form=' . $loginForm;
        add_submenu_page(
            sanitize_key('correoargentino-orders'),
            'Conexión API',
            'Conexión API',
            'manage_options',
            $settingsApiConnection,
        );
        if ($isConnected) {
            add_submenu_page(
                sanitize_key('correoargentino-orders'),
                'Datos comerciales',
                'Datos comerciales',
                'manage_options',
                'admin.php?page=wc-settings&tab=shipping&section=correoargentino_shipping_method&form=business',
            );
        }
    }

    /**
     * Prints Metabox Contents
     *
     * @param WC_Post $post
     * @param Metabox $metabox
     * @return void
     */
    public static function content()
    {
        $wp_list_table = new CorreoArgentinoOrderTable();
        $wp_list_table->prepare_items();
        $wp_list_table->display();
    }
}
