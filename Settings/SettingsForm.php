<?php

/**
 * Correo Argentino Admin Form
 */
class CorreoArgentinoAdminForm
{

    /**
     * Checks system requirements
     *
     * @return Array Fields Settings for CorreArgentino
     */
    public static function get(): array
    {
        return [
            'enabled' => [
                'title' => __('Enable', 'correoargentino'),
                'type' => 'checkbox',
                'default' => 'yes',
            ],
            'user' => [
                'id' => 'wc-correoargentino-user',
                'type' => 'text',
                'title' => __('User', 'correoargentino'),
            ],
            'password' => [
                'id' => 'wc-correoargentino-password',
                'type' => 'password',
                'title' => __('Password', 'correoargentino'),
            ],
            'business_name' => [
                'id' => 'wc-correoargentino-business-name',
                'type' => 'text',
                'title' => __('Business name', 'correoargentino'),
                //'desc_tip' => true,
                'description' => __('You can use a personal or business name.', 'correoargentino'),
            ],
            'email' => [
                'id' => 'wc-correoargentino-email',
                'type' => 'text',
                'title' => __('Email', 'correoargentino'),
                //'desc_tip' => true,
                'description' => __('Enter the following format: correoargentino@correo.com', 'correoargentino'),
            ],
            'state' => [
                'id' => 'wc-correoargentino-state',
                'type' => 'select',
                'title' => __('State', 'correoargentino'),
                'options' => self::getProvinces(),
                'required' => true
            ],
            'city_name' => [
                'id' => 'wc-correoargentino-city-name',
                'type' => 'text',
                'title' => __('City', 'correoargentino')
            ],
            'departament' => [
                'id' => 'wc-correoargentino-departament',
                'type' => 'text',
                'title' => __('Department', 'correoargentino'),
                'maxlength' => 2,
            ],
            'floor' => [
                'id' => 'wc-correoargentino-floor',
                'type' => 'text',
                'title' => __('Floor', 'correoargentino'),
                'maxlength' => 2,
            ],
            'street_name' => [
                'id' => 'wc-correoargentino-street-name',
                'type' => 'text',
                'title' => __('Street', 'correoargentino'),
                'maxlength' => 64,
            ],
            'street_number' => [
                'id' => 'wc-correoargentino-street-number',
                'type' => 'text',
                'title' => __('Street number', 'correoargentino'),
                'maxlength' => 64,
            ],
            'zip_code' => [
                'id' => 'wc-correoargentino-zip-code',
                'type' => 'text',
                'title' => __('Postal code', 'correoargentino'),
                'maxlength' => 8,
                // 'desc_tip' => true,
                'description' => __('Please enter your postal code. You can find it in https://www.correoargentino.com.ar/formularios/cpa', 'correoargentino'),
            ],
            'area_code_cell_phone' => [
                'id' => 'wc-correoargentino-area-code-cell-phone',
                'type' => 'text',
                'title' => __('Cellphone area code', 'correoargentino')
            ],
            'cell_phone_number' => [
                'id' => 'wc-correoargentino-cell-phone-number',
                'type' => 'text',
                'title' => __('Cellphone', 'correoargentino'),
                'maxlength' => 16,
            ],
            'area_code_phone' => [
                'id' => 'wc-correoargentino-area-code-phone',
                'type' => 'text',
                'title' => __('Cellphone area code', 'correoargentino')
            ],
            'phone_number' => [
                'id' => 'wc-correoargentino-phone-number',
                'type' => 'text',
                'title' => __('Cellphone', 'correoargentino'),
                'maxlength' => 16,
            ],
            'observation' => [
                'id' => 'wc-correoargentino-observation',
                'type' => 'textarea',
                'title' => __('Observation', 'correoargentino'),
                'maxlength' => 500,
            ],
        ];


    }

    static function getProvinces(): array
    {
        $provinces = json_decode(file_get_contents(plugin_dir_path(__FILE__) . '../Mock/provinces.json'), 'true');
        $results = [];
        foreach ($provinces["provincias"] as $item) {
            $code = str_replace("AR-", "", $item['iso_id']);
            $results[$code] = $item['iso_nombre'];
        }
        asort($results);
        return $results;

    }
}
