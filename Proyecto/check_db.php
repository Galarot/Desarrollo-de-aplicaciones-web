<?php
require 'vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$dbUrl = $_ENV['DATABASE_URL'];
echo "Checking database: $dbUrl\n";

// conecta a la bd segun la url del env
if (str_starts_with($dbUrl, 'sqlite:')) {
    $path = str_replace(['sqlite:///', 'sqlite://'], ['', ''], $dbUrl);
    if (!str_starts_with($path, '/') && !str_contains($path, ':')) {
        $path = __DIR__ . '/' . $path;
    }
    $db = new PDO('sqlite:' . $path);
} else {
    // saca los datos para conectar a mysql
    if (preg_match('/mysql:\/\/([^:]+):([^@]+)@([^:]+):(\d+)\/(.+)/', $dbUrl, $matches)) {
        $dsn = "mysql:host={$matches[3]};port={$matches[4]};dbname={$matches[5]};charset=utf8mb4";
        $db = new PDO($dsn, $matches[1], $matches[2]);
    } else {
        die("Unsupported or malformed DATABASE_URL\n");
    }
}

// lista todos los usuarios de la tabla user
$res = $db->query("SELECT id, email, username, roles, banned FROM user");
if (!$res) {
    echo "No users found or table 'user' does not exist.\n";
    exit;
}

while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
