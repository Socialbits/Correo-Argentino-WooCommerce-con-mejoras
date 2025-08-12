jQuery(document).ready(function($) {
    'use strict';
    
    // Función para actualizar mensajes de envío gratuito
    function updateFreeShippingMessages() {
        var $cartInfo = $('.correoargentino-free-shipping-info');
        var $cartSuccess = $('.correoargentino-free-shipping-success');
        var $checkoutInfo = $('.correoargentino-free-shipping-info.checkout');
        var $checkoutSuccess = $('.correoargentino-free-shipping-success.checkout');
        
        // Solo actualizar si hay mensajes en la página
        if ($cartInfo.length === 0 && $cartSuccess.length === 0 && 
            $checkoutInfo.length === 0 && $checkoutSuccess.length === 0) {
            return;
        }
        
        // Hacer petición AJAX para obtener información actualizada
        $.ajax({
            url: wc_correoargentino_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_free_shipping_info',
                nonce: wc_correoargentino_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.free_shipping_enabled) {
                    var data = response.data;
                    
                    if (data.qualifies) {
                        // Mostrar mensaje de éxito
                        $cartInfo.hide();
                        $checkoutInfo.hide();
                        
                        if ($cartSuccess.length === 0) {
                            $('.woocommerce-cart-form').before(
                                '<div class="woocommerce-message correoargentino-free-shipping-success">' +
                                '<span class="dashicons dashicons-yes-alt"></span> ' +
                                '¡Felicitaciones! Tu pedido califica para envío gratuito con Correo Argentino.' +
                                '</div>'
                            );
                        }
                        
                        if ($checkoutSuccess.length === 0) {
                            $('.woocommerce-checkout').prepend(
                                '<div class="woocommerce-message correoargentino-free-shipping-success checkout">' +
                                '<span class="dashicons dashicons-yes-alt"></span> ' +
                                '¡Tu pedido califica para envío gratuito con Correo Argentino!' +
                                '</div>'
                            );
                        }
                    } else {
                        // Mostrar mensaje informativo
                        $cartSuccess.hide();
                        $checkoutSuccess.hide();
                        
                        var messageText = '¡Agregá productos por ' + data.remaining_formatted + 
                                         ' más y obtené envío gratuito con Correo Argentino!';
                        
                        if ($cartInfo.length === 0) {
                            $('.woocommerce-cart-form').before(
                                '<div class="woocommerce-info correoargentino-free-shipping-info">' +
                                '<span class="dashicons dashicons-info"></span> ' + messageText +
                                '</div>'
                            );
                        } else {
                            $cartInfo.find('span:last').text(messageText);
                        }
                        
                        if ($checkoutInfo.length === 0) {
                            $('.woocommerce-checkout').prepend(
                                '<div class="woocommerce-info correoargentino-free-shipping-info checkout">' +
                                '<span class="dashicons dashicons-info"></span> ' + messageText +
                                '</div>'
                            );
                        } else {
                            $checkoutInfo.find('span:last').text(messageText);
                        }
                    }
                }
            }
        });
    }
    
    // Actualizar mensajes cuando se actualice el carrito
    $(document.body).on('updated_cart_totals', function() {
        setTimeout(updateFreeShippingMessages, 500);
    });
    
    // Actualizar mensajes cuando se actualice el checkout
    $(document.body).on('updated_checkout', function() {
        setTimeout(updateFreeShippingMessages, 500);
    });
    
    // Actualizar mensajes cuando se cambie la cantidad de productos
    $(document.body).on('change', '.qty', function() {
        setTimeout(updateFreeShippingMessages, 1000);
    });
    
    // Actualizar mensajes cuando se elimine un producto
    $(document.body).on('click', '.remove', function() {
        setTimeout(updateFreeShippingMessages, 1000);
    });
    
    // Actualizar mensajes cuando se agregue un producto al carrito
    $(document.body).on('added_to_cart', function() {
        setTimeout(updateFreeShippingMessages, 1000);
    });
});
