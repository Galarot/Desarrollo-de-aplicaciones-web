<?php
require 'vendor/autoload.php';
use App\Kernel;
use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv('.env');
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');
$hasher = $container->get('security.user_password_hasher');

$user = $em->getRepository(User::class)->findOneBy(['username' => 'admin']);
if (!$user) {
    $user = new User();
    $user->setUsername('admin');
    $user->setEmail('admin@example.com');
    $user->setRoles(['ROLE_ADMIN']);
    $user->setPassword($hasher->hashPassword($user, 'admin123'));
    $em->persist($user);
    $em->flush();
    echo "Usuario admin creado con éxito.\n";
} else {
    echo "El usuario admin ya existe.\n";
}
