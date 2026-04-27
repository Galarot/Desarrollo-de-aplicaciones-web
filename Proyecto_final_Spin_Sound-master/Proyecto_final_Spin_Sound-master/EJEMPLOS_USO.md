# Ejemplos Prácticos de Uso

## Ejemplo 1: Migrar una página completa

### Antes (base.html.twig)
```twig
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Mi página</h1>
{% endblock %}
```

### Ahora (base_react.html.twig)
```twig
{% extends 'base_react.html.twig' %}

{% block body %}
    <h1>Mi página</h1>
{% endblock %}
```

**¡Así de simple!** Todo el JavaScript se convierte automáticamente en React.

---

## Ejemplo 2: Lista de vinilos con React

```twig
{% extends 'base_react.html.twig' %}

{% block body %}
<main class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Catálogo</h1>
    
    {# Componente React para la lista #}
    <div {{ react_component('ViniloList', {
        vinilos: vinilos|json_encode|raw,
        favoritosIds: favoritosIds|json_encode|raw,
        csrfToken: csrf_token('add-to-cart')
    }) }}></div>
</main>
{% endblock %}
```

---

## Ejemplo 3: Detalle de producto con favoritos

```twig
{% extends 'base_react.html.twig' %}

{% block body %}
<main class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-2 gap-8">
        <div>
            <img src="{{ vinilo.imagen }}" alt="{{ vinilo.titulo }}">
        </div>
        
        <div>
            <h1>{{ vinilo.titulo }}</h1>
            <p>{{ vinilo.precio }} €</p>
            
            <div class="flex gap-4 mt-4">
                {# Formulario tradicional - se intercepta con AjaxFormHandler #}
                <form method="post" action="{{ path('app_carrito_add') }}" class="flex-1">
                    <input type="hidden" name="vinilo_id" value="{{ vinilo.id }}">
                    <input type="hidden" name="_token" value="{{ csrf_token('add-to-cart') }}">
                    <button type="submit" class="w-full btn-primary">
                        Añadir al carrito
                    </button>
                </form>
                
                {# Componente React para favoritos #}
                <div {{ react_component('FavoritoButton', {
                    viniloId: vinilo.id,
                    isFavorito: isFavorito
                }) }}></div>
            </div>
        </div>
    </div>
</main>
{% endblock %}
```

---

## Ejemplo 4: Usar componentes individuales en plantilla existente

```twig
{% extends 'base.html.twig' %}

{% block body %}
<main>
    {# Añadir sistema de toasts a una página específica #}
    <div {{ react_component('ToastContainer') }}></div>
    
    {# Añadir manejador AJAX #}
    <div {{ react_component('AjaxFormHandler') }}></div>
    
    <h1>Mi página con React parcial</h1>
    
    {# Botón de favoritos React #}
    <div {{ react_component('FavoritoButton', {
        viniloId: 123,
        isFavorito: false
    }) }}></div>
</main>
{% endblock %}
```

---

## Ejemplo 5: Personalizar el buscador

```twig
<header>
    <nav>
        {# Buscador React personalizado #}
        <div {{ react_component('SearchForm', {
            initialQuery: app.request.query.get('q', ''),
            action: path('app_vinilo_index')
        }) }}></div>
    </nav>
</header>
```

---

## Ejemplo 6: Tarjeta de producto individual

```twig
{# En un loop de productos #}
{% for vinilo in vinilos %}
    <div class="col-md-4">
        {# Usar componente ViniloCard #}
        <div {{ react_component('ViniloCard', {
            vinilo: vinilo|json_encode|raw,
            isFavorito: vinilo.id in favoritosIds,
            csrfToken: csrf_token('add-to-cart')
        }) }}></div>
    </div>
{% endfor %}
```

---

## Ejemplo 7: Botón añadir al carrito standalone

```twig
<div class="product-actions">
    {# Botón React con todas las funcionalidades #}
    <div {{ react_component('AddToCart', {
        viniloId: vinilo.id,
        csrfToken: csrf_token('add-to-cart'),
        productName: vinilo.titulo,
        disabled: vinilo.stock == 0
    }) }}></div>
</div>
```

---

## Ejemplo 8: Menú móvil personalizado

```twig
<header>
    <div class="mobile-menu-container">
        {# Menú móvil React #}
        <div {{ react_component('MobileMenu') }}></div>
    </div>
</header>
```

---

## Ejemplo 9: Badge del carrito con contador

```twig
<nav>
    <a href="{{ path('app_carrito_show_user') }}" class="cart-link">
        <svg><!-- icono carrito --></svg>
        
        {# Badge React con sincronización localStorage #}
        <div {{ react_component('CartBadge', {
            initialCount: carrito_count
        }) }}></div>
    </a>
</nav>
```

---

## Ejemplo 10: Página completa con todos los componentes

```twig
{% extends 'base_react.html.twig' %}

{% block title %}Tienda - Spin&Sound{% endblock %}

{% block body %}
<main class="container mx-auto px-4 py-8">
    {# Buscador #}
    <div class="mb-8">
        <div {{ react_component('SearchForm', {
            initialQuery: query,
            action: path('app_vinilo_index')
        }) }}></div>
    </div>
    
    {# Filtros activos #}
    {% if filters %}
        <div class="filters mb-6">
            {# ... etiquetas de filtros ... #}
        </div>
    {% endif %}
    
    {# Lista de productos #}
    <div {{ react_component('ViniloList', {
        vinilos: vinilos|json_encode|raw,
        favoritosIds: favoritosIds|json_encode|raw,
        csrfToken: csrf_token('add-to-cart')
    }) }}></div>
</main>

{# Los componentes ToastContainer y AjaxFormHandler 
     ya están en base_react.html.twig #}
{% endblock %}
```

---

## Ejemplo 11: Mostrar notificación desde Twig

```twig
{% block body %}
<main>
    <h1>Página de confirmación</h1>
    
    {# Mostrar toast al cargar la página #}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.showToast === 'function') {
                window.showToast('¡Operación exitosa!');
            }
        });
    </script>
</main>
{% endblock %}
```

---

## Ejemplo 12: Actualizar badge del carrito manualmente

```javascript
// Desde cualquier JavaScript
if (typeof window.updateCartBadge === 'function') {
    window.updateCartBadge(5); // Actualizar a 5 items
}
```

---

## Ejemplo 13: Integrar con formularios existentes

```twig
{# El formulario se intercepta automáticamente por AjaxFormHandler #}
<form method="post" action="{{ path('app_carrito_add') }}">
    <input type="hidden" name="vinilo_id" value="{{ vinilo.id }}">
    <input type="hidden" name="_token" value="{{ csrf_token('add-to-cart') }}">
    
    <button type="submit" class="btn btn-primary">
        Añadir al carrito
    </button>
</form>

{# No necesitas JavaScript adicional, 
     AjaxFormHandler lo maneja automáticamente #}
```

---

## Ejemplo 14: Crear un componente personalizado

```jsx
// assets/react/components/MiComponente.jsx
import React, { useState } from 'react';

export default function MiComponente({ titulo, items }) {
  const [seleccionado, setSeleccionado] = useState(null);

  return (
    <div className="mi-componente">
      <h2>{titulo}</h2>
      <ul>
        {items.map(item => (
          <li 
            key={item.id}
            onClick={() => setSeleccionado(item.id)}
            className={seleccionado === item.id ? 'active' : ''}
          >
            {item.nombre}
          </li>
        ))}
      </ul>
    </div>
  );
}
```

```jsx
// assets/react/controllers/MiComponente.jsx
import React from 'react';
import MiComponente from '../components/MiComponente';

export default function (props) {
  return <MiComponente {...props} />;
}
```

```twig
{# Usar en Twig #}
<div {{ react_component('MiComponente', {
    titulo: 'Mi Lista',
    items: items|json_encode|raw
}) }}></div>
```

---

## Tips y Trucos

### 1. Pasar datos complejos
```twig
<div {{ react_component('MiComponente', {
    data: {
        usuario: app.user ? {
            id: app.user.id,
            nombre: app.user.nombre
        } : null,
        configuracion: configuracion|json_encode|raw
    }
}) }}></div>
```

### 2. Combinar React con JavaScript vanilla
```twig
{% block javascripts %}
    {{ parent() }}
    <script>
        // Tu JavaScript personalizado
        document.addEventListener('DOMContentLoaded', () => {
            // Usar funciones globales de React
            if (typeof window.showToast === 'function') {
                window.showToast('Bienvenido!');
            }
        });
    </script>
{% endblock %}
```

### 3. Debugging
```jsx
// En cualquier componente
console.log('Props recibidas:', props);
console.log('Estado actual:', state);
```

---

**¡Estos ejemplos cubren todos los casos de uso comunes!** 🚀
