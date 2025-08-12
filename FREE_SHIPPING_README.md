# Funcionalidad de Envío Gratuito - Plugin Correo Argentino

## Descripción

Esta funcionalidad permite configurar un umbral de monto mínimo en el carrito para que el envío sea gratuito cuando se use el método de envío de Correo Argentino.

## Características

- **Configuración flexible**: Define el monto mínimo para envío gratuito
- **Mensajes informativos**: Muestra cuánto falta para calificar al envío gratuito
- **Actualización dinámica**: Los mensajes se actualizan automáticamente al cambiar el carrito
- **Visibilidad completa**: Las opciones de envío siguen siendo visibles pero con costo 0
- **Información en admin**: Muestra detalles del envío gratuito en las órdenes

## Configuración

### 1. Acceder a la Configuración

1. Ve a **WooCommerce > Configuración > Envíos**
2. Selecciona la zona de envío donde tienes configurado Correo Argentino
3. Haz clic en **Correo Argentino** para editar

### 2. Configurar el Umbral

1. En el campo **"Umbral para envío gratuito"** ingresa el monto mínimo
2. Ejemplo: Si ingresas `5000`, el envío será gratuito para pedidos de $5000 o más
3. Deja vacío para deshabilitar la funcionalidad
4. Guarda los cambios

## Funcionamiento

### Para el Cliente

- **En el Carrito**: Ve mensajes informativos sobre el envío gratuito
- **En el Checkout**: Ve claramente si califica para envío gratuito
- **Opciones Visibles**: Todas las opciones de envío siguen disponibles
- **Costo 0**: El envío se muestra como gratuito cuando corresponde

### Mensajes Mostrados

- **Antes de calificar**: "¡Agregá productos por $X más y obtené envío gratuito con Correo Argentino!"
- **Al calificar**: "¡Felicitaciones! Tu pedido califica para envío gratuito con Correo Argentino."

### Para el Administrador

- **En las Órdenes**: Ve información detallada cuando se aplica envío gratuito
- **Costo Original**: Se guarda el costo original del envío
- **Umbral Configurado**: Se registra el umbral aplicado
- **Subtotal de la Orden**: Se muestra el subtotal que calificó

## Archivos Modificados

### Nuevos Archivos
- `js/free-shipping-messages.js` - Lógica JavaScript para mensajes dinámicos
- `FREE_SHIPPING_README.md` - Esta documentación

### Archivos Modificados
- `lib/constants.php` - Nueva constante para el campo
- `Settings/correoargentino-settings-form.php` - Campo de configuración
- `Classes/correoargentino-shipping-method.php` - Lógica de cálculo
- `Hooks/correoargentino-hooks.php` - Hooks y endpoints AJAX
- `css/woocommerce-correoargentino.css` - Estilos para mensajes
- `correoargentino-shipping.php` - Enqueue de scripts

## Consideraciones Técnicas

### Cálculo del Subtotal
- Se usa `WC()->cart->get_subtotal()` que excluye impuestos y envío
- Solo considera el valor de los productos

### Compatibilidad
- Funciona con ambos servicios: MiCorreo y Paq.AR
- Compatible con envíos a domicilio y sucursal
- Funciona con todos los tipos de envío (Clásico, Expreso)

### Rendimiento
- Los mensajes se actualizan solo cuando es necesario
- Uso de caché de sesión para evitar cálculos repetidos
- Actualizaciones AJAX para mejor experiencia del usuario

## Personalización

### Cambiar Textos
Los mensajes se pueden personalizar editando las cadenas en los archivos:
- `Hooks/correoargentino-hooks.php` - Mensajes del carrito y checkout
- `Classes/correoargentino-shipping-method.php` - Etiqueta del método de envío

### Cambiar Estilos
Los estilos se pueden modificar en:
- `css/woocommerce-correoargentino.css` - Estilos de mensajes y admin

### Cambiar Comportamiento
La lógica principal está en:
- `Classes/correoargentino-shipping-method.php` - Método `calculate_shipping()`

## Troubleshooting

### El Envío Gratuito No Se Aplica
1. Verifica que el umbral esté configurado correctamente
2. Confirma que el subtotal del carrito sea mayor o igual al umbral
3. Revisa que el método de envío sea Correo Argentino

### Los Mensajes No Se Muestran
1. Verifica que los scripts se estén cargando
2. Revisa la consola del navegador para errores JavaScript
3. Confirma que el CSS esté siendo cargado

### Problemas en el Admin
1. Verifica que los meta datos se estén guardando correctamente
2. Revisa que la orden use el método de envío de Correo Argentino

## Soporte

Para soporte técnico o reportar problemas:
- Revisa los logs de WooCommerce
- Verifica la configuración del plugin
- Contacta al equipo de desarrollo

## Versión

Esta funcionalidad está disponible desde la versión **3.0.3** del plugin.
