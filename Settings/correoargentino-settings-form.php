<?php

/**
 * Correo Argentino Admin Form
 */
class CorreoArgentinoAdminForm
{

    public const REDIRECT_FORM_URL_BASE = '?page=wc-settings&tab=shipping&section=correoargentino_shipping_method&form=';

    /**
     * Checks system requirements
     *
     * @return array
     */
    public static function getLoginPaqArForm(): array
    {
        $credentials = Utils::getCredentials();

        return [
            'form_id' => self::getFormFieldID('wc-correoargentino-form-id', 'paq-ar', 'log-in'),
            'agreement' => [
                'id' => 'wc-correoargentino-agreement',
                'type' => 'text',
                'title' => __('Acuerdo', 'correoargentino') . Utils::REQUIRED_SIGN,
                'default' => isset($credentials['agreement']) ? $credentials['agreement'] : '',
            ],
            'api_key' => [
                'id' => 'wc-correoargentino-apikey',
                'type' => 'textarea',
                'title' => __('Clave API', 'correoargentino') . Utils::REQUIRED_SIGN,
                'default' => isset($credentials['api_key']) ? $credentials['api_key'] : '',
                'description' => __(DOESNT_HAVE_ACCOUNT_YET . ' <a href="https://www.correoargentino.com.ar/" target="_blank" rel="noopener noreferrer">' . CREATE_NEW_ACCOUNT . '</a>', 'correoargentino')
            ],
        ];
    }

    public static function getFormFieldID($formId, $serviceName, $subject)
    {
        return [
            'id' => $formId,
            'custom_attributes' => ['data-form-id' => strtolower($subject) . '-' . strtolower($serviceName)],
            'type' => 'hidden',
            'class' => 'wc-hidden-field',
        ];
    }

    public static function getLoginMiCorreoForm(): array
    {
        $credentials = Utils::getCredentials();

        return [
            'username_login' => [
                'id' => 'wc-correoargentino-username',
                'type' => 'text',
                'title' => __('Usuario', 'correoargentino') . Utils::REQUIRED_SIGN,
                'default' => isset($credentials['username']) ? $credentials['username'] : '',
            ],
            'password_login' => [
                'id' => 'wc-correoargentino-password',
                'type' => 'password',
                'title' => __('Contraseña', 'correoargentino') . Utils::REQUIRED_SIGN,
                'default' => isset($credentials['api_key']) ? $credentials['api_key'] : '',
            ],
        ];
    }

    public static function getUserValidateMiCorreoForm(): array
    {
        $credentials = Utils::getCredentials();

        return [
            'form_id' => self::getFormFieldID('wc-correoargentino-form-id', 'mi-correo', 'log-in'),
            'email' => [
                'id' => 'wc-correoargentino-email',
                'type' => 'text',
                'title' => __('Usuario', 'correoargentino') . Utils::REQUIRED_SIGN,
                'default' => isset($credentials['email']) ? $credentials['email'] : '',
            ],
            'password' => [
                'id' => 'wc-correoargentino-password',
                'type' => 'password',
                'title' => __('Contraseña', 'correoargentino') . Utils::REQUIRED_SIGN,
                'description' => __(DOESNT_HAVE_ACCOUNT_YET . ' <a href="' . self::REDIRECT_FORM_URL_BASE . CA_BUSINESS_MI_CORREO_FORM . '">' . CREATE_NEW_ACCOUNT . '</a>', 'correoargentino')
            ],
            'customer_id' => [
                'id' => 'wc-correoargentino-customer-id',
                'custom_attributes' => ['value' => null],
                'type' => 'hidden',
                'class' => 'wc-hidden-field'
            ],
        ];
    }

    public static function getServiceSelectorForm(): array
    {
        $service_type_options = array_merge(["-1" => "Seleccioná una opción"], Utils::getAvailableServices());
        return [
            'service_type' => [
                'id' => 'wc-correoargentino-service-type',
                'type' => 'select',
                'title' => __('Servicio', 'correoargentino') . Utils::REQUIRED_SIGN,
                'options' => $service_type_options,
                'class' => 'wc-correoargentino-service-type',
                'custom_attributes' => [
                    'data-allow-clear' => 'false',
                    'data-minimum-results-for-search' => 'Infinity',
                ],
            ],
            'use_rates' => [
                'id' => 'wc-correoargentino-use-rates',
                'type' => 'select',
                'title' => __('¿Querés usar el cotizador de Correo Argentino?', 'correoargentino') . Utils::REQUIRED_SIGN,
                'custom_attributes' => [
                    'data-allow-clear' => 'false',
                    'data-minimum-results-for-search' => 'Infinity',
                ],
                'options' => ['-1' => "Seleccioná una opción", 1 => 'Si', 0 => 'No'],
                'class' => 'wc-correoargentino-use-rates',
                'desc_tip' => __('Al integrar el cotizador el cálculo es realizado por la API de Correo Argentino', 'correoargentino')
            ],
            'form_id' => [
                'id' => 'wc-correoargentino-form-id',
                'custom_attributes' => ['data-form-id' => CA_SERVICE_SELECTOR],
                'type' => 'hidden',
                'class' => 'wc-hidden-field'
            ]
        ];
    }

    public static function getMiCorreoBusinessForm(): array
    {
        $settings = Utils::getSettings();
        $serviceType = Utils::getCurrentServiceType();

        return [
            'form_id' => self::getFormFieldID('wc-correoargentino-form-id', 'mi-correo', 'new-account'),
            'document_type' => [
                'id' => 'wc-correoargentino-document-type',
                'type' => 'select',
                'title' => __('Tipo de Documento', 'correoargentino') . Utils::REQUIRED_SIGN,
                'desc_tip' => __('Elegí CUIT si querés operar como Monotributista o Responsable Inscripto.', 'correoargentino'),
                'options' => Utils::getDocumentTypes(),
                'class' => 'documentType'
            ],
            'document_id' => [
                'id' => 'wc-correoargentino-document-id',
                'type' => 'text',
                'title' => __('Número de Documento', 'correoargentino') . Utils::REQUIRED_SIGN,
                'description' => __('Si elegís CUIT solo agregá el mismo sin guiones', 'correoargentino'),
                'class' => 'documentId'
            ],
            'first_name' => [
                'id' => 'wc-correoargentino-first-name',
                'type' => 'text',
                'title' => __('Nombre', 'correoargentino') . Utils::REQUIRED_SIGN,
                'description' => __('Podés ingresar un nombre personal o comercial.', 'correoargentino'),
                'class' => 'firstName'
            ],
            'last_name' => [
                'id' => 'wc-correoargentino-last-name',
                'type' => 'text',
                'title' => __('Apellido', 'correoargentino'),
                'description' => __('Este campo es requerido para consumidor final.', 'correoargentino'),
                'class' => 'lastName'
            ],
            'email' => [
                'id' => 'wc-correoargentino-email',
                'type' => 'text',
                'title' => __('Email', 'correoargentino') . Utils::REQUIRED_SIGN,
                'class' => 'email'
            ],
            'password' => [
                'id' => 'wc-correoargentino-password',
                'type' => 'password',
                'title' => __('Contraseña', 'correoargentino') . Utils::REQUIRED_SIGN,
                'class' => 'password'
            ],
            'street_name' => [
                'id' => 'wc-correoargentino-street-name',
                'type' => 'text',
                'title' => __('Calle', 'correoargentino') . Utils::REQUIRED_SIGN,
                'class' => 'streetName'
            ],
            'street_number' => [
                'id' => 'wc-correoargentino-street-number',
                'type' => 'text',
                'title' => __('Altura', 'correoargentino') . Utils::REQUIRED_SIGN,
                'class' => 'streeNumber'
            ],
            'floor' => [
                'id' => 'wc-correoargentino-floor',
                'type' => 'text',
                'title' => __('Piso', 'correoargentino'),
                'class' => 'floor'
            ],
            'department' => [
                'id' => 'wc-correoargentino-apartment',
                'type' => 'text',
                'title' => __('Departamento', 'correoargentino'),
                'class' => 'department'
            ],
            'city_name' => [
                'id' => 'wc-correoargentino-city',
                'type' => 'text',
                'title' => __('Ciudad', 'correoargentino') . Utils::REQUIRED_SIGN,
                'class' => 'wc-correoargentino-city'
            ],
            'state_code' => [
                'id' => 'wc-correoargentino-state-code',
                'type' => 'select',
                'title' => __('Provincia', 'correoargentino') . Utils::REQUIRED_SIGN,
                'options' => Utils::getProvinces(),
                'class' => 'wc-correoargentino-state-code'
            ],
            'zip_code' => [
                'id' => 'wc-correoargentino-zip-code',
                'type' => 'text',
                'title' => __('Código Postal', 'correoargentino') . Utils::REQUIRED_SIGN,
                'class' => 'postalCode'
            ],
            'phone' => [
                'id' => 'wc-correoargentino-phone',
                'type' => 'text',
                'title' => __('Teléfono', 'correoargentino'),
                'class' => 'phone'
            ],
            'cellphone' => [
                'id' => 'wc-correoargentino-cellphone',
                'type' => 'text',
                'title' => __('Celular', 'correoargentino') . Utils::REQUIRED_SIGN,
                'class' => 'phone'
            ],
            'customer_id' => [
                'id' => 'wc-correoargentino-customer-id',
                'custom_attributes' => ['value' => $settings["customer_id"]],
                'type' => 'hidden',
                'class' => 'wc-hidden-field'
            ],
            'current_service_type' => [
                'id' => 'wc-correoargentino-current-service-type',
                'custom_attributes' => ['data-current-service-type' => $serviceType],
                'type' => 'hidden',
                'class' => 'wc-hidden-field'
            ],
        ];
    }

    private static function getCommonFormFields(): array
    {
        return [
            'business_name' => [
                'id' => 'wc-correoargentino-business-name',
                'type' => 'text',
                'title' => __('Nombre', 'correoargentino') . Utils::REQUIRED_SIGN,
                'description' => __('Podés ingresar un nombre personal o comercial.', 'correoargentino'),
                'class' => 'businessName'
            ],
            'email' => [
                'id' => 'wc-correoargentino-email',
                'type' => 'text',
                'title' => __('Email', 'correoargentino') . Utils::REQUIRED_SIGN,
                'description' => __('Usá el siguiente formato: correoargentino@correo.com', 'correoargentino'),
            ],
            'state' => [
                'id' => 'wc-correoargentino-state',
                'type' => 'select',
                'title' => __('Provincia', 'correoargentino') . Utils::REQUIRED_SIGN,
                'options' => Utils::getProvinces(),
                'class' => 'wc-correoargentino-states'
            ],
            'city_name' => [
                'id' => 'wc-correoargentino-city-name',
                'type' => 'text',
                'title' => __('Ciudad', 'correoargentino') . Utils::REQUIRED_SIGN,
                'class' => 'cityName'
            ],
            'department' => [
                'id' => 'wc-correoargentino-department',
                'type' => 'text',
                'title' => __('Departamento', 'correoargentino'),
                'maxlength' => 3,
                'class' => 'department'
            ],
            'floor' => [
                'id' => 'wc-correoargentino-floor',
                'type' => 'text',
                'title' => __('Piso', 'correoargentino'),
                'class' => 'floor'
            ],
            'street_name' => [
                'id' => 'wc-correoargentino-street-name',
                'type' => 'text',
                'title' => __('Calle', 'correoargentino') . Utils::REQUIRED_SIGN,
                'maxlength' => 32,
                'class' => 'streetName'
            ],
            'street_number' => [
                'id' => 'wc-correoargentino-street-number',
                'type' => 'text',
                'title' => __('Altura', 'correoargentino') . Utils::REQUIRED_SIGN,
                'maxlength' => 10,
                'class' => 'streetNumber'
            ],
            'zip_code' => [
                'id' => 'wc-correoargentino-zip-code',
                'type' => 'text',
                'title' => __('Código Postal', 'correoargentino') . Utils::REQUIRED_SIGN,
                'maxlength' => 10,
                'desc_tip' => __('Usá el siguiente formato: LNNNNLLL o NNNN. ej: 1190 o A1190NNC'),
                'description' => __('Si no conocés tu CP consultá <a href="https://www.correoargentino.com.ar/formularios/cpa">aquí</a>', 'correoargentino'),
                'class' => 'postalCode'
            ],
            'cellphone' => [
                'id' => 'wc-correoargentino-cellphone',
                'type' => 'text',
                'title' => __('Celular', 'correoargentino'),
                'maxlength' => 16,
                'desc_tip' => __('Usá en el siguiente formato: 9999-99999999'),
                'description' => __('Usá el siguiente formato: 9999-99999999'),
                'class' => 'phone'
            ],
            'phone' => [
                'id' => 'wc-correoargentino-phone',
                'type' => 'text',
                'title' => __('Teléfono', 'correoargentino'),
                'maxlength' => 16,
                'desc_tip' => __('Usá en el siguiente formato: 9999-99999999'),
                'description' => __('Usá el siguiente formato: 9999-99999999'),
                'class' => 'phone'
            ]
        ];
    }

    public static function getInstanceBusinessForm(): array
    {
        return array_merge(
            [
                'form_id' => self::getFormFieldID('wc-correoargentino-form-id', 'mi-correo', 'business-details'),
                'title'             => [
                    'title'           => __('Descripción en tienda', 'correoargentino'),
                    'type'            => 'text',
                    'description'     => __('Nombre visualizado por compradores en tienda al seleccionar el método requerido', 'correoargentino'),
                    'default'         => __('Correo Argentino', 'correoargentino'),
                    'desc_tip'        => true,
                    'custom_attributes' => ["readonly" => true],
                ]
            ],
            self::getCommonFormFields(),
            [
                'tipo_servicio'       => [
                    'title'           => 'Tipo de envío',
                    'type'            => 'select',
                    'label'           => 'Tipo de envío',
                    'options'         => [
                        'E' => 'Expreso',
                        'C' => 'Clásico',
                    ]
                ],
                'metodo_envio'       => [
                    'title'           => 'Método de envío',
                    'type'            => 'select',
                    'label'           => 'Método de envío',
                    'options'         => [
                        'S' => 'Sucursal',
                        'D' => 'A Domicilio',
                    ]
                ],

            ],
            [
                'observation' => [
                    'id' => 'wc-correoargentino-observation',
                    'type' => 'textarea',
                    'title' => __('Observación', 'correoargentino'),
                    'description' => ' <div id="the-count"><span id="current">0</span><span id="maximum">/ 150</span></div>',
                    'custom_attributes' => ["maxlength" => 150],
                ],
            ]
        );
    }

    public static function getBusinessForm(): array
    {
        return array_merge(
            [
                'form_id' => self::getFormFieldID('wc-correoargentino-form-id', 'mi-correo', 'business-details'),
            ],
            self::getCommonFormFields(),
            [
                'observation' => [
                    'id' => 'wc-correoargentino-observation',
                    'type' => 'textarea',
                    'title' => __('Observación', 'correoargentino'),
                    'custom_attributes' => ["maxlength" => 150],
                    'error' => __('La observación no puede superar los 150 caracteres', 'correoargentino'),
                ],
            ]
        );
    }
}
