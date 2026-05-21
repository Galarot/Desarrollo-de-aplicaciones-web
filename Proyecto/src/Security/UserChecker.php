<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        $this->checkBanned($user);
    }

    public function checkPostAuth(UserInterface $user): void
    {
        $this->checkBanned($user);
    }

    private function checkBanned(UserInterface $user): void
    {
        if ($user instanceof User && $user->isBanned()) {
            throw new CustomUserMessageAuthenticationException('Esta cuenta esta baneada.');
        }
    }
}
