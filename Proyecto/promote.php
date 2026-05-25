<?php
require 'vendor/autoload.php';
use App\Kernel;
use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;

if ($argc < 2) {
    echo "Uso: php promote.php <username>\n";
    exit(1);
}

$targetUsername = $argv[1];

(new Dotenv())->bootEnv('.env');
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');

$user = $em->getRepository(User::class)->findOneBy(['username' => $targetUsername]);

if (!$user) {
    echo "Usuario '$targetUsername' no encontrado.\n";
    exit(1);
}

$roles = $user->getRoles();
if (!in_array('ROLE_ADMIN', $roles)) {
    $roles[] = 'ROLE_ADMIN';
    $user->setRoles($roles);
    $em->flush();
    echo "Usuario '$targetUsername' ahora es ADMIN.\n";
} else {
    echo "El usuario '$targetUsername' ya es ADMIN.\n";
}
