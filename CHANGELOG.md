# Registro de Cambios - Plugin Mejorado Correo Argentino

## [3.0.3] - 2024-12-XX

### ✨ Nuevas Funcionalidades
- **Envío Gratuito**: Implementación completa del sistema de envío gratuito basado en umbral de monto
  - Campo de configuración "Umbral para envío gratuito" en la configuración del método
  - Cálculo automático del envío gratuito cuando el carrito alcanza el umbral
  - Mensajes informativos dinámicos en carrito y checkout
  - Actualización en tiempo real de los mensajes
  - Información detallada del envío gratuito en el panel de administración
  - Estilos CSS mejorados para los mensajes informativos

### 🔧 Mejoras Técnicas
- **Arquitectura**: Refactorización del método `calculate_shipping()` para soportar envío gratuito
- **Hooks**: Nuevos hooks para mensajes dinámicos y endpoints AJAX
- **JavaScript**: Nuevo archivo `free-shipping-messages.js` para funcionalidad dinámica
- **CSS**: Estilos adicionales para mensajes de envío gratuito y panel de administración
- **Constantes**: Nueva constante `CA_FREE_SHIPPING_THRESHOLD` para configuración

### 📚 Documentación
- **README.md**: Documentación completa del plugin mejorado
- **FREE_SHIPPING_README.md**: Guía detallada de la funcionalidad de envío gratuito
- **CHANGELOG.md**: Registro detallado de cambios y mejoras

### 🐛 Correcciones
- Mejora en la gestión de errores del sistema de envío gratuito
- Optimización del rendimiento en la actualización de mensajes

---

## [3.0.3.rc-241028-5] - 2024-10-28

### 🔧 Mejoras Técnicas
- Se corrigen los texto dependiendo del tipo de servicio reflejado en la orden
- Se corrigen la visibilidad de acciones dependiendo el tipo de servicio reflejado en la orden

## [3.0.3.rc-240331-1] - 2024-03-31

### 🐛 Correcciones
- Se corrigen los texto dependiendo del tipo de servicio reflejado en la orden
- Se corrigen la visibilidad de acciones dependiendo el tipo de servicio reflejado en la orden

## [3.0.3.rc-240331] - 2024-03-31

### 🐛 Correcciones
- Se corrige un error en el evento de orden completada.

## [3.0.3.rc-240327-1] - 2024-03-27

### 🔧 Mejoras Técnicas
- Bypass on-complete hook

## [3.0.3.rc-240327] - 2024-03-27

### ✨ Nuevas Funcionalidades
- Agregar validacion al completar orden.
- Mostrar acciones en ordenes completadas.

## [3.0.3.rc-240326] - 2024-03-26

### 🔧 Mejoras Técnicas
- Agregar shortcodes de WC por defecto.

## [3.0.3.rc-240320] - 2024-03-20

### 🐛 Correcciones
- Se corrige problema con los metaboxes.
- Se corrige error en la importacion.

## [3.0.3.rc-240319] - 2024-03-19

### 🔧 Mejoras Técnicas
- Se agrega soporte a las consultas de la DB para versiones de WC

## [3.0.3.rc-240318] - 2024-03-18

### 🔧 Mejoras Técnicas
- Se genera version 240318 con mergeo de commit de version 27

## [3.0.3.rc-240315] - 2024-03-15

### 🔧 Mejoras Técnicas
- Se genera version sin cotizador para deploy a prod, con fecha 2024-03-15

## [3.0.3.rc-25] - 2024-03-XX

### 🐛 Correcciones
- Ticket 1032, no se puede editar las ordenes con sucursales.

## [3.0.3.rc-24] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Ajustes en tabla elegir address + telefono no obligatorio

## [3.0.3.rc-23] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Tabla de elegir address diferente no habilitada cuando es sucursal

## [3.0.3.rc-22] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Cambio de extOrderId a solamente el orderId (Se quita el time())

## [3.0.3.rc-21] - 2024-03-XX

### 🐛 Correcciones
- Corregir mensaje al ingresar contraseña incorrecta en MiCorreo login.
- Agregar validaciones y mensajes al crear cuenta con CUIT.
- Inhabilitar temporalmente Correo Argentino Hoy - Domicilio.

## [3.0.3.rc-20] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Prevenir que se cliquee el botón de aplicar multiples veces.

## [3.0.3.rc-19] - 2024-03-XX

### 🐛 Correcciones
- Se corrige un error que permitía enviar órdenes duplicadas.

## [3.0.3.rc-18] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Se oculta el checkbox que permite seleccionar e importar una orden, para evitar ordenes duplicadas.

## [3.0.3.rc-17] - 2024-03-XX

### 🐛 Correcciones
- Se corrige error que ocultaba los botones en metabox.
- Se establece que el producto a enviar es el que posee la cotización más alta.

## [3.0.3.rc-16] - 2024-03-XX

### 🐛 Correcciones
- Se corrigen los enlaces en las referencias.
- Se agrega validación y mensaje de dimensiones al cotizar.

## [3.0.3.rc-15] - 2024-03-XX

### 🐛 Correcciones
- Se corrige un error en el cálculo de la tarifa al importar relacionado con el peso.
- Se garantiza que se envía el producto con mayor costo.

## [3.0.3.rc-14] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Agregado de orderNumber en servicio import MiCorreo

## [3.0.3.rc-13] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Cambios de roland

## [3.0.3.rc-12] - 2024-03-XX

### 🐛 Correcciones
- Se corrige un problema con el peso que no era enviado en la unidad de medida correcta (gramos)

## [3.0.3.rc-11] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Se traducen mensajes de validación en contraseña.
- Se valida longitud requerida en contraseña.

## [3.0.3.rc-10] - 2024-03-XX

### 🐛 Correcciones
- Se corrige error al seleccionar método de envío por sucursal.
- Se agrega mensaje de validación en formulario de login.

## [3.0.3.rc-09] - 2024-03-XX

### 🐛 Correcciones
- Se corrigen errores en la dirección de envío.
- Se remueve servicio mockeado.

## [3.0.3.rc-08] - 2024-03-XX

### 🐛 Correcciones
- Se corrige problema con direcciones al usar el navegador para volver atrás.

## [3.0.0.rc-07] - 2024-03-XX

### 🐛 Correcciones
- Se corrigen problemas en las direcciones.
- Se corrigen defecto al registrar una orden.

## [3.0.0.rc-06] - 2024-03-XX

### 🐛 Correcciones
- Se corrigen varios errores en la cotización.
- Se corrigen errores en las direcciones.
- Se filtra la table de órdenes por el tipo de API activa.
- Se corrigen mensajes y acciones en las órdenes.
- Se muestran direcciones más detalladas.

## [3.0.0.rc-05] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Se actualiza la dirección de envío bajo demanda.
- Se fuerza a completar los datos del comercio y se muestra un mensaje mientras no estén completados.
- Se corrigen la selección de las órdenes mediantes checkboxes. 
- Se corrigen el mensaje del campo email.

## [3.0.3.rc-04] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Se cambian los textos de MiCorreo y Paq.AR por los correctos.
- Se agrega un mensaje de validación faltante.
- Se limpia el formulario de forma correcta.

## [3.0.3.rc-03] - 2024-03-XX

### 🐛 Correcciones
- Corrigen errores en los formularios de configuración
- Se mejora el selector de servicios
- Se elimina un select vacío
- Se corrigen mensajes no traducidos
- Mejoras generales

## [3.0.3.rc-02] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Se fuerza limpiar caché en los métodos de envío. 
- Se limita el número de registros a 10 durante una operacion masiva.

## [3.0.3] - 2024-03-XX

### 🔧 Mejoras Técnicas
- Se envia el dato peso en gramos.
- Se agregan acciones masivas para importar.
- Se reactivan las validaciones del form de configuracion.

## [3.0.2] - 2024-XX-XX

### ✨ Nuevas Funcionalidades
- Se agrega la opción de cotización contra MiCorreo.
- Se filtra el método de envío desde la página de checkout.

---

## Notas de Versión

### Versión 3.0.3 (Actual)
Esta versión incluye la primera mejora significativa al plugin oficial:
- **Sistema de Envío Gratuito**: Funcionalidad completa para configurar umbrales de envío gratuito
- **Mejoras de UX**: Mensajes informativos y actualización dinámica
- **Documentación**: README completo y guías de uso

### Versiones Anteriores
Las versiones anteriores corresponden al plugin oficial de Correo Argentino sin las mejoras implementadas en este fork.

---

## Próximas Versiones

### [3.0.4] - Próximamente
- **Descuentos por Volumen**: Sistema de descuentos automáticos
- **Horarios de Entrega**: Selección de horarios preferidos
- **Notificaciones SMS**: Alertas por SMS del estado del envío

### [3.0.5] - En Desarrollo
- **Integración con Mercado Libre**: Sincronización automática
- **Reportes Avanzados**: Estadísticas detalladas de envíos
- **API REST**: Endpoints para integraciones externas

---

*Para más información sobre las mejoras implementadas, consulta el [README.md](README.md) principal.*
