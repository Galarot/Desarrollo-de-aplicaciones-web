# Estructura del Proyecto DBLegendle Symfony

## 📁 Directorios Principales

### `/bin`
Contiene scripts ejecutables de Symfony:
- `console` - CLI de Symfony para ejecutar comandos

### `/config`
Configuración de la aplicación Symfony:
- `packages/` - Archivos YAML de configuración de bundles
  - `framework.yaml` - Configuración principal de Symfony
  - `doctrine.yaml` - Configuración de la BD (Doctrine ORM)
  - `twig.yaml` - Configuración del motor de plantillas
  - `security.yaml` - Configuración de seguridad
  - `dev/` y `prod/` - Configuraciones por entorno
- `services.yaml` - Definición de servicios
- `routes.yaml` - Rutas de la aplicación

### `/data`
Archivos de datos JSON estáticos:
- `characters.json` - Base de datos de personajes del juego clásico
- `splash.json` - Base de datos de artes para Art Cart
- `plant.txt` - Datos adicionales

### `/public`
Directorio público accesible desde el navegador:
- `index.php` - Punto de entrada de la aplicación
- `.htaccess` - Configuración para Apache
- `assets/` - Recursos estáticos:
  - `multimedia/` - Imágenes y recursos visuales
    - `logo.png` - Logo de la aplicación
    - `arts/` - Artes de los personajes
  - `artes/` - Fondos y decoraciones

### `/src`
Código fuente PHP de la aplicación:
- `Kernel.php` - Kernel de Symfony (configurador principal)
- `Controller/` - Controladores
  - `DBLegendleController.php` - Controlador principal
- `Entity/` - Entidades Doctrine (modelos de BD)
- `Repository/` - Repositorios (acceso a datos)
- `Service/` - Servicios (lógica de negocio)

### `/templates`
Plantillas Twig (vistas):
- `base.html.twig` - Plantilla base con estructura común
- `db_legendle/` - Plantillas del juego
  - `index.html.twig` - Página principal (juego clásico)
  - `artcart.html.twig` - Página del Art Cart

### `/assets`
Recursos del cliente (CSS, JS, imágenes):
- `js/` - Archivos JavaScript
- `styles/` - Archivos CSS
- `controllers/` - Controladores Stimulus (si se usa)

### `/tests`
Pruebas unitarias e integración:
- Estructura para tests

### `/var`
Archivos generados por la aplicación (NO INCLUIR EN GIT):
- `cache/` - Caché de Symfony
- `log/` - Archivos de log
- `data/` - Datos SQLite

### `/migrations`
Migraciones de base de datos con Doctrine

## 📄 Archivos Raíz Importantes

### Configuración
- `.env` - Variables de entorno (plantilla)
- `.env.local` - Variables de entorno locales (crear localmente)
- `.env.example` - Ejemplo de configuración
- `.gitignore` - Archivos a ignorar en Git

### Dependencias
- `composer.json` - Dependencias PHP
- `composer.lock` - Versiones específicas instaladas
- `symfony.lock` - Lock file de Symfony

### Documentación
- `README.md` - Guía principal
- `INSTALACION.md` - Pasos de instalación
- `ESTRUCTURA.md` - Este archivo

### Instalación
- `install.bat` - Script de instalación para Windows
- `install.sh` - Script de instalación para Linux/Mac

### Docker
- `docker-compose.yml` - Configuración de contenedores
- `docker/nginx.conf` - Configuración de Nginx

### Otros
- `.editorconfig` - Configuración de editor
- `public/.htaccess` - Configuración para Apache

## 🔄 Flujo de Peticiones

```
Usuario accede a: http://localhost:8000/
                ↓
    public/index.php (Bootstrap)
                ↓
        Kernel.php (Inicializa Symfony)
                ↓
    Router busca ruta coincidente
                ↓
    DBLegendleController ejecuta método
                ↓
    Renderiza template Twig
                ↓
    HTML al navegador
```

## 📊 Base de Datos

### SQLite (Por defecto)
- Ubicación: `var/app.db`
- No requiere instalación extra
- Ideal para desarrollo

### Cambiar a MySQL/PostgreSQL
Editar `DATABASE_URL` en `.env.local`:
```bash
# MySQL
DATABASE_URL="mysql://user:password@localhost:3306/dblegend"

# PostgreSQL
DATABASE_URL="postgresql://user:password@localhost:5432/dblegend"
```

## 🎯 Rutas Disponibles

| Ruta | Método | Controlador | Descripción |
|------|--------|-----------|------------|
| `/` | GET | DBLegendleController::index | Página principal |
| `/artcart` | GET | DBLegendleController::artcart | Art Cart |
| `/api/characters` | GET | DBLegendleController::getCharacters | API de personajes |
| `/api/splash` | GET | DBLegendleController::getSplash | API de artes |

## 🔐 Seguridad

- `config/packages/security.yaml` - Configuración de seguridad
- Actualmente sin autenticación (desarrollo)
- Las rutas de API son públicas

## 📦 Dependencias Principales

```json
{
  "symfony/framework-bundle": "7.0",
  "symfony/twig-bundle": "7.0",
  "doctrine/orm": "2.15",
  "symfony/console": "7.0"
}
```

## 🛠️ Comandos Útiles

```bash
# Listar rutas
php bin/console debug:router

# Ver servicios disponibles
php bin/console debug:container

# Limpiar caché
php bin/console cache:clear

# Crear BD
php bin/console doctrine:database:create

# Ver propiedades de configuración
php bin/console debug:config framework
```

## 📈 Estructura de Datos JSON

### characters.json
```json
[
  {
    "id": 1,
    "nombre": "Goku",
    "anio": 2018,
    "art_cart_url": "./public/assets/multimedia/arts/gokuh.png",
    "atributos": {
      "genero": "Hombre",
      "afinidad": "Amarillo",
      "rareza": "Hero",
      "estilo": "Energia",
      "zenkai": "Sin zenkai",
      "saga": "Saiyan Saga",
      "raza": "Saiyan"
    }
  }
]
```

### splash.json
```json
[
  {
    "id": 1,
    "nombre": "Goku",
    "art_url": "./public/assets/multimedia/splash/goku.png"
  }
]
```

## 🚀 Próximas Extensiones

1. **Autenticación de usuarios**
   - Crear Entity User
   - Configurar Security

2. **Sistema de puntuaciones**
   - Crear Entity Score
   - Guardar records en BD

3. **Más modos de juego**
   - Modo cooperativo
   - Modo contra tiempo
   - Dificultades

4. **API REST completa**
   - CRUD de personajes
   - Sistema de filtros
   - Paginación

5. **Frontend moderno**
   - Componentes Stimulus
   - Asset Mapper
   - Webpack Encore

---

Última actualización: 2026-04-24
