@echo off
REM Script de instalación para DBLegendle en Windows

echo ================================
echo Instalador DBLegendle Symfony
echo ================================

REM Verificar PHP
php --version >nul 2>&1
if errorlevel 1 (
    echo ❌ PHP no está instalado en el PATH
    echo Descárgalo de: https://www.php.net/downloads
    pause
    exit /b 1
)

echo ✓ PHP detectado
php --version | findstr /R "^PHP"

REM Verificar Composer
composer --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Composer no está instalado
    echo Descárgalo de: https://getcomposer.org/download/
    pause
    exit /b 1
)

echo ✓ Composer detectado
composer --version

REM Instalar dependencias
echo.
echo Instalando dependencias...
call composer install

if errorlevel 1 (
    echo ❌ Error al instalar dependencias
    pause
    exit /b 1
)

REM Crear carpeta var
if not exist "var" mkdir var
if not exist "var\cache" mkdir var\cache
if not exist "var\log" mkdir var\log
if not exist "var\data" mkdir var\data

REM Limpiar caché
echo.
echo Limpiando caché...
call php bin/console cache:clear

echo.
echo ================================
echo ✓ Instalación completada
echo ================================
echo.
echo Para iniciar el servidor ejecuta:
echo   php -S localhost:8000 -t public/
echo.
echo O con Symfony CLI:
echo   symfony server:start
echo.
echo Abre en tu navegador: http://localhost:8000
echo.
pause
