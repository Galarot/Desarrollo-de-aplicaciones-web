<?php

namespace App\DataFixtures;

use App\Entity\Cliente;
use App\Entity\Admin;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsuarioFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Crear 2 clientes
        $cliente1 = new Cliente();
        $cliente1->setEmail('usuario1@test.com');
        $cliente1->setNombre('Usuario Uno');
        $cliente1->setDireccion('Calle Usuario 1, Madrid');
        $cliente1->setTelefono(600111111);
        $cliente1->setRoles(['ROLE_USER']);
        $cliente1->setPassword($this->passwordHasher->hashPassword($cliente1, 'password123'));
        $cliente1->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($cliente1);

        $cliente2 = new Cliente();
        $cliente2->setEmail('usuario2@test.com');
        $cliente2->setNombre('Usuario Dos');
        $cliente2->setDireccion('Calle Usuario 2, Barcelona');
        $cliente2->setTelefono(600222222);
        $cliente2->setRoles(['ROLE_USER']);
        $cliente2->setPassword($this->passwordHasher->hashPassword($cliente2, 'password123'));
        $cliente2->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($cliente2);

        // Crear 2 administradores
        $admin1 = new Admin();
        $admin1->setEmail('admin1@test.com');
        $admin1->setNombre('Admin Uno');
        $admin1->setDireccion('Calle Admin 1, Madrid');
        $admin1->setTelefono(600333333);
        $admin1->setRoles(['ROLE_ADMIN']);
        $admin1->setPassword($this->passwordHasher->hashPassword($admin1, 'admin123'));
        $admin1->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($admin1);

        $admin2 = new Admin();
        $admin2->setEmail('admin2@test.com');
        $admin2->setNombre('Admin Dos');
        $admin2->setDireccion('Calle Admin 2, Valencia');
        $admin2->setTelefono(600444444);
        $admin2->setRoles(['ROLE_ADMIN']);
        $admin2->setPassword($this->passwordHasher->hashPassword($admin2, 'admin123'));
        $admin2->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($admin2);

        // Crear 1 super administrador
        $superAdmin = new Admin();
        $superAdmin->setEmail('superadmin@test.com');
        $superAdmin->setNombre('Super Admin');
        $superAdmin->setDireccion('Calle Super Admin, Madrid');
        $superAdmin->setTelefono(600555555);
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN']);
        $superAdmin->setPassword($this->passwordHasher->hashPassword($superAdmin, 'superadmin123'));
        $superAdmin->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($superAdmin);

        $manager->flush();
    }
}
