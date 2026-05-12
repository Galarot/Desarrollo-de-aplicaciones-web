# Instrucciones explícitas para subir el proyecto DBLegendle

Este documento describe paso a paso qué hacer si necesitas volver a preparar, ejecutar y subir este proyecto en otro equipo o servidor.

---

## 1. Requisitos previos

Asegúrate de tener instalados:

- PHP 8.1 o superior
- Composer
- Git (si vas a subir el proyecto a un repositorio)
- Navegador web para validar la aplicación

En Windows, también es recomendable tener configurado `scoop` o una instalación de PHP con `zip` y `openssl` habilitados.

---

## 2. Preparar el proyecto localmente

1. Abre una terminal en la carpeta raíz del proyecto:

```powershell
cd "C:\ruta\a\tu\proyecto"
```

2. Verifica que PHP esté disponible:

```powershell
php -v
```

3. Comprueba que las extensiones necesarias estén habilitadas:

```powershell
php -m | findstr /i "zip openssl intl mbstring pdo_sqlite"
```

Si alguna falta, habilítala en el `php.ini` de tu instalación PHP. En el caso de `scoop`, el archivo suele estar en:

```text
C:\Users\galoc.DESKTOP-F1QQGIG\scoop\apps\php\current\cli\php.ini
```

Y las líneas a descomentar son, por ejemplo:

```ini
extension=zip
extension=openssl
extension=mbstring
extension=intl
```

---

## 3. Instalar dependencias PHP

Ejecuta:

```powershell
php composer.phar install --no-interaction
```

Si usas Composer global, también puedes hacer:

```powershell
composer install --no-interaction
```

Esto descargará todas las dependencias y generará el autoload de PHP.

---

## 4. Configurar la base de datos SQLite

1. Crea la carpeta `var` si no existe:

```powershell
if (-not (Test-Path var)) { New-Item -ItemType Directory -Path var | Out-Null }
```

2. Asegúrate de que PHP pueda escribir en esa carpeta.

En Windows, una forma de hacerlo es usar permisos o asegurarte de que el usuario actual tenga acceso a la carpeta.

3. Verifica que la base de datos SQLite se pueda crear. El proyecto ejecuta automáticamente el script `public/setup.php` al cargar la aplicación y crea `var/app.db` con la tabla `user`.

---

## 5. Asegurar que las imágenes estén en la carpeta pública correcta

El proyecto usa rutas de recursos basadas en `public/assets/`. En este repositorio ya están incluidas las imágenes necesarias en `public/assets/`, por lo que no es necesario copiarlas de nuevo si ya existen allí.

Si llegas a desplegar desde una copia sin estos recursos, copia los archivos desde `DBLegendle/public/assets/` a `public/assets/`:

```powershell
New-Item -ItemType Directory -Path public\assets -ErrorAction SilentlyContinue | Out-Null
Copy-Item -Path DBLegendle\public\assets\* -Destination public\assets -Recurse -Force
```

Esto es necesario para que las imágenes usadas en las plantillas Twig carguen correctamente.

---

## 6. Iniciar el servidor local

Para iniciar la aplicación localmente, usa:

```powershell
php -S localhost:8000 -t public/
```

Luego abre en el navegador:

```text
http://localhost:8000
```

Si el servidor responde con `200`, la aplicación está en funcionamiento.

---

## 7. Comprobar rutas y assets

Prueba directamente una imagen para validar que el servidor sirve estáticos:

```powershell
Invoke-WebRequest -Uri http://localhost:8000/assets/artes/shallotfondo.png -UseBasicParsing
```

Si responde `200`, la ruta de recursos está bien.

---

## 8. Subir el proyecto a un repositorio Git

1. Inicializa el repo (si no existe):

```powershell
git init
```

2. Agrega los archivos y haz commit:

```powershell
git add .
git commit -m "Subida inicial del proyecto DBLegendle"
```

3. Conecta con tu repositorio remoto y sube:

```powershell
git remote add origin <URL_DEL_REPOSITORIO>
git push -u origin main
```

> Asegúrate de usar la rama correcta (`main` o `master`) según tu repositorio.

---

## 9. Pasos para desplegar en un servidor remoto

Si necesitas subirlo a un servidor de producción, sigue estos pasos básicos:

1. Copia el proyecto al servidor.
2. En el servidor, instala PHP y Composer.
3. Habilita las extensiones necesarias en `php.ini`.
4. Ejecuta `composer install --no-interaction`.
5. Crea la carpeta `var/` y ajusta permisos.
6. Copia los assets a `public/assets/` si es necesario.
7. Configura un `.env.local` con los valores de entorno adecuados.
8. Usa `php -S localhost:8000 -t public/` para pruebas locales, o configura Apache/Nginx para producción.

---

## 10. Errores comunes y soluciones

- `Failed opening required 'vendor/autoload.php'`: ejecuta `composer install`.
- `unable to open database file`: crea `var/` y ajusta permisos.
- `You must enable the openssl extension`: habilita `extension=openssl` en `php.ini`.
- `No se reconoce el comando php`: revisa tu PATH o instala PHP.

---

## 11. Notas finales

- El proyecto espera que los assets públicos estén en `public/assets/`.
- La configuración principal está en `.env` y se puede personalizar en `.env.local`.
- Siempre verifica `http://localhost:8000` después de iniciar el servidor.

---

Documentado por GitHub Copilot para que puedas repetir el proceso con claridad.
