<?php

defined('ABSPATH') || exit;

/**
 * WooCommerce Order Metabox's base Class
 */
class CorreoArgentinoLabel
{
    /**
     *
     * @return void
     */
    public static function create($arg)
    {
        register_rest_route(
            'correoagentino',
            '/labels',
            array(
                'methods' =>  'POST',
                'callback' => [__CLASS__, 'content'],
                'permission_callback' => '__return_true'
            )
        );
    }


    /**
     * @param ...$arg
     */
    public static function content(...$arg)
    {
        /* if (!isset($_GET['tracking'])) {
            die('ERROR');
        }

        $tracking = $_GET['tracking'];
        $service = CorreoArgentinoService::getInstanceWithLogin();
        $label = $service->label($tracking);*/
    }
}
