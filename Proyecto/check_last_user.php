<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Entity\User;

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');

try {
    $user = $em->getRepository(User::class)->findOneBy([], ['id' => 'DESC']);
    if ($user) {
        echo "Ultimo usuario registrado:\n";
        echo "ID: " . $user->getId() . "\n";
        echo "Username: " . $user->getUsername() . "\n";
        echo "Email: " . $user->getEmail() . "\n";
        echo "Password (hashed): " . $user->getPassword() . "\n";
        echo "Roles: " . json_encode($user->getRoles()) . "\n";
    } else {
        echo "No hay usuarios registrados.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
