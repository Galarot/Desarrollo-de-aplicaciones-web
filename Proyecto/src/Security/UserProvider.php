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
        $identifier = trim($identifier);
        
        // Intentar primero con Doctrine (MySQL en Docker)
        try {
            $query = $this->entityManager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('LOWER(u.email) = :identifier')
                ->orWhere('LOWER(u.username) = :identifier')
                ->setParameter('identifier', mb_strtolower($identifier))
                ->setMaxResults(1)
                ->getQuery();

            $user = $query->getOneOrNullResult();
            if ($user instanceof User) {
                return $user;
            }
        } catch (\Exception $e) {
            // Si falla Doctrine, intentamos el fallback manual
        }

        // Fallback manual con PDO (usando DATABASE_URL si está disponible)
        try {
            $dbUrl = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? '';
            if (str_starts_with($dbUrl, 'sqlite:')) {
                $path = str_replace('sqlite:///', '', $dbUrl);
                $path = str_replace('%kernel.project_dir%', dirname(__DIR__, 2), $path);
                $db = new \PDO('sqlite:' . $path);
            } elseif (str_starts_with($dbUrl, 'mysql:')) {
                // Parse simple para mysql://user:pass@host:port/db (ignorando query params)
                $matches = [];
                if (preg_match('/mysql:\/\/([^:]+):([^@]+)@([^:]+):(\d+)\/([^?]+)/', $dbUrl, $matches)) {
                    $dsn = "mysql:host={$matches[3]};port={$matches[4]};dbname={$matches[5]}";
                    $db = new \PDO($dsn, $matches[1], $matches[2]);
                }
            } else {
                // Fallback por defecto si no se puede parsear
                $path = dirname(__DIR__, 2) . '/var/app.db';
                $db = new \PDO('sqlite:' . $path);
            }

            if (isset($db)) {
                $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                // Usamos backticks para compatibilidad (especialmente MySQL)
                $stmt = $db->prepare('SELECT * FROM `user` WHERE LOWER(email) = :id OR LOWER(username) = :id LIMIT 1');
                $stmt->execute(['id' => mb_strtolower($identifier)]);
                $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($userData) {
                    $user = new User();
                    $user->setEmail($userData['email']);
                    $user->setUsername($userData['username']);
                    $user->setPassword($userData['password']);
                    
                    // Asegurar que los roles sean un array
                    $roles = $userData['roles'];
                    if (is_string($roles)) {
                        $roles = json_decode($roles, true);
                    }
                    $user->setRoles(is_array($roles) ? $roles : []);
                    
                    $reflection = new \ReflectionClass(User::class);
                    $property = $reflection->getProperty('id');
                    $property->setAccessible(true);
                    $property->setValue($user, (int)$userData['id']);
                    
                    return $user;
                }
            }
        } catch (\Exception $e) {
        }

        $exception = new UserNotFoundException();
        $exception->setUserIdentifier($identifier);
        throw $exception;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        // Intentar primero con Doctrine
        try {
            $refreshed = $this->entityManager->getRepository(User::class)->find($user->getId());
            if ($refreshed) {
                return $refreshed;
            }
        } catch (\Exception $e) {}

        // Fallback manual para refresh
        try {
            $dbUrl = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? '';
            if (str_starts_with($dbUrl, 'sqlite:')) {
                $path = str_replace('sqlite:///', '', $dbUrl);
                $path = str_replace('%kernel.project_dir%', dirname(__DIR__, 2), $path);
                $db = new \PDO('sqlite:' . $path);
            } elseif (str_starts_with($dbUrl, 'mysql:')) {
                $matches = [];
                if (preg_match('/mysql:\/\/([^:]+):([^@]+)@([^:]+):(\d+)\/([^?]+)/', $dbUrl, $matches)) {
                    $dsn = "mysql:host={$matches[3]};port={$matches[4]};dbname={$matches[5]}";
                    $db = new \PDO($dsn, $matches[1], $matches[2]);
                }
            }

            if (isset($db)) {
                $stmt = $db->prepare('SELECT * FROM `user` WHERE id = :id');
                $stmt->execute(['id' => $user->getId()]);
                $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($userData) {
                    $refreshedUser = new User();
                    $refreshedUser->setEmail($userData['email']);
                    $refreshedUser->setUsername($userData['username']);
                    $refreshedUser->setPassword($userData['password']);
                    
                    $roles = $userData['roles'];
                    if (is_string($roles)) {
                        $roles = json_decode($roles, true);
                    }
                    $refreshedUser->setRoles(is_array($roles) ? $roles : []);
                    
                    $reflection = new \ReflectionClass(User::class);
                    $property = $reflection->getProperty('id');
                    $property->setAccessible(true);
                    $property->setValue($refreshedUser, (int)$userData['id']);
                    return $refreshedUser;
                }
            }
        } catch (\Exception $e) {}

        return $user;
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
