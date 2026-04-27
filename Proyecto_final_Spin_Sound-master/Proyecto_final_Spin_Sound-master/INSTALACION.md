# Instalación y Configuración - Componentes React

## ✅ Estado Actual

Los componentes React ya están creados y listos para usar. Solo necesitas seguir estos pasos para activarlos.

---

## 📋 Requisitos Previos

Tu proyecto ya tiene instalado:
- ✅ Symfony UX React (`symfony/ux-react`)
- ✅ React y ReactDOM
- ✅ Asset Mapper de Symfony

---

## 🚀 Pasos de Activación

### 1. Verificar que Symfony UX React está instalado

```bash
composer show symfony/ux-react
```

Si no está instalado:
```bash
composer require symfony/ux-react
```

### 2. Verificar importmap.php

Asegúrate de que `importmap.php` incluye React:

```php
// importmap.php
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/ux-react' => [
        'path' => './vendor/symfony/ux-react/assets/dist/render_controller.js',
    ],
    'react' => [
        'version' => '18.3.1',
    ],
    'react-dom' => [
        'version' => '18.3.1',
    ],
    // ... otros imports
];
```

### 3. Registrar los componentes React

Edita `assets/controllers.json` para incluir los controladores React:

```json
{
    "controllers": {
        "@symfony/ux-react": {
            "react": {
                "enabled": true,
                "fetch": "eager"
            }
        }
    },
    "entrypoints": []
}
```

### 4. Compilar assets

```bash
php bin/console asset-map:compile
```

---

## 🎯 Uso Inmediato

### Opción A: Usar plantilla base React completa

En tu controlador:
```php
// src/Controller/ViniloController.php
public function index(): Response
{
    return $this->render('vinilo/index_react.html.twig', [
        'vinilos' => $vinilos,
        'favoritosIds' => $favoritosIds,
        'carrito_count' => $carritoCount,
    ]);
}
```

### Opción B: Extender base_react.html.twig

```twig
{# templates/mi_pagina.html.twig #}
{% extends 'base_react.html.twig' %}

{% block body %}
    <h1>Mi contenido</h1>
{% endblock %}
```

### Opción C: Usar componentes individuales

```twig
{# En cualquier plantilla #}
<div {{ react_component('ToastContainer') }}></div>
<div {{ react_component('CartBadge', { initialCount: carrito_count }) }}></div>
```

---

## 🔧 Configuración Adicional

### Variables Globales JavaScript

Las variables globales ya están configuradas en `base_react.html.twig`:

```javascript
window.routes = { ... };
window.userRoles = [ ... ];
window.isAuthenticated = true/false;
```

Si usas `base.html.twig` original, añade esto antes de `</head>`:

```twig
<script>
    window.routes = {
        app_home: '{{ path('app_home') }}',
        app_vinilo_index: '{{ path('app_vinilo_index') }}',
        // ... otras rutas
    };
    window.userRoles = {{ app.user ? app.user.roles|json_encode|raw : '[]' }};
    window.isAuthenticated = {{ app.user ? 'true' : 'false' }};
</script>
```

---

## 🧪 Probar la Instalación

### 1. Probar ToastContainer

```twig
{% extends 'base_react.html.twig' %}

{% block body %}
    <button onclick="window.showToast('¡Funciona!')">
        Probar Toast
    </button>
{% endblock %}
```

### 2. Probar CartBadge

```twig
{% extends 'base_react.html.twig' %}

{% block body %}
    <button onclick="window.updateCartBadge(5)">
        Actualizar Badge a 5
    </button>
{% endblock %}
```

### 3. Probar FavoritoButton

```twig
{% extends 'base_react.html.twig' %}

{% block body %}
    <div {{ react_component('FavoritoButton', {
        viniloId: 1,
        isFavorito: false
    }) }}></div>
{% endblock %}
```

---

## 🐛 Solución de Problemas

### Problema: "react_component no está definido"

**Solución**: Asegúrate de que Symfony UX React está instalado:
```bash
composer require symfony/ux-react
php bin/console cache:clear
```

### Problema: "Cannot find module 'react'"

**Solución**: Instala las dependencias de JavaScript:
```bash
php bin/console importmap:install
```

### Problema: Los componentes no se renderizan

**Solución**: Verifica que los controladores están en la carpeta correcta:
```
assets/react/controllers/
├── MobileMenu.jsx
├── ToastContainer.jsx
├── CartBadge.jsx
└── ...
```

### Problema: "showToast is not a function"

**Solución**: Asegúrate de que ToastContainer está incluido:
```twig
<div {{ react_component('ToastContainer') }}></div>
```

### Problema: El badge no se actualiza

**Solución**: Verifica que CartBadge está renderizado:
```twig
<div {{ react_component('CartBadge', { initialCount: carrito_count }) }}></div>
```

---

## 📊 Verificar que Todo Funciona

### Checklist de Verificación

- [ ] `composer show symfony/ux-react` muestra el paquete instalado
- [ ] `importmap.php` incluye React y ReactDOM
- [ ] Los archivos en `assets/react/components/` existen
- [ ] Los archivos en `assets/react/controllers/` existen
- [ ] `base_react.html.twig` existe en `templates/`
- [ ] Al visitar una página con `base_react.html.twig`, no hay errores en consola
- [ ] `window.showToast('test')` funciona en la consola del navegador
- [ ] `window.updateCartBadge(5)` actualiza el badge
- [ ] Los botones de favoritos funcionan
- [ ] Los formularios del carrito se interceptan con AJAX

---

## 🎨 Personalización

### Cambiar colores

Edita los componentes en `assets/react/components/`:

```jsx
// Ejemplo: Cambiar color del toast
<div className="bg-[#1a1f23] border-l-4 border-[#e00000]">
  {/* Cambia #e00000 por tu color */}
</div>
```

### Añadir nuevos componentes

1. Crea el componente en `assets/react/components/MiComponente.jsx`
2. Crea el controlador en `assets/react/controllers/MiComponente.jsx`
3. Úsalo en Twig: `<div {{ react_component('MiComponente') }}></div>`

---

## 📚 Recursos

- [Documentación Symfony UX React](https://symfony.com/bundles/ux-react/current/index.html)
- [Documentación React](https://react.dev/)
- `GUIA_REACT.md` - Guía de uso completa
- `EJEMPLOS_USO.md` - Ejemplos prácticos
- `RESUMEN_MIGRACION.md` - Resumen de la migración

---

## 🚀 Siguiente Paso

Una vez verificado que todo funciona, puedes:

1. **Migrar gradualmente** tus plantillas a `base_react.html.twig`
2. **Usar componentes individuales** en páginas específicas
3. **Crear nuevos componentes** siguiendo el patrón establecido
4. **Personalizar** los componentes según tus necesidades

---

## 💡 Tips Finales

- **No elimines** `base.html.twig` original, mantenlo como respaldo
- **Prueba primero** en páginas no críticas
- **Usa la consola del navegador** para debugging
- **Revisa los ejemplos** en `EJEMPLOS_USO.md`
- **Consulta la documentación** cuando tengas dudas

---

**¡Listo para usar! 🎉**

Todos los componentes están creados y documentados. Solo necesitas activarlos siguiendo estos pasos.
