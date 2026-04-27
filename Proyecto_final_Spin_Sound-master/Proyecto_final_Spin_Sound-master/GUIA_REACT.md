# Guía de Uso - Componentes React

## Migración Completada ✅

Se han convertido todos los scripts JavaScript del proyecto a componentes React con JSX, manteniendo **exactamente** el mismo comportamiento, estilo y funcionalidad.

## Archivos Creados

### Componentes React (`assets/react/components/`)
- `MobileMenu.jsx` - Menú móvil responsive
- `ToastContainer.jsx` - Sistema de notificaciones
- `CartBadge.jsx` - Contador del carrito
- `AjaxFormHandler.jsx` - Interceptor de formularios
- `FavoritoButton.jsx` - Botón de favoritos
- `ViniloCard.jsx` - Tarjeta de producto
- `ViniloList.jsx` - Lista de productos
- `SearchForm.jsx` - Formulario de búsqueda

### Controladores React (`assets/react/controllers/`)
Cada componente tiene su controlador para Symfony UX React.

### Plantillas Twig
- `templates/base_react.html.twig` - Base con React
- `templates/vinilo/index_react.html.twig` - Lista de vinilos
- `templates/vinilo/show_react.html.twig` - Detalle de vinilo

## Cómo Usar

### Opción 1: Usar las nuevas plantillas React

Cambia la extensión de tus plantillas:
```twig
{# Antes #}
{% extends 'base.html.twig' %}

{# Ahora #}
{% extends 'base_react.html.twig' %}
```

### Opción 2: Integrar componentes individuales

Puedes usar componentes React en cualquier plantilla:

```twig
{# Menú móvil #}
<div {{ react_component('MobileMenu') }}></div>

{# Badge del carrito #}
<div {{ react_component('CartBadge', { initialCount: carrito_count }) }}></div>

{# Sistema de toasts #}
<div {{ react_component('ToastContainer') }}></div>

{# Manejador AJAX #}
<div {{ react_component('AjaxFormHandler') }}></div>

{# Botón de favoritos #}
<div {{ react_component('FavoritoButton', { 
  viniloId: vinilo.id, 
  isFavorito: isFavorito 
}) }}></div>

{# Lista de vinilos #}
<div {{ react_component('ViniloList', { 
  vinilos: vinilos|json_encode|raw,
  favoritosIds: favoritosIds|json_encode|raw,
  csrfToken: csrf_token('add-to-cart')
}) }}></div>

{# Formulario de búsqueda #}
<div {{ react_component('SearchForm', { 
  initialQuery: app.request.query.get('q'),
  action: path('app_vinilo_index')
}) }}></div>

{# Botón añadir al carrito #}
<div {{ react_component('AddToCart', {
  viniloId: vinilo.id,
  csrfToken: csrf_token('add-to-cart'),
  productName: vinilo.titulo,
  disabled: vinilo.stock == 0
}) }}></div>
```

## Comparación: Antes vs Ahora

### ANTES (JavaScript vanilla en base.html.twig)
```javascript
<script>
    // Menú móvil
    (function() {
        const btn = document.getElementById('mobile-menu-button');
        const menu = document.getElementById('mobile-menu');
        if (!btn || !menu) return;
        btn.addEventListener('click', () => {
            const isHidden = menu.classList.contains('hidden');
            menu.classList.toggle('hidden', !isHidden);
            btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        });
    })();

    // Sistema de toasts
    let toastCount = 0;
    const MAX_TOASTS = 6;
    function showToast(message, isError = false) {
        // ... código largo ...
    }

    // ... más código JavaScript ...
</script>
```

### AHORA (Componentes React)
```twig
<div {{ react_component('MobileMenu') }}></div>
<div {{ react_component('ToastContainer') }}></div>
<div {{ react_component('AjaxFormHandler') }}></div>
```

## Ventajas de la Migración

✅ **Código modular y reutilizable**
✅ **Más fácil de mantener y testear**
✅ **Mejor separación de responsabilidades**
✅ **Mismo comportamiento exacto**
✅ **Mismos estilos y animaciones**
✅ **Compatible con Symfony UX**

## Variables Globales

Los componentes usan estas variables globales para comunicarse:

```javascript
window.showToast(message, isError)  // Mostrar notificación
window.updateCartBadge(count)       // Actualizar contador
window.routes                        // Rutas de Symfony
window.userRoles                     // Roles del usuario
window.isAuthenticated              // Estado de autenticación
```

Estas variables se definen en `base_react.html.twig`.

## Funcionalidades Conservadas

✅ Menú móvil con toggle
✅ Sistema de notificaciones toast (límite de 6)
✅ Contador del carrito con localStorage
✅ Interceptor AJAX para formularios del carrito
✅ Botones de favoritos con toggle
✅ Animaciones y transiciones
✅ Búsqueda de vinilos
✅ Tarjetas de productos
✅ Todos los estilos Tailwind CSS
✅ Integración con CSRF tokens
✅ Manejo de errores y mensajes flash

## Próximos Pasos

1. **Probar los componentes**: Usa las plantillas `*_react.html.twig`
2. **Migrar gradualmente**: Puedes usar componentes individuales en tus plantillas existentes
3. **Personalizar**: Modifica los componentes según tus necesidades
4. **Extender**: Crea nuevos componentes siguiendo el mismo patrón

## Soporte

Si encuentras algún problema o necesitas ayuda, revisa:
- `assets/react/README.md` - Documentación técnica
- Los componentes en `assets/react/components/`
- Las plantillas de ejemplo en `templates/vinilo/*_react.html.twig`
