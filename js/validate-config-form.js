const currentServiceType = jQuery('#woocommerce_correoargentino_current_service_type').data('current-service-type');
const currentFormId = jQuery('#woocommerce_correoargentino_form_id').data('form-id');
const MI_CORREO = "miCorreo";
const PAQ_AR = "paq.ar";
const BUSINESS_DETAILS_FORM_ID = 'business-details-mi-correo';

console.log(`Current Service Type: ${currentServiceType}`);
console.log(`Current Form ID: ${currentFormId}`);
jQuery.validator.methods.email = function (value, element) {
    return (
        this.optional(element) || /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/g.test(value)
    );
};
jQuery("#mainform").validate({
    messages: {
        woocommerce_correoargentino_document_id: currentServiceType === MI_CORREO ? {
            required: "Este campo es requerido",
            minLength: "Por favor ingresá al menos 8 dígitos"
        } : null,
        woocommerce_correoargentino_phone: currentServiceType === MI_CORREO ? "Este campo es requerido" : null,
        woocommerce_correoargentino_cellphone: currentServiceType === MI_CORREO ? "Este campo es requerido" : null,
        woocommerce_correoargentino_email: "Este campo es requerido",
        woocommerce_correoargentino_password: {
            required: "Este campo es requerido",
            minlength: jQuery.validator.format("Por favor ingresá al menos 6 caracteres."),
            maxlength: jQuery.validator.format("Se permite un máximo de 20 caracteres."),
        },
        woocommerce_correoargentino_agreement: "Este campo es requerido",
        woocommerce_correoargentino_api_key: "Este campo es requerido",
        woocommerce_correoargentino_zip_code: {
            required: "Este campo es requerido",
            minlength: jQuery.validator.format("Por favor ingresá un código postal válido."),
            maxlength: jQuery.validator.format("Se permite un máximo de 8 caracteres."),
        },
        woocommerce_correoargentino_business_name: {
            required: "Este campo es requerido",
        },
        woocommerce_correoargentino_first_name: {
            required: "Este campo es requerido",
        },
        woocommerce_correoargentino_department: {
            minlength: jQuery.validator.format("Ingresá al menos un número o letra"),
        },
        woocommerce_correoargentino_state: "Este campo es requerido",
        woocommerce_correoargentino_city_name: {
            required: "Este campo es requerido",
            minlength: jQuery.validator.format("El campo Ciudad debe poseer al menos 4 letras"),
        },
        woocommerce_correoargentino_street_name: "Este campo es requerido",
        woocommerce_correoargentino_street_number: "Este campo es requerido",
        woocommerce_correoargentino_observation: {
            minlength: jQuery.validator.format("Si querés dejar una observación ingresá al menos 18 caracteres"),
            maxlength: jQuery.validator.format("Se permite un máximo de 150 caracteres."),
        },
    },
    rules: {
        woocommerce_correoargentino_business_name: {
            required: true,
        },
        woocommerce_correoargentino_city_name: {
            required: true,
            minlength: 4,
            maxlength: 36,
        },
        woocommerce_correoargentino_department: {
            required: false,
            minlength: 1,
            maxlength: 3,
        },
        woocommerce_correoargentino_floor: {
            required: false,
            minlength: 1,
            maxlength: 4,
        },
        woocommerce_correoargentino_zip_code: {
            required: true,
            minlength: 4,
            maxlength: 8,
        },
        woocommerce_correoargentino_street_number: {
            minlength: 1,
            maxlength: 10,
        },
        woocommerce_correoargentino_observation: {
            required: false,
            minlength: 18,
            maxlength: 150,
        },
        woocommerce_correoargentino_email: {
            required: true,
            email: true
        },
        woocommerce_correoargentino_password: {
            required: true,
            minlength: 6,
            maxlength: 20,
        },
    },
    submitHandler: function (form) {
        form.submit();
    },
    onFocusOut: function (element) {
        if (
            !this.checkable(element) &&
            (element.name in this.submitted || !this.optional(element))
        ) {
            this.element(element);
        }
    },
});

jQuery(document).ready(function (event) {
    const currentURL = window.location.href;
    const formAction = currentURL + (currentURL.includes('?') ? '&' : '?') + 'formSubmitted=true';

    // Set the updated action
    jQuery('#mainform').attr('action', formAction);

    function clearFields() {
        const fields = [
            'document_id',
            'first_name',
            'last_name',
            'email',
            'password',
            'street_name',
            'street_number',
            'floor',
            'department',
            'city_name',
            'zip_code',
            'phone',
            'cellphone'
        ];

        jQuery.each(fields, function (index, value) {
            jQuery('#woocommerce_correoargentino_' + value).val('');
        });
    }

    function resetFormExceptFields() {
        const fieldsToKeep = [
            'document_id',
            'first_name',
            'last_name',
            'email',
            'password',
            'street_name',
            'street_number',
            'floor',
            'department',
            'city_name',
            'zip_code',
            'phone',
            'cellphone'
        ];

        // Get the form element using jQuery
        const form = jQuery('#mainform');

        // Store the values of fields to keep
        const valuesToKeep = {};
        jQuery.each(fieldsToKeep, function (index, value) {
            valuesToKeep[value] = form.find('#woocommerce_correoargentino_' + value).val();
        });

        // Reset the form
        form.trigger('reset');

        // Set the values back to the fields to keep
        jQuery.each(fieldsToKeep, function (index, value) {
            form.find('#woocommerce_correoargentino_' + value).val(valuesToKeep[value]);
        });
    }

    const queryParams = new URLSearchParams(window.location.search);
    const formSubmitted = queryParams.get('formSubmitted');

    if (formSubmitted === 'true') {
        console.log('Page loaded after form submission.');
        resetFormExceptFields();
    } else {
        console.log('Page loaded without form submission.');
        clearFields();
    }


    jQuery(".businessName").mask("XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", {
        translation: {
            X: {
                pattern: /^[a-zA-ZÀ-ÿ\d\s]+$/,
            },
        },
    });

    jQuery(".cityName").mask("XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", {
        translation: {
            X: {
                pattern: /^[a-zA-ZÀ-ÿ\d\s]+$/,
            },
        },
    });

    jQuery(".streetName").mask("XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", {
        translation: {
            X: {
                pattern: /^[a-zA-ZÀ-ÿ\d\s]+$/,
            },
        },
    });

    jQuery(".department").mask("ZZ", {
        translation: {
            Z: {
                pattern: /^[a-zA-Z\d]+$/,
            },
        },
    });
    jQuery(".floor").mask("TTTT", {
        translation: {
            T: {
                pattern: /^[\w\d\s]+$/,
            },
        },
    });
    jQuery(".phone").mask("99ZZ-99999999", {
        translation: {
            Z: {
                pattern: /[0-9]/,
                optional: true,
            },
        },
    });
    // Rule /^([A-HJ-TP-Z]{1}\d{4}[A-Z]{3}|[a-z]{1}\d{4}[a-hj-tp-z]{3})$/
    jQuery(".postalCode").mask("X9999YYY", {
        translation: {
            X: {
                pattern: /[A-HJ-TP-Z]|[a-hj-tp-z]/gm,
                optional: true,
            },
            Y: {
                pattern: /[A-Z]|[a-z]/gm,
                optional: true,
            },
        },
    });

    jQuery(".streetNumber").mask("9999999999");
    jQuery(".documentId").mask("99999999999");
});

jQuery(document).ready(function () {
    if (currentFormId !== BUSINESS_DETAILS_FORM_ID) return;

    const elm = "#woocommerce_correoargentino_observation";

    function count() {
        const characterCount = jQuery(elm).val().length;
        const current = jQuery("#current");
        current.text(characterCount);
    }

    count();
    jQuery(elm).keyup(function () {
        count();
    });
});

