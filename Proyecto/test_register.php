<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

(new Dotenv())->loadEnv(__DIR__ . '/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');
$hasher = $container->get('security.user_password_hasher');

$email = 'manual_test@example.com';
$username = 'manualtest';
$pass = 'Pass123!';

$existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
if ($existing) {
    echo "User already exists\n";
} else {
    $user = new User();
    $user->setEmail($email);
    $user->setUsername($username);
    $user->setPassword($hasher->hashPassword($user, $pass));
    $em->persist($user);
    $em->flush();
    echo "User created successfully\n";
}
