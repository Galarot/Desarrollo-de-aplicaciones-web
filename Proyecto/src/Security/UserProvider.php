<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new \Exception("PROVIDER CALLED WITH: " . $identifier);
        $identifier = trim($identifier);
        $query = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('LOWER(u.email) = :identifier')
            ->orWhere('LOWER(u.username) = :identifier')
            ->setParameter('identifier', mb_strtolower($identifier))
            ->setMaxResults(1)
            ->getQuery();

        $user = $query->getOneOrNullResult();

        if (!$user instanceof User) {
            throw UserNotFoundException::create($identifier, User::class);
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->entityManager->getRepository(User::class)->find($user->getId()) ?: $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    // For compatibility with Symfony versions that still call this method.
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }
}
