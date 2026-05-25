<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crea o actualiza el usuario administrador por defecto',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repo = $this->entityManager->getRepository(User::class);
        $user = $repo->findOneBy(['username' => 'admin']);

        if (!$user) {
            $user = new User();
            $user->setUsername('admin');
            $user->setEmail('useradmin@gmail.com');
            $io->info('Creando nuevo usuario admin...');
        } else {
            $io->info('Actualizando usuario admin existente...');
        }

        $user->setEmail('useradmin@gmail.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setBanned(false);
        $user->setCrystals(10000);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Usuario admin configurado con éxito:');
        $io->table(
            ['Usuario', 'Email', 'Password'],
            [['admin', 'useradmin@gmail.com', 'admin123']]
        );

        return Command::SUCCESS;
    }
}
