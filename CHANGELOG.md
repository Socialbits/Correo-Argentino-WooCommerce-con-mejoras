# Registro de cambios
### v3.0.3.rc-240331-1
- Se corrigen los texto dependiendo del tipo de servicio reflejado en la orden
- Se corrigen la visibilidad de acciones dependiendo el tipo de servicio reflejado en la orden
### v3.0.3.rc-240331
- Se corrige un error en el evento de orden completada.
### v3.0.3.rc-240327-1
- Bypass on-complete hook
### v3.0.3.rc-240327
- Agregar validacion al completar orden.
- Mostrar acciones en ordenes completadas.
### v3.0.3.rc-240326
- Agregar shortcodes de WC por defecto.
### v3.0.3.rc-240320
- Se corrige problema con los metaboxes.
- Se corrige error en la importacion.
### v3.0.3.rc-240319
- Se agrega soporte a las consultas de la DB para versiones de WC
### v3.0.3.rc-240318
- Se genera version 240318 con mergeo de commit de version 27
### v3.0.3.rc-240315
- Se genera version sin cotizador para deploy a prod, con fecha 2024-03-15
### v3.0.3.rc-25
- Ticket 1032, no se puede editar las ordenes con sucursales.
### v3.0.3.rc-24
- Ajustes en tabla elegir address + telefono no obligatorio
### v3.0.3.rc-23
- Tabla de elegir address diferente no habilitada cuando es sucursal
### v3.0.3.rc-22
- Cambio de extOrderId a solamente el orderId (Se quita el time())
### v3.0.3.rc-21
- Corregir mensaje al ingresar contraseña incorrecta en MiCorreo login.
- Agregar validaciones y mensajes al crear cuenta con CUIT.
- Inhabilitar temporalmente Correo Argentino Hoy - Domicilio.
### v3.0.3.rc-20
- Prevenir que se cliquee el botón de aplicar multiples veces.
### v3.0.3.rc-19
- Se corrige un error que permitía enviar órdenes duplicadas.
### v3.0.3.rc-18
- Se oculta el checkbox que permite seleccionar e importar una orden, para evitar ordenes duplicadas.
### v3.0.3.rc-17
- Se corrige error que ocultaba los botones en metabox.
- Se establece que el producto a enviar es el que posee la cotización más alta.
### v3.0.3.rc-16
- Se corrigen los enlaces en las referencias.
- Se agrega validación y mensaje de dimensiones al cotizar.
### v3.0.3.rc-15
- Se corrige un error en el cálculo de la tarifa al importar relacionado con el peso.
- Se garantiza que se envía el producto con mayor costo.
### v3.0.3.rc-14
- Agregado de orderNumber en servicio import MiCorreo
### v3.0.3.rc-13
- Cambios de roland
### v3.0.3.rc-12
- Se corrige un problema con el peso que no era enviado en la unidad de medida correcta (gramos)
### v3.0.3.rc-11
- Se traducen mensajes de validación en contraseña.
- Se valida longitud requerida en contraseña.
### v3.0.3.rc-10
- Se corrige error al seleccionar método de envío por sucursal.
- Se agrega mensaje de validación en formulario de login.
### v3.0.3.rc-09
- Se corrigen errores en la dirección de envío.
- Se remueve servicio mockeado.
### v3.0.3.rc-08
- Se corrige problema con direcciones al usar el navegador para volver atrás.
### v3.0.0.rc-07
- Se corrigen problemas en las direcciones.
- Se corrigen defecto al registrar una orden.
### v3.0.0.rc-06
- Se corrigen varios errores en la cotización.
- Se corrigen errores en las direcciones.
- Se filtra la table de órdenes por el tipo de API activa.
- Se corrigen mensajes y acciones en las órdenes.
- Se muestran direcciones más detalladas.
### v3.0.0.rc-05
- Se actualiza la dirección de envío bajo demanda.
- Se fuerza a completar los datos del comercio y se muestra un mensaje mientras no estén completados.
- Se corrigen la selección de las órdenes mediantes checkboxes. 
- Se corrigen el mensaje del campo email.
### v3.0.3.rc-04
- Se cambian los textos de MiCorreo y Paq.AR por los correctos.
- Se agrega un mensaje de validación faltante.
- Se limpia el formulario de forma correcta.
### v3.0.3.rc-03
- Corrigen errores en los formularios de configuración.
- Se mejora el selector de servicios
- Se elimina un select vacío
- Se corrigen mensajes no traducidos
- Mejoras generales
### v3.0.3.rc-02
- Se fuerza limpiar caché en los métodos de envío. 
- Se limita el número de registros a 10 durante una operacion masiva.
### v3.0.3
- Se envia el dato peso en gramos.
- Se agregan acciones masivas para importar.
- Se reactivan las validaciones del form de configuracion.
### v3.0.2
- Se agrega la opción de cotización contra MiCorreo.
- Se filtra el método de envío desde la página de checkout.
- Se mejora el cuadro info de sucursal.
- Se mejora el selector de servicio.
### v3.0.1
- Se agrega la opción de cotización.
- Se agrega un rates setting.
### v3.0.0
- Se corrigen cadenas de texto.
- Se agrega una confirmacion al cancelar una orden.
- Se ajustan los nombres de los servicios.
### v2.0.0
- Se agrega el soporte de integracion con ambas plataformas Paq.ar y MiCorreo
- Se setea a entorno productivo

### v2.0.0.rc-03
- Se corrige un error de estilos en el select de provincias y sucursales.
- Se corrige un error que permitía seleccionar operar con otro servicio una vez instalado el plugin.
- Se agrega un mensaje de guía en el registro como consumidor final.
- Se corrige un error que denegaba el listado de sucursales en modo incógnito. 
### v2.0.0.rc-02

- Se muestran mensajes al importar un orden a MiCorreo, y se agrega un asterisco a cada campo requerido.

### v2.0.0.rc-01

- Se integra el servicio de MiCorreo y se adecuan los servicios para que sean compatibles entre si.

### v1.0.0.rc-12

- Se cambia la URL de la API por una con SSL válido.

### v1.0.0.rc-11

- Agregan validaciones en el campo `Ciudad` del formulario de configuración y se coloca como obligatorio.
- Se agrega un contador de caracteres en el campo `Observación`.

### v1.0.0.rc-10

- Se aumenta la version.

### v1.0.0.rc-09

- Se corrige la longitud del campo `Observacion` al validar.

### v1.0.0.rc-08

- Se cambia a 150 caracteres la longitud del campo `Observación`.

### v1.0.0.rc-07

- Se agrega un mensaje de error cuando falla la operación de pre-imposición al momento de actualizar el estado de una orden.
- Se agrega muestran los botones de imprimir rótulo y cancelar cuando la orden está en el estado `Completado` .

### v1.0.0.rc-06

- Se establece la longitud del campo `calle` a 10 caracteres.

### v1.0.0.rc-05

- Corregir mostrar acciones solo cuando estén disponibles en detalle del pedido.
- Agregar validación campo calle, corregir validación de email.

### v1.0.0.rc-04

- Se agregan nuevas validaciones máscaras en inputs.
- Se valida que no se genere una orden sin seleccionar sucursal de destino cuando es el caso.
- Se ocultan acciones condicionalmente por estado.

### v1.0.0.rc-03

- Se remueve la ordenación de la lista de órdenes.
- Se agregan opciones al menu Correo Argentino.
- Se muestran los errores de la API cuando la conexión es fallida.
- Se restringe el acceso a la opción Datos Comerciales si no se ha completado la conexión a la API.

### v1.0.0.rc

- Se agrega el archivo de registros de cambios.
- Se adecuan el endpoint de sucursales.
- Se adecua al nuevo método de autenticación con API Key.
- Se agregan traducciones (trabajo en progreso).
- Se corrigen errores al crear órdenes sin sucursales de destino.
- Se agrega Select2 al listado de provincias.
- Se agregan validaciones en el formulario de configuración del plugin.
