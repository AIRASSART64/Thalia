<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // si user existe mais pas encore activé
        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException(
                "Votre compte n'a pas encore été validé."
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        
    }
}