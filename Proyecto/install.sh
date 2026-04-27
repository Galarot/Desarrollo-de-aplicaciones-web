#!/bin/bash

# Script de instalación para DBLegendle en Linux/Mac

echo "================================"
echo "Instalador DBLegendle Symfony"
echo "================================"

# Verificar PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP no está instalado. Por favor, instálalo primero."
    exit 1
fi

echo "✓ PHP detectado: $(php --version | head -n1)"

# Verificar Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer no está instalado."
    echo "Descárgalo de: https://getcomposer.org/download/"
    exit 1
fi

echo "✓ Composer detectado: $(composer --version | head -n1)"

# Instalar dependencias
echo ""
echo "Instalando dependencias..."
composer install

if [ $? -ne 0 ]; then
    echo "❌ Error al instalar dependencias"
    exit 1
fi

# Crear .env.local si no existe
if [ ! -f .env.local ]; then
    echo ""
    echo "Creando .env.local..."
    cp .env .env.local
    sed -i 's/APP_ENV=.*/APP_ENV=dev/' .env.local
    sed -i 's/APP_DEBUG=.*/APP_DEBUG=true/' .env.local
fi

# Crear carpeta var
mkdir -p var/cache var/log var/data

# Permisos
chmod -R 777 var/
chmod -R 777 public/

# Limpiar caché
php bin/console cache:clear

echo ""
echo "================================"
echo "✓ Instalación completada"
echo "================================"
echo ""
echo "Para iniciar el servidor ejecuta:"
echo "  php -S localhost:8000 -t public/"
echo ""
echo "O con Symfony CLI:"
echo "  symfony server:start"
echo ""
echo "Abre: http://localhost:8000"
