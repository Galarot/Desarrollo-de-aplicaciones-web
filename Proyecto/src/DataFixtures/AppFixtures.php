<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    // carga los datos de prueba en la bd
    public function load(ObjectManager $manager): void
    {
        // crea el admin por defecto
        $admin = new User();
        $admin->setEmail('useradmin@gmail.com');
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        
        $manager->persist($admin);

        // crea un usuario de prueba normal
        $user = new User();
        $user->setEmail('user@dblegends.com');
        $user->setUsername('testuser');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        
        $manager->persist($user);

        $manager->flush();
    }
}
