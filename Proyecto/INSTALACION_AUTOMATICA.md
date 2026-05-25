# 🚀 INSTALACIÓN AUTOMÁTICA - DBLegendle

> **Instrucciones para IA/Script de instalación automática**
> 
> Este documento contiene TODOS los comandos necesarios para instalar y ejecutar el proyecto DBLegendle desde cero en un equipo nuevo.

---

## 📋 REQUISITOS MÍNIMOS

- **Sistema Operativo**: Windows 10/11, Linux (Ubuntu 20.04+), macOS 12+
- **RAM**: 4GB mínimo (8GB recomendado)
- **Disco**: 2GB de espacio libre
- **Conexión a Internet**: Para descargar dependencias

---

## 🔧 PASO 1: INSTALAR HERRAMIENTAS BASE

### Windows (PowerShell como Administrador):
```powershell
# Instalar Git
winget install --id Git.Git -e --source winget

# Instalar Docker Desktop
winget install --id Docker.DockerDesktop -e --source winget

# Instalar PHP 8.1 (opcional, si no usas Docker)
winget install --id PHP.PHP.8.1 -e --source winget

# Reiniciar después de instalar Docker
Restart-Computer -Force
```

### Linux (Ubuntu/Debian):
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Git
sudo apt install -y git

# Instalar Docker
sudo apt install -y docker.io docker-compose

# Iniciar Docker
sudo systemctl start docker
sudo systemctl enable docker

# Instalar PHP 8.1 y extensiones (opcional, si no usas Docker)
sudo apt install -y php8.1 php8.1-cli php8.1-mysql php8.1-sqlite3 php8.1-intl php8.1-zip php8.1-xml php8.1-mbstring

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### macOS:
```bash
# Instalar Homebrew (si no está instalado)
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Instalar Git
brew install git

# Instalar Docker Desktop
brew install --cask docker

# Instalar PHP 8.1 (opcional, si no usas Docker)
brew install php@8.1

# Instalar Composer
brew install composer
```

---

## 📦 PASO 2: CLONAR REPOSITORIO

```bash
# Navegar a la carpeta donde quieras el proyecto
cd ~/Documents  # o la carpeta que prefieras

# Clonar el repositorio
git clone https://github.com/galarot/Desarrollo-de-aplicaciones-web.git

# Entrar en la carpeta del proyecto
cd Desarrollo-de-aplicaciones-web/Proyecto
```

---

## 🐳 PASO 3: LEVANTAR CON DOCKER (RECOMENDADO)

```bash
# Verificar que Docker está corriendo
docker --version
docker compose version

# Construir y levantar contenedores
docker compose up --build -d

# Esperar 30 segundos a que los servicios inicien
sleep 30

# Ver estado de contenedores
docker compose ps

# Ejecutar migraciones de base de datos
docker compose exec app php bin/console doctrine:migrations:migrate

# Cargar datos de prueba (fixtures)
docker compose exec app php bin/console doctrine:fixtures:load --no-interaction

# Ver logs (opcional, para verificar que no hay errores)
docker compose logs -f --tail=50
```

---

## 🖥️ PASO 4: INSTALACIÓN MANUAL (SIN DOCKER)

```bash
# 1. Instalar dependencias PHP con Composer
composer install --no-interaction

# 2. Crear archivo .env.local (copiar de .env)
cp .env .env.local

# 3. Crear carpeta var/ y dar permisos
mkdir -p var
chmod -R 775 var/

# 4. Crear base de datos SQLite
php bin/console doctrine:database:create

# 5. Ejecutar migraciones
php bin/console doctrine:migrations:migrate

# 6. Cargar fixtures (datos de prueba)
php bin/console doctrine:fixtures:load --no-interaction

# 7. Limpiar caché
php bin/console cache:clear
```

---

## 🌐 PASO 5: INICIAR SERVIDOR WEB

### Opción A: Docker (ya está corriendo)
```bash
# El servidor Nginx ya está activo en puerto 8000
# Solo abre el navegador en: http://localhost:8000
```

### Opción B: PHP Built-in Server
```bash
# Iniciar servidor PHP
php -S localhost:8000 -t public/

# El servidor quedará corriendo en segundo plano
# Para detenerlo: Ctrl+C
```

### Opción C: Symfony CLI
```bash
# Instalar Symfony CLI (si no está instalada)
# Windows: winget install Symfony.SymfonyCLI
# Linux/Mac: curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bash && sudo apt install symfony-cli

# Iniciar servidor Symfony
symfony server:start

# El servidor quedará corriendo
# Para detenerlo: symfony server:stop
```

---

## ✅ PASO 6: VERIFICAR INSTALACIÓN

```bash
# Verificar que la aplicación responde
curl -I http://localhost:8000

# Debería devolver: HTTP/1.1 200 OK

# Probar API de personajes
curl http://localhost:8000/api/characters | head -20

# Verificar base de datos
docker compose exec app php bin/console doctrine:database:run-sql "SHOW TABLES"
# O si usas SQLite:
# sqlite3 var/app.db ".tables"

# Ver logs de errores (si los hay)
docker compose logs app --tail=50
# O si usas PHP directo:
# tail -f var/log/dev.log
```

---

## 🎮 PASO 7: ACCEDER A LA APLICACIÓN

1. **Abrir navegador** en: `http://localhost:8000`
2. **Deberías ver** la página principal del juego DBLegendle
3. **Probar login** con usuarios de prueba:
   - Admin: `admin@dblegends.com` / `admin123`
   - Usuario: `user@dblegends.com` / `user123`
4. **Acceder al admin**: `http://localhost:8000/admin/users`

---

## 🛠️ COMANDOS DE MANTENIMIENTO

```bash
# Detener aplicación Docker
docker compose down

# Reiniciar aplicación Docker
docker compose restart

# Ver logs en tiempo real
docker compose logs -f

# Limpiar caché
docker compose exec app php bin/console cache:clear

# Actualizar dependencias
docker compose exec app composer update

# Crear nueva migración (después de cambiar entidades)
docker compose exec app php bin/console doctrine:migrations:diff

# Ejecutar migraciones
docker compose exec app php bin/console doctrine:migrations:migrate

# Backup de base de datos SQLite
cp var/app.db var/app.db.backup.$(date +%Y%m%d)

# Restaurar backup
cp var/app.db.backup.YYYYMMDD var/app.db
```

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Error: "Cannot connect to the Docker daemon"
```bash
# Windows: Reiniciar Docker Desktop
# Linux:
sudo systemctl start docker
sudo systemctl enable docker

# Verificar que Docker corre:
docker ps
```

### Error: "Permission denied" en var/
```bash
# Linux/Mac:
sudo chown -R $USER: var/
sudo chmod -R 775 var/

# Windows (PowerShell como admin):
icacls var /grant %USERNAME%:(OI)(CI)F /T
```

### Error: "Extension pdo_mysql not found"
```bash
# Habilitar en php.ini:
# Windows: C:\php\php.ini
# Linux: /etc/php/8.1/cli/php.ini
# Descomentar línea: extension=pdo_mysql
# Reiniciar servidor web
```

### Error: "Database does not exist"
```bash
# Crear manualmente:
docker compose exec app php bin/console doctrine:database:create

# O si usas MySQL local:
# mysql -u root -p -e "CREATE DATABASE dblegends;"
```

### Error: Puerto 8000 ya en uso
```bash
# Matar proceso en puerto 8000:
# Windows:
netstat -ano | findstr :8000
taskkill /PID <PID> /F

# Linux/Mac:
lsof -ti:8000 | xargs kill -9

# O cambiar puerto en docker-compose.yml:
# ports:
#   - "8001:80"  # Cambiar 8000 por 8001
```

---

## 📊 DATOS INCLUIDOS POR DEFECTO

Después de ejecutar `doctrine:fixtures:load`, tendrás:

### Usuarios:
- **Admin**: admin@dblegends.com / admin123
- **Usuario**: user@dblegends.com / user123

### Personajes:
- 374+ personajes en `data/characters.json`
- 28 splash arts en `data/splash.json`
- Todos con atributos completos (género, afinidad, rareza, estilo, zenkai, saga, raza)

### Configuración:
- Cristales iniciales: 0
- Personajes poseídos: []
- Progreso diario: vacío

---

## 🔄 ACTUALIZAR PROYECTO

```bash
# 1. Navegar a la carpeta del proyecto
cd Desarrollo-de-aplicaciones-web/Proyecto

# 2. Actualizar código (si hay cambios en el repositorio)
git pull origin main

# 3. Actualizar dependencias
docker compose exec app composer install --no-interaction

# 4. Ejecutar nuevas migraciones (si las hay)
docker compose exec app php bin/console doctrine:migrations:migrate

# 5. Limpiar caché
docker compose exec app php bin/console cache:clear

# 6. Reiniciar contenedores
docker compose restart
```

---

## 📝 COMANDOS RÁPIDOS (COPY-PASTE)

### Instalación completa en un comando (Linux/Mac):
```bash
sudo apt update && sudo apt install -y git docker.io docker-compose && sudo systemctl start docker && sudo systemctl enable docker && git clone https://github.com/galarot/Desarrollo-de-aplicaciones-web.git && cd Desarrollo-de-aplicaciones-web/Proyecto && docker compose up --build -d && sleep 30 && docker compose exec app php bin/console doctrine:migrations:migrate && docker compose exec app php bin/console doctrine:fixtures:load --no-interaction && echo "✅ Instalación completada. Abre http://localhost:8000"
```

### Instalación completa en un comando (Windows PowerShell):
```powershell
winget install --id Git.Git -e --source winget; winget install --id Docker.DockerDesktop -e --source winget; Restart-Computer -Force; Start-Sleep -Seconds 30; cd ~; git clone https://github.com/galarot/Desarrollo-de-aplicaciones-web.git; cd Desarrollo-de-aplicaciones-web\Proyecto; docker compose up --build -d; Start-Sleep -Seconds 30; docker compose exec app php bin/console doctrine:migrations:migrate; docker compose exec app php bin/console doctrine:fixtures:load --no-interaction; Write-Host "✅ Instalación completada. Abre http://localhost:8000" -ForegroundColor Green
```

---

## 🎯 VERIFICACIÓN FINAL

Ejecuta estos comandos para verificar que todo funciona:

```bash
# 1. Verificar contenedores activos
docker compose ps

# 2. Verificar aplicación web
curl -I http://localhost:8000

# 3. Verificar API
curl -s http://localhost:8000/api/characters | head -5

# 4. Verificar base de datos
docker compose exec app php bin/console doctrine:database:run-sql "SELECT COUNT(*) FROM user"

# 5. Ver logs (presiona Ctrl+C para salir)
docker compose logs -f --tail=10
```

**Si todos los comandos anteriores funcionan correctamente, ¡la instalación fue exitosa!** ✅

---

## 📞 SOPORTE

- **Documentación completa**: `/README.md`
- **Estructura del proyecto**: `/ESTRUCTURA.md`
- **Especificaciones técnicas**: `/Especificación Técnica.md`
- **Problemas comunes**: Revisar `/var/log/dev.log`

---

**¡Listo! Tu proyecto DBLegendle está instalado y funcionando.** 🎮✨

**URL de acceso**: http://localhost:8000  
**Usuario admin**: admin@dblegends.com / admin123  
**Panel admin**: http://localhost:8000/admin/users