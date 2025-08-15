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
                            // Buscar contenedores del carrito (clásico y bloques)
                            var $cartContainer = $('.woocommerce-cart-form, .wp-block-woocommerce-cart');
                            if ($cartContainer.length > 0) {
                                $cartContainer.first().before(
                                    '<div class="woocommerce-message correoargentino-free-shipping-success">' +
                                    '<span class="dashicons dashicons-yes-alt"></span> ' +
                                    '¡Felicitaciones! Tu pedido califica para envío gratuito con Correo Argentino.' +
                                    '</div>'
                                );
                            }
                        }
                        
                        if ($checkoutSuccess.length === 0) {
                            // Buscar contenedores del checkout (clásico y bloques)
                            var $checkoutContainer = $('.woocommerce-checkout, .wp-block-woocommerce-checkout');
                            if ($checkoutContainer.length > 0) {
                                $checkoutContainer.first().prepend(
                                    '<div class="woocommerce-message correoargentino-free-shipping-success checkout">' +
                                    '<span class="dashicons dashicons-yes-alt"></span> ' +
                                    '¡Tu pedido califica para envío gratuito con Correo Argentino!' +
                                    '</div>'
                                );
                            }
                        }
                    } else {
                        // Mostrar mensaje informativo
                        $cartSuccess.hide();
                        $checkoutSuccess.hide();
                        
                        var messageText = '¡Agregá productos por ' + data.remaining_formatted + 
                                         ' más y obtené envío gratuito con Correo Argentino!';
                        
                        if ($cartInfo.length === 0) {
                            // Buscar contenedores del carrito (clásico y bloques)
                            var $cartContainer = $('.woocommerce-cart-form, .wp-block-woocommerce-cart');
                            if ($cartContainer.length > 0) {
                                $cartContainer.first().before(
                                    '<div class="woocommerce-info correoargentino-free-shipping-info">' +
                                    '<span class="dashicons dashicons-info"></span> ' + messageText +
                                    '</div>'
                                );
                            }
                        } else {
                            $cartInfo.find('span:last').text(messageText);
                        }
                        
                        if ($checkoutInfo.length === 0) {
                            // Buscar contenedores del checkout (clásico y bloques)
                            var $checkoutContainer = $('.woocommerce-checkout, .wp-block-woocommerce-checkout');
                            if ($checkoutContainer.length > 0) {
                                $checkoutContainer.first().prepend(
                                    '<div class="woocommerce-info correoargentino-free-shipping-info checkout">' +
                                    '<span class="dashicons dashicons-info"></span> ' + messageText +
                                    '</div>'
                                );
                            }
                        } else {
                            $checkoutInfo.find('span:last').text(messageText);
                        }
                    }
                }
            }
        });
    }
    
    // Función para actualizar mensajes en bloques de WooCommerce
    function updateFreeShippingMessagesBlocks() {
        // Buscar contenedores de bloques
        var $blocksCart = $('.wp-block-woocommerce-cart');
        var $blocksCheckout = $('.wp-block-woocommerce-checkout');
        
        if ($blocksCart.length > 0 || $blocksCheckout.length > 0) {
            updateFreeShippingMessages();
        }
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
    
    // Hooks específicos para WooCommerce Blocks
    $(document.body).on('wc-blocks-cart-update', function() {
        setTimeout(updateFreeShippingMessagesBlocks, 500);
    });
    
    $(document.body).on('wc-blocks-checkout-update', function() {
        setTimeout(updateFreeShippingMessagesBlocks, 500);
    });
    
    // Observar cambios en el DOM para bloques de WooCommerce
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    var $blocksCart = $('.wp-block-woocommerce-cart');
                    var $blocksCheckout = $('.wp-block-woocommerce-checkout');
                    
                    if ($blocksCart.length > 0 || $blocksCheckout.length > 0) {
                        // Si se detectan bloques, actualizar mensajes
                        setTimeout(updateFreeShippingMessagesBlocks, 1000);
                    }
                }
            });
        });
        
        // Observar cambios en el body
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Actualización inicial para bloques
    setTimeout(function() {
        updateFreeShippingMessagesBlocks();
    }, 2000);
});
