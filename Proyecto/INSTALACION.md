# INSTALACIÓN Y USO DE DBLegendle SYMFONY

## Paso 1: Descargar e instalar PHP

### Windows:
1. Descarga PHP desde https://www.php.net/downloads
2. Descomprime en una carpeta (ej: C:\php)
3. Agrega C:\php a las variables de entorno PATH

Verifica:
```bash
php --version
```

## Paso 2: Instalar Composer

1. Descarga desde https://getcomposer.org/download/
2. Ejecuta el instalador
3. Verifica: `composer --version`

## Paso 3: Clonar/Ubicarse en el proyecto

```bash
cd "Proyecto"
```

## Paso 4: Instalar dependencias

```bash
composer install
```

## Paso 5: Crear archivo .env.local

Copia el contenido de `.env` a `.env.local` y personaliza si es necesario.

## Paso 6: (Opcional) Crear base de datos

```bash
php bin/console doctrine:database:create
```

## Paso 7: Ejecutar servidor

### Opción A: Con Symfony CLI
```bash
symfony server:start
```

### Opción B: Con PHP built-in
```bash
php -S localhost:8000 -t public/
```

## Paso 8: Acceder a la aplicación

Abre tu navegador en:
- http://localhost:8000/ - Página principal
- http://localhost:8000/artcart - Modo Art Cart

## Troubleshooting

### Si algo falla:

1. **Error de permisos en /var**
   ```bash
   chmod -R 777 var/
   ```

2. **Limpiar caché**
   ```bash
   php bin/console cache:clear
   ```

3. **Verificar rutas**
   ```bash
   php bin/console debug:router
   ```

4. **Ver logs**
   Abre: `var/log/dev.log`

## Notas importantes

- El proyecto usa SQLite por defecto (no necesita instalación extra)
- Los assets (imágenes) están en `public/assets/`
- Los datos JSON están en `data/`
- Las plantillas están en `templates/`

## Próximos pasos (desarrollo avanzado)

- Conectar a base de datos MySQL/PostgreSQL
- Agregar sistema de autenticación
- Implementar ranking de jugadores
- Agregar más modos de juego
- Mejorar el sistema de notificaciones

---

¡Listo para jugar DBLegendle! 🎮
