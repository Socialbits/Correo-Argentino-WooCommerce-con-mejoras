# Correo Argentino WooCommerce - Plugin Mejorado

[![Version](https://img.shields.io/badge/version-3.0.3-blue.svg)](https://github.com/Socialbits/Correo-Argentino-WooCommerce-con-mejoras)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-3.0+-green.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)

## 📋 Descripción

Este plugin es una versión mejorada del **plugin oficial de Correo Argentino para WooCommerce**, que incluye funcionalidades adicionales y mejoras de UX para optimizar la experiencia de envío en tiendas argentinas.

### 🎯 Características Principales

- **Integración Oficial**: Basado en el plugin oficial de Correo Argentino
- **Múltiples Servicios**: Soporte para MiCorreo y Paq.AR
- **Envíos Flexibles**: A domicilio y a sucursales
- **Cotización en Tiempo Real**: Tarifas actualizadas desde la API oficial
- **Gestión de Órdenes**: Sistema completo de tracking y etiquetas
- **Interfaz Mejorada**: UX optimizada para administradores y clientes

## 🚀 Mejoras Implementadas

### ✨ Envío Gratuito (v3.0.3)
**Nueva funcionalidad que permite configurar un umbral de monto mínimo para envío gratuito.**

- **Configuración Flexible**: Define el monto mínimo para envío gratuito
- **Mensajes Informativos**: Muestra cuánto falta para calificar
- **Actualización Dinámica**: Los mensajes se actualizan automáticamente
- **Visibilidad Completa**: Las opciones siguen visibles pero con costo 0
- **Información en Admin**: Detalles del envío gratuito en las órdenes

📖 [Ver documentación completa del envío gratuito](FREE_SHIPPING_README.md)

## 🛠️ Instalación

### Requisitos Previos
- WordPress 5.0+
- WooCommerce 3.0+
- PHP 7.4+

### Pasos de Instalación
1. **Descargar**: Clona este repositorio en tu servidor
2. **Reemplazar**: Sustituye los archivos del plugin oficial
3. **Activar**: Activa el plugin desde WordPress
4. **Configurar**: Sigue la guía de configuración

## ⚙️ Configuración

### 1. Configuración Básica
1. Ve a **WooCommerce > Configuración > Envíos**
2. Selecciona tu zona de envío
3. Agrega el método **Correo Argentino**
4. Completa los datos de negocio requeridos

### 2. Configuración del Envío Gratuito
1. En la configuración del método de envío
2. Completa el campo **"Umbral para envío gratuito"**
3. Ejemplo: `5000` = envío gratuito para pedidos de $5000+
4. Guarda los cambios

### 3. Configuración de Servicios
- **MiCorreo**: Para envíos estándar y express
- **Paq.AR**: Para paquetes de mayor peso
- **Tipos de Envío**: Clásico o Expreso
- **Métodos**: A domicilio o a sucursal

## 🔧 Funcionalidades Técnicas

### Arquitectura del Plugin
```
correoargentino-3.0.3/
├── Classes/           # Clases principales del método de envío
├── Service/           # Servicios de API (MiCorreo, Paq.AR)
├── Settings/          # Formularios de configuración
├── Hooks/             # Hooks de WordPress y WooCommerce
├── js/                # Scripts JavaScript
├── css/               # Estilos CSS
├── lib/               # Utilidades y constantes
└── templates/         # Plantillas de frontend
```

### Servicios Soportados
- **MiCorreo**: API para envíos estándar
- **Paq.AR**: API para paquetes pesados
- **Sucursales**: Red de agencias de Correo Argentino
- **Domicilio**: Envíos directos a la dirección del cliente

### Características Técnicas
- **Caché Inteligente**: Evita consultas repetidas a la API
- **Validaciones Robustas**: Verificación de datos y dimensiones
- **Manejo de Errores**: Gestión completa de excepciones
- **Compatibilidad**: Funciona con todas las versiones de WooCommerce

## 📱 Experiencia del Usuario

### En el Carrito
- Mensajes informativos sobre envío gratuito
- Cálculo dinámico de tarifas
- Selección de sucursales disponibles

### En el Checkout
- Opciones de envío claras y organizadas
- Validación de direcciones en tiempo real
- Información completa de costos

### En el Panel de Administración
- Gestión completa de órdenes
- Generación de etiquetas
- Tracking de envíos
- Información detallada del envío gratuito

## 🎨 Personalización

### Estilos CSS
Los estilos se pueden personalizar editando:
- `css/woocommerce-correoargentino.css` - Estilos principales
- `css/select2-override.css` - Personalización de Select2
- `css/validate.css` - Estilos de validación

### Mensajes
Los textos se pueden modificar en:
- `Hooks/correoargentino-hooks.php` - Mensajes del frontend
- `Classes/correoargentino-shipping-method.php` - Etiquetas del método

### JavaScript
Funcionalidades personalizables en:
- `js/free-shipping-messages.js` - Mensajes de envío gratuito
- `js/service-selector.js` - Selector de servicios
- `js/branch.js` - Gestión de sucursales

## 🔍 Troubleshooting

### Problemas Comunes

#### El Envío Gratuito No Se Aplica
1. Verifica que el umbral esté configurado correctamente
2. Confirma que el subtotal del carrito sea mayor o igual al umbral
3. Revisa que el método de envío sea Correo Argentino

#### Los Mensajes No Se Muestran
1. Verifica que los scripts se estén cargando
2. Revisa la consola del navegador para errores JavaScript
3. Confirma que el CSS esté siendo cargado

#### Problemas de API
1. Verifica las credenciales de Correo Argentino
2. Confirma que el servicio esté activo
3. Revisa los logs de WooCommerce

### Logs y Debug
- **WooCommerce**: Habilita el modo debug en WooCommerce
- **WordPress**: Revisa los logs de WordPress
- **API**: Verifica las respuestas de la API de Correo Argentino

## 📈 Roadmap de Mejoras

### Próximas Funcionalidades


### Mejoras Técnicas
- [ ] **API REST**: Endpoints REST para integraciones externas
- [ ] **Webhooks**: Notificaciones en tiempo real
- [ ] **Cache Avanzado**: Sistema de caché más eficiente
- [ ] **Testing**: Suite de tests automatizados

## 🤝 Contribuciones

### Cómo Contribuir
1. **Fork** del repositorio
2. **Crea** una rama para tu funcionalidad
3. **Implementa** tus mejoras
4. **Testea** exhaustivamente
5. **Envía** un Pull Request

### Estándares de Código
- **PHP**: PSR-12 coding standards
- **JavaScript**: ES6+ con compatibilidad
- **CSS**: BEM methodology
- **Documentación**: Markdown con ejemplos

## 📞 Soporte

### Canales de Soporte
- **Issues**: [GitHub Issues](https://github.com/Socialbits/Correo-Argentino-WooCommerce-con-mejoras/issues)
- **Documentación**: [Wiki del proyecto](https://github.com/Socialbits/Correo-Argentino-WooCommerce-con-mejoras/wiki)
- **Comunidad**: [Discussions](https://github.com/Socialbits/Correo-Argentino-WooCommerce-con-mejoras/discussions)

### Recursos Útiles
- [Documentación de WooCommerce](https://docs.woocommerce.com/)
- [API de Correo Argentino](https://www.correoargentino.com.ar/integraciones)
- [WordPress Developer Handbook](https://developer.wordpress.org/)

## 📄 Licencia

Este proyecto está basado en el plugin oficial de Correo Argentino y mantiene su licencia original. Las mejoras implementadas están sujetas a la misma licencia.

## 🙏 Agradecimientos

- **Correo Argentino**: Por el plugin oficial base
- **WooCommerce**: Por la plataforma de e-commerce
- **Comunidad WordPress**: Por el ecosistema de plugins
- **Contribuidores**: Por las mejoras y sugerencias

## 📊 Estadísticas del Proyecto

- **Versión Actual**: 3.0.3
- **Última Actualización**: Diciembre 2024
- **Funcionalidades**: 1 mejora implementada
- **Compatibilidad**: WooCommerce 3.0+
- **Servicios**: MiCorreo, Paq.AR

---

**¿Te gustó el plugin? ¡Dale una ⭐ en GitHub!**

*Desarrollado con ❤️ para la comunidad de e-commerce argentina*
