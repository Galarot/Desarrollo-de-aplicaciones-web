# Migración de JavaScript a React con JSX

## Estructura de Componentes

### Componentes Principales

1. **App.jsx** - Componente principal que integra todos los demás
2. **MobileMenu.jsx** - Menú móvil con estado de apertura/cierre
3. **ToastContainer.jsx** - Sistema de notificaciones toast (máximo 6)
4. **CartBadge.jsx** - Contador del carrito con sincronización localStorage
5. **AjaxFormHandler.jsx** - Interceptor de formularios AJAX para carrito
6. **FavoritoButton.jsx** - Botón de favoritos con toggle
7. **ViniloCard.jsx** - Tarjeta individual de vinilo
8. **ViniloList.jsx** - Lista de vinilos con grid responsive
9. **SearchForm.jsx** - Formulario de búsqueda
10. **AddToCart.jsx** - Botón para añadir al carrito

### Controladores React (Symfony UX)

Cada componente tiene su controlador en `assets/react/controllers/` para integrarse con Symfony UX React.

## Uso en Plantillas Twig

### Ejemplo básico:

```twig
{# Menú móvil #}
<div {{ react_component('MobileMenu') }}></div>

{# Badge del carrito #}
<div {{ react_component('CartBadge', { initialCount: carrito_count }) }}></div>

{# Sistema de toasts #}
<div {{ react_component('ToastContainer') }}></div>

{# Manejador de formularios AJAX #}
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
```

## Funcionalidades Conservadas

✅ Menú móvil con toggle
✅ Sistema de notificaciones toast (límite de 6)
✅ Contador del carrito con localStorage
✅ Interceptor AJAX para formularios del carrito
✅ Botones de favoritos con toggle
✅ Animaciones y transiciones
✅ Búsqueda de vinilos
✅ Tarjetas de productos
✅ Todos los estilos y comportamientos originales

## Variables Globales

Los componentes utilizan variables globales para comunicarse:

```javascript
window.showToast(message, isError)  // Mostrar notificación
window.updateCartBadge(count)       // Actualizar contador
window.routes                        // Rutas de la aplicación
window.userRoles                     // Roles del usuario
window.isAuthenticated              // Estado de autenticación
```

## Notas Importantes

- Todos los componentes mantienen el comportamiento exacto del JavaScript original
- Los estilos Tailwind CSS se conservan tal cual
- La integración con Symfony se mantiene mediante Symfony UX React
- El sistema de CSRF tokens se preserva
- LocalStorage se usa para persistir el contador del carrito
