# DBLegendle - Proyecto Symfony

Bienvenido a **DBLegendle**, un juego interactivo basado en Symfony para adivinar personajes de Dragon Ball Legends.

## 📋 Requisitos previos

- **PHP 8.1+** ([Descargar](https://www.php.net/downloads))
- **Composer** ([Descargar](https://getcomposer.org/download/))
- **Node.js y npm** (opcional, para compilación de assets avanzada)

## 🚀 Instalación

### 1. Instalar dependencias PHP

```bash
composer install
```

### 2. Configurar el archivo `.env.local`

Crea un archivo `.env.local` en la raíz del proyecto (ya viene la plantilla `.env`):

```bash
# .env.local
APP_ENV=dev
APP_DEBUG=true
DATABASE_URL="sqlite:///%kernel.project_dir%/var/app.db"
```

### 3. Crear la base de datos (opcional)

```bash
php bin/console doctrine:database:create
```

### 4. Ejecutar migraciones (si es necesario)

```bash
php bin/console doctrine:migrations:migrate
```

## 🎮 Uso

### Ejecutar servidor de desarrollo

```bash
symfony server:start
```

O si no tienes Symfony CLI:

```bash
php -S localhost:8000 -t public/
```

El proyecto estará disponible en: **http://localhost:8000**

### Páginas disponibles

- **Inicio**: `http://localhost:8000/` - Juego clásico de adivinanza
- **Art Cart**: `http://localhost:8000/artcart` - Juego basado en las artes

## 🏗️ Estructura del Proyecto

```
Proyecto/
├── bin/                    # Scripts ejecutables
├── config/                 # Configuración de Symfony
├── data/                   # Archivos JSON de datos (personajes)
├── public/                 # Punto de entrada público
│   ├── index.php          # Bootstrap de la aplicación
│   └── assets/            # Recursos estáticos (imágenes)
├── src/                   # Código fuente PHP
│   ├── Controller/        # Controladores
│   ├── Entity/            # Entidades Doctrine
│   ├── Repository/        # Repositorios
│   └── Kernel.php         # Kernel de Symfony
├── templates/             # Plantillas Twig
│   ├── base.html.twig    # Plantilla base
│   └── db_legendle/      # Plantillas específicas del juego
├── assets/               # Archivos CSS/JS del cliente
├── composer.json         # Dependencias PHP
└── .env                 # Variables de entorno
```

## 🎨 Características

- **Juego Clásico**: Adivina el personaje basado en atributos (género, afinidad, rareza, etc.)
- **Art Cart**: Adivina el personaje viendo solo una parte de su arte
- **Base de datos completa**: 374+ personajes de Dragon Ball Legends
- **Interfaz visual**: Diseño moderno con Tailwind CSS
- **Animaciones**: Efectos flotantes y brillo (glow)

## 📊 Datos del Juego

### Archivo: `data/characters.json`
Contiene información de todos los personajes:
- ID único
- Nombre
- Año de lanzamiento
- URL del arte completo
- Atributos: género, afinidad, rareza, estilo, zenkai, saga, raza

### Archivo: `data/splash.json`
Contiene información para el modo Art Cart:
- ID único
- Nombre
- URL del splash art

## 🔧 Configuración del servidor

### Apache (con mod_rewrite)

Asegúrate de que `.htaccess` está en la carpeta `public/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

### Nginx

```nginx
location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

location ~ /\. {
    deny all;
}
```

## 📝 API Endpoints

### Obtener todos los personajes
```
GET /api/characters
```

### Obtener datos para Art Cart
```
GET /api/splash
```

## 🛠️ Desarrollo

### Limpiar caché
```bash
php bin/console cache:clear
```

### Ver rutas disponibles
```bash
php bin/console debug:router
```

### Ver servicios disponibles
```bash
php bin/console debug:container
```

## 📦 Dependencias principales

- **Symfony 7.0**: Framework web PHP
- **Doctrine ORM**: Mapeo objeto-relacional
- **Twig**: Motor de plantillas
- **Tailwind CSS**: Framework CSS
- **Fonts**: Edo SZ (personalizada)

## 📄 Licencia

Proyecto educativo - 2026

## 👤 Autor

Desarrollado como parte de Desarrollo de Aplicaciones Web

## 🐛 Solución de problemas

### "Command 'php' not found"
Instala PHP desde [php.net](https://www.php.net/downloads)

### "Composer not found"
Instala Composer desde [getcomposer.org](https://getcomposer.org/)

### Errores de permisos en `/var`
```bash
chmod -R 777 var/
```

### La página no carga correctamente
1. Limpia el caché: `php bin/console cache:clear`
2. Verifica que public/ sea accesible
3. Revisa que los assets estén en `public/assets/`

---

¡Disfruta jugando DBLegendle! 🎮✨
