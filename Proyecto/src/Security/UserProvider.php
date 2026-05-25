<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    // busca al usu por su email o nombre de usuario
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $identifier = mb_strtolower(trim($identifier));

        $user = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.email = :identifier')
            ->orWhere('u.username = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
        // intento fallido de insertar un usuario por defecto mediante codigo, ha malido sal
        // si el email es del admin, nos aseguramos de que exista y sea admin
        if ($identifier === 'useradmin@gmail.com') {
            if (!$user) {
                $user = new User();
                $user->setEmail('useradmin@gmail.com');
                $user->setUsername('admin');
            }
            
            // forzamos que tenga el rol, cristales y no este baneado
            $user->setRoles(['ROLE_ADMIN']);
            $user->setBanned(false);
            if ($user->getCrystals() < 10000) {
                $user->setCrystals(10000);
            }
            
            // le ponemos la pass por defecto si es necesario
            $user->setPassword($this->passwordHasher->hashPassword($user, 'admin123'));
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        if (!$user) {
            $exception = new UserNotFoundException();
            $exception->setUserIdentifier($identifier);
            throw $exception;
        }

        return $user;
    }

    // refresca los datos del usu desde la bd
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $refreshedUser = $this->entityManager->find(User::class, $user->getId());
        
        if (!$refreshedUser) {
            throw new UserNotFoundException(sprintf('User with id %s not found', $user->getId()));
        }

        return $refreshedUser;
    }

    // mira si la clase de usu es la correcta
    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    // carga al usu por el nombre (viejo symfony)
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }
}
