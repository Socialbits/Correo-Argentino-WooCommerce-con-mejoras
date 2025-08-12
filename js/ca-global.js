jQuery(window).on('pageshow', function () {
    jQuery(document.body).trigger('updated_shipping_method');
    jQuery('.correoargentino_province_select').val('NONE').trigger('change');
});
