#!/bin/sh
set -eu

cd /var/www/html

mkdir -p var/cache var/log

if [ ! -f vendor/autoload.php ]; then
  echo "Installing Composer dependencies..."
  composer install --prefer-dist --no-interaction --no-progress
fi

if [ "${APP_ENV:-dev}" != "prod" ]; then
  composer dump-autoload --no-interaction >/dev/null
fi

echo "Waiting for database..."
php -r '
$url = getenv("DATABASE_URL") ?: "";
$parts = parse_url($url);
if (($parts["scheme"] ?? "") !== "mysql") {
    exit(0);
}
$host = $parts["host"] ?? "db";
$port = (int)($parts["port"] ?? 3306);
$deadline = time() + 60;
do {
    $socket = @fsockopen($host, $port, $errno, $errstr, 2);
    if ($socket) {
        fclose($socket);
        exit(0);
    }
    sleep(2);
} while (time() < $deadline);
fwrite(STDERR, "Database is not reachable at {$host}:{$port}\n");
exit(1);
'

echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

exec "$@"
