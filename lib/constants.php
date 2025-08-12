<?php
const CA_PLUGIN_ID = 'correoargentino_shipping_method';
const CA_IS_BRANCH = '_is_branch';
const CA_PLUGIN_NAMESPACE = 'woocommerce_' . CA_PLUGIN_ID;
const CA_IS_FIRST_TIME = CA_PLUGIN_NAMESPACE . '_is_first_time';
const CA_SETTINGS = CA_PLUGIN_NAMESPACE . '_settings';
const CA_SERVICE_TYPE = CA_PLUGIN_NAMESPACE . '_service_type';
const CA_USE_RATES = CA_PLUGIN_NAMESPACE . '_use_rates';
const CA_IS_CONNECTED = CA_PLUGIN_NAMESPACE . '_is_connected';
const CA_BUSINESS_DETAILS_FILLED = CA_PLUGIN_NAMESPACE . '_business_details_filled';
const CA_AGREEMENT_FIELD = CA_PLUGIN_NAMESPACE . '_agreement';
const CA_APIKEY_FIELD = CA_PLUGIN_NAMESPACE . '_api_key';
const CA_SANDBOX_FIELD = CA_PLUGIN_NAMESPACE . '_sandbox';
const CA_SERVICE_TYPE_FIELD = CA_PLUGIN_NAMESPACE . '_service_type';
const CA_CREDENTIALS = CA_PLUGIN_NAMESPACE . '_credentials';

// Chosen Address Values
const CA_CHOSEN_BRANCH = '_correoargentino_chosen_branch';
const CA_CHOSEN_PROVINCE_CODE = '_correoargentino_chosen_province_code';
const CA_CHOSEN_BRANCH_NAME = '_correoargentino_chosen_branch_name';
const CA_CHOSEN_BRANCH_STREET_NAME = '_correoargentino_chosen_branch_street_name';
const CA_CHOSEN_BRANCH_STREET_NUMBER = '_correoargentino_chosen_branch_street_number';
const CA_CHOSEN_BRANCH_ZIP_CODE = '_correoargentino_chosen_branch_zip_code';
const CA_CHOSEN_BRANCH_CITY = '_correoargentino_chosen_branch_city';
const CA_CHOSEN_BRANCH_STATE = '_correoargentino_chosen_branch_state';
const CA_CHOSEN_PRODUCT_TYPE = '_correoargentino_chosen_product_type';
const CA_CHOSEN_SERVICE_TYPE = '_correoargentino_chosen_service_type';

// Previous Address Values
const CA_CUSTOMER_SHIPPING_CITY = '_correoargentino_customer_shipping_city';
const CA_CUSTOMER_SHIPPING_STATE = '_correoargentino_customer_shipping_state';
const CA_CUSTOMER_SHIPPING_POSTCODE = '_correoargentino_customer_shipping_postcode';
const CA_CUSTOMER_SHIPPING_COMPANY = '_correoargentino_customer_shipping_company';
const CA_CUSTOMER_SHIPPING_ADDRESS_1 = '_correoargentino_customer_shipping_address_1';
const CA_CUSTOMER_SHIPPING_ADDRESS_2 = '_correoargentino_customer_shipping_address_2';

const CA_TRACKING_NUMBER = '_correoargentino_tracking_number';
const CA_ORDER_API_ERROR = '_correoargentino_order_api_error';

const CA_TRACKING_URL = 'https://www.correoargentino.com.ar/formularios/e-commerce?id=';
const CA_PAQ_AR_API_URL = 'https://api.correoargentino.com.ar/paqar/v1';
const CA_PAQ_AR_API_SANDBOX_URL = 'https://apitest.correoargentino.com.ar/paqar/v1';
const CA_MI_CORREO_API_URL = 'https://api.correoargentino.com.ar/micorreo/v1';
const CA_MI_CORREO_API_SANDBOX_URL = 'https://apitest.correoargentino.com.ar/micorreo/v1';
const CA_BUSINESS_DETAILS_FORM_URL = 'admin.php?page=wc-settings&tab=shipping&section=correoargentino_shipping_method&form=business';

const CA_USERNAME_MI_CORREO = 'WOOCOMMERCE';
const CA_PASSWORD_MI_CORREO = 'Paneles55+';
const CA_USER_VALIDATE_MI_CORREO_FORM = 'user-validate-mi-correo-form';
const CA_LOGIN_MI_CORREO_FORM = 'login-mi-correo-form';
const CA_BUSINESS_MI_CORREO_FORM = 'business-mi-correo-form';
const CA_BUSINESS_DETAILS_FORM = 'business';
const CA_LOGIN_PAQ_AR_FORM = 'login-paq-ar-form';
const CA_ERROR_ON_CONNECTING_TO_MI_CORREO = "Ha ocurrido al conectarse a MiCorreo";
const CA_SUCCESS_ON_CONNECTING_TO_MI_CORREO = "Listo, ya podés operar en MiCorreo";
const CA_ERROR_ON_CONNECTING_TO_PAQ_AR = "Ha ocurrido al conectarse a Paq.Ar";
const CA_SUCCESS_ON_CONNECTING_TO_PAQ_AR = "Listo, ya podés operar en Paq.Ar";
const CA_SUCCESS_ON_REGISTER_TO_MI_CORREO = "Listo, la cuenta ha sido creada";
const CA_ERROR_ON_REGISTER_TO_MI_CORREO = "Ha ocurrido un error al registrarse a MiCorreo";
const CA_NO_ACCESS_TOKEN_MESSAGE = 'Necesitas actualizar los datos de tu sesión';
const CA_BUSINESS_DETAILS_NOT_FILLED_MESSAGE = "Hola, para poder operar con <b>Correo Argentino</b> es necesario que configures todos los datos del comercio requeridos <a href='" . CA_BUSINESS_DETAILS_FORM_URL . "'>aquí</a>";
const CA_CHOOSE_DESTINATION_AGENCY = 'Seleccioná la sucursal destino';
// Business Form fields
const CA_DOCUMENT_TYPE_FIELD = CA_PLUGIN_NAMESPACE . '_document_type';
const CA_DOCUMENT_ID_FIELD = CA_PLUGIN_NAMESPACE . '_document_id';
const CA_FIRST_NAME_FIELD = CA_PLUGIN_NAMESPACE . '_first_name';
const CA_LAST_NAME_FIELD = CA_PLUGIN_NAMESPACE . '_last_name';
const CA_EMAIL_FIELD = CA_PLUGIN_NAMESPACE . '_email';
const CA_PASSWORD_FIELD = CA_PLUGIN_NAMESPACE . '_password';
const CA_STREET_NAME_FIELD = CA_PLUGIN_NAMESPACE . '_street_name';
const CA_STREET_NUMBER_FIELD = CA_PLUGIN_NAMESPACE . '_street_number';
const CA_FLOOR_FIELD = CA_PLUGIN_NAMESPACE . '_floor';
const CA_DEPARTMENT_FIELD = CA_PLUGIN_NAMESPACE . '_department';
const CA_CITY_NAME_FIELD = CA_PLUGIN_NAMESPACE . '_city_name';
const CA_STATE_CODE_FIELD = CA_PLUGIN_NAMESPACE . '_state_code';
const CA_ZIP_CODE_FIELD = CA_PLUGIN_NAMESPACE . '_zip_code';
const CA_PHONE_FIELD = CA_PLUGIN_NAMESPACE . '_phone';
const CA_CELLPHONE_FIELD = CA_PLUGIN_NAMESPACE . '_cellphone';
const CA_CUSTOMER_ID_FIELD = CA_PLUGIN_NAMESPACE . '_customer_id';
const CA_BRANCH_ZIP_CODE_HIDDEN_FIELD = CA_PLUGIN_NAMESPACE . "_branch_zip_code_hidden";
const CA_FORM_SUBMIT = 'save';
const CA_SERVICE_SELECTOR = 'service-selector';
const CA_PLEASE_SELECT_A_DESTINATION_PROVINCE = 'Seleccioná la provincia destino';
const CA_MAX_WEIGHT_PAQ_AR = 25000; // 25kg
const CA_MAX_LENGTH_PAQ_AR = 250;   // 250cm
const CA_MAX_WEIGHT_MI_CORREO = 50000; // 25kg
const CA_MAX_LENGTH_MI_CORREO = 300;   // 250cm
const CA_FREE_SHIPPING_THRESHOLD = CA_PLUGIN_NAMESPACE . '_free_shipping_threshold';
// Isolated strings
const STREET_NUMBER = 'Altura, piso y departamento';
const PAQ_AR = "paq.ar";
const ORDER = "Pedido";
const TRACKING = 'Tracking';
const SHIPPING_TYPE = 'Tipo de envío';
const STATUS = 'Estatus';
const DATE = 'Fecha';
const MI_CORREO = "miCorreo";
const BRANCH = 'Sucursal';
const AGENCY = 'agency';
const HOME = 'Domicilio';
const SHIPPING_AGENCY = 'Sucursal';
const HOME_DELIVERY = 'homeDelivery';
const NONE = 'NONE';
const CREATE_NEW_ACCOUNT = 'Creá una nueva';
const DOESNT_HAVE_ACCOUNT_YET = '¿No tenés una aún?';
const MIN_PRODUCT_WEIGHT = 1;
const MIN_PRODUCT_HEIGHT = 10;
const MIN_PRODUCT_WIDTH = 10;
const MIN_PRODUCT_LENGTH = 10;
const REFERENCE = 'Referencia';
const WC_SHIPPING_DEBUG_MODE = 'woocommerce_shipping_debug_mode';
const WC_SHIPPING_TO_DESTINATION = 'woocommerce_ship_to_destination';
