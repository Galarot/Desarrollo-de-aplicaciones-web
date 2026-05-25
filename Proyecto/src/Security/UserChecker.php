<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    // mira el ban antes del login
    public function checkPreAuth(UserInterface $user): void
    {
        $this->checkBanned($user);
    }

    // mira el ban después del login
    public function checkPostAuth(UserInterface $user): void
    {
        $this->checkBanned($user);
    }

    // comprueba si el usu está baneado
    private function checkBanned(UserInterface $user): void
    {
        if ($user instanceof User && $user->isBanned()) {
            throw new CustomUserMessageAuthenticationException('Esta cuenta esta baneada.');
        }
    }
}
