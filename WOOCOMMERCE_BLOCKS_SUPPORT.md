# Soporte para WooCommerce Blocks - Envío Gratuito

## 📋 Descripción

Este documento explica cómo funciona el soporte para **WooCommerce Blocks** en la funcionalidad de envío gratuito del plugin de Correo Argentino.

## 🎯 ¿Qué son WooCommerce Blocks?

WooCommerce Blocks es el nuevo sistema de bloques de Gutenberg que reemplaza gradualmente el checkout y carrito clásicos. Los bloques proporcionan:

- **Mejor UX**: Interfaz más moderna y responsive
- **Más Flexibilidad**: Personalización avanzada del diseño
- **Mejor Performance**: Carga más rápida y eficiente
- **Compatibilidad**: Funciona perfectamente en dispositivos móviles

## 🚀 Funcionalidades Implementadas

### ✅ Soporte Completo para Bloques

El plugin ahora soporta **ambos sistemas**:
- **Checkout/Carrito Clásicos**: Funcionalidad original
- **WooCommerce Blocks**: Nuevo soporte implementado

### 📍 Ubicaciones de Mensajes

#### En el Carrito (Bloques)
- **Antes del contenido**: Mensaje principal
- **En el resumen**: Mensaje en la barra lateral
- **Mini carrito**: Mensaje compacto

#### En el Checkout (Bloques)
- **Antes del formulario**: Mensaje principal
- **En el resumen**: Mensaje en la barra lateral
- **Después del header**: Mensaje destacado

## 🔧 Implementación Técnica

### Hooks Utilizados

#### Hooks Principales
```php
// Registro de bloques
add_action('woocommerce_blocks_cart_block_registry', 'add_free_shipping_hooks_blocks_cart');
add_action('woocommerce_blocks_checkout_block_registry', 'add_free_shipping_hooks_blocks_checkout');

// Filtros de contenido
add_filter('woocommerce_blocks_cart_block_content', 'add_free_shipping_message_to_cart_blocks', 10, 1);
add_filter('woocommerce_blocks_checkout_block_content', 'add_free_shipping_message_to_checkout_blocks', 10, 1);
```

#### Hooks Adicionales
```php
// Mini carrito
add_action('woocommerce_mini_cart_contents', 'display_free_shipping_message_mini_cart', 5);

// Resumen del carrito
add_action('woocommerce_cart_collaterals', 'display_free_shipping_message_cart_summary', 5);

// Resumen del checkout
add_action('woocommerce_checkout_order_review', 'display_free_shipping_message_checkout_summary', 5);
```

### JavaScript para Bloques

#### Detección Automática
```javascript
// Buscar contenedores de bloques
var $blocksCart = $('.wp-block-woocommerce-cart');
var $blocksCheckout = $('.wp-block-woocommerce-checkout');

// Actualizar mensajes en bloques
function updateFreeShippingMessagesBlocks() {
    if ($blocksCart.length > 0 || $blocksCheckout.length > 0) {
        updateFreeShippingMessages();
    }
}
```

#### Observador de Cambios
```javascript
// Observar cambios en el DOM para bloques
var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'childList') {
            // Detectar bloques y actualizar mensajes
            setTimeout(updateFreeShippingMessagesBlocks, 1000);
        }
    });
});
```

## 🎨 Estilos CSS

### Clases Específicas para Bloques

#### Carrito (Bloques)
```css
.correoargentino-free-shipping-info.blocks-cart,
.correoargentino-free-shipping-success.blocks-cart {
    margin: 15px 0;
    padding: 12px 15px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}
```

#### Checkout (Bloques)
```css
.correoargentino-free-shipping-info.blocks-checkout,
.correoargentino-free-shipping-success.blocks-checkout {
    margin: 15px 0;
    padding: 12px 15px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}
```

#### Mini Carrito
```css
.correoargentino-free-shipping-info.mini-cart,
.correoargentino-free-shipping-success.mini-cart {
    margin: 10px 0;
    padding: 8px 10px;
    font-size: 12px;
    border-radius: 3px;
}
```

## 📱 Experiencia del Usuario

### En Carrito (Bloques)
1. **Mensaje Principal**: Aparece antes del contenido del carrito
2. **Mensaje en Resumen**: Se muestra en la barra lateral
3. **Actualización Dinámica**: Se actualiza al cambiar cantidades

### En Checkout (Bloques)
1. **Mensaje Principal**: Aparece antes del formulario
2. **Mensaje en Resumen**: Se muestra en la barra lateral
3. **Actualización en Tiempo Real**: Se actualiza al modificar el carrito

### Mini Carrito
1. **Mensaje Compacto**: Versión resumida del mensaje
2. **Diseño Adaptativo**: Se ajusta al tamaño del mini carrito
3. **Información Esencial**: Solo muestra lo más importante

## 🔍 Troubleshooting

### Los Mensajes No Se Muestran en Bloques

#### Verificación Básica
1. **Confirmar Bloques**: Verificar que se estén usando bloques de WooCommerce
2. **Revisar Consola**: Buscar errores JavaScript
3. **Verificar Hooks**: Confirmar que los hooks se estén ejecutando

#### Soluciones Comunes
1. **Limpiar Caché**: Limpiar caché del sitio y navegador
2. **Verificar Tema**: Confirmar que el tema soporte bloques
3. **Revisar Plugins**: Verificar conflictos con otros plugins

### Mensajes Duplicados

#### Causas
- Múltiples hooks ejecutándose
- JavaScript ejecutándose varias veces
- Conflictos con otros plugins

#### Soluciones
- Verificar que no haya hooks duplicados
- Revisar que el JavaScript se ejecute una sola vez
- Usar `remove_action` para hooks conflictivos

## 🚀 Próximas Mejoras

### Funcionalidades Planificadas
- [ ] **Soporte para Mini Cart Block**: Mensajes en el mini carrito de bloques
- [ ] **Soporte para Cart Items Block**: Mensajes en la lista de productos
- [ ] **Soporte para Order Summary Block**: Mensajes en el resumen de la orden
- [ ] **Soporte para Payment Block**: Mensajes en el área de pagos

### Mejoras Técnicas
- [ ] **API REST para Bloques**: Endpoints específicos para bloques
- [ ] **Webhooks para Bloques**: Notificaciones en tiempo real
- [ ] **Cache para Bloques**: Sistema de caché optimizado
- [ ] **Testing para Bloques**: Suite de tests específica

## 📊 Compatibilidad

### Versiones Soportadas
- **WooCommerce**: 5.0+
- **WordPress**: 5.8+
- **PHP**: 7.4+
- **Bloques**: Todos los bloques oficiales de WooCommerce

### Temas Compatibles
- **Temas Clásicos**: 100% compatible
- **Temas de Bloques**: 100% compatible
- **Temas Híbridos**: 100% compatible

### Plugins Compatibles
- **WooCommerce Blocks**: 100% compatible
- **WooCommerce**: 100% compatible
- **Otros Plugins**: Compatibilidad verificada

## 📝 Notas de Desarrollo

### Consideraciones Técnicas
1. **Performance**: Los mensajes se cargan de forma eficiente
2. **Responsive**: Se adaptan a todos los tamaños de pantalla
3. **Accesibilidad**: Cumplen con estándares de accesibilidad
4. **SEO**: No afectan el SEO del sitio

### Mejores Prácticas
1. **Hooks Mínimos**: Solo se usan los hooks necesarios
2. **JavaScript Optimizado**: Código eficiente y sin conflictos
3. **CSS Modular**: Estilos organizados y mantenibles
4. **Documentación Clara**: Código bien documentado

---

## 🤝 Soporte

Para soporte técnico o reportar problemas con WooCommerce Blocks:
- **Issues**: Crear un issue en GitHub
- **Documentación**: Consultar este documento
- **Comunidad**: Preguntar en las discusiones

---

*Documento actualizado: Diciembre 2024*
