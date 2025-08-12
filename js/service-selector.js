jQuery(document).ready(function ($) {
    $("#woocommerce_correoargentino_shipping_method_use_rates").select2()

    $("#woocommerce_correoargentino_shipping_method_service_type").select2().on('select2:select', function (e) {
        const chosen_service = $('#woocommerce_correoargentino_shipping_method_service_type').val()
        console.log(chosen_service)
        if (chosen_service === 'miCorreo' || chosen_service == 0) {
            $('#mainform > table > tbody > tr:nth-child(2)').show()
        } else {
            $('#mainform > table > tbody > tr:nth-child(2)').hide()
        }

        if (chosen_service !== 'miCorreo') {
            $("#woocommerce_correoargentino_shipping_method_use_rates").val(-1)
            $("#woocommerce_correoargentino_shipping_method_use_rates").trigger('change')
        }


        // Call the enableSaveButton function to enable or disable the save button
        enableSaveButton()
    })

    // Listen for changes on both select fields
    $("#woocommerce_correoargentino_shipping_method_use_rates, #woocommerce_correoargentino_shipping_method_service_type").on('change', function () {
        enableSaveButton()
    })

    // Disable the save button by default on load
    enableSaveButton()

    function enableSaveButton() {
        var useRatesValue = $("#woocommerce_correoargentino_shipping_method_use_rates").val()
        var serviceTypeValue = $("#woocommerce_correoargentino_shipping_method_service_type").val()

        var shouldEnable = (
            serviceTypeValue != 0 &&
            (
                serviceTypeValue !== 'miCorreo' ||
                serviceTypeValue === 'miCorreo' && useRatesValue !== '-1'
            )
        )

        $('.woocommerce-save-button').attr('disabled', !shouldEnable)

        return shouldEnable
    }
});

