<?php
require 'vendor/autoload.php';
use App\Kernel;
use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;

// arranca el entorno de symfony
(new Dotenv())->bootEnv('.env');
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');
$hasher = $container->get('security.user_password_hasher');

// busca o crea el usuario administrador
$user = $em->getRepository(User::class)->findOneBy(['username' => 'admin']);
if (!$user) {
    $user = new User();
    $user->setUsername('admin');
    $user->setEmail('useradmin@gmail.com');
    $user->setRoles(['ROLE_ADMIN']);
    $user->setPassword($hasher->hashPassword($user, 'admin123'));
    $em->persist($user);
    $em->flush();
    echo "Usuario admin creado con éxito (useradmin@gmail.com / admin123).\n";
} else {
    // lo actualiza si ya existia
    $user->setEmail('useradmin@gmail.com');
    $user->setRoles(['ROLE_ADMIN']);
    $user->setPassword($hasher->hashPassword($user, 'admin123'));
    $em->flush();
    echo "El usuario admin ya existe. Se han actualizado sus credenciales a: useradmin@gmail.com / admin123\n";
}
