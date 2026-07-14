<?php

namespace App\Security\Voter;

use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
  
    public const VIEW_AVATAR = 'USER_VIEW_AVATAR';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // On ne supporte que l'attribut VIEW_AVATAR sur un objet de type User
        return $attribute === self::VIEW_AVATAR && $subject instanceof AppUser;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var AppUser|null $currentUser */
        $currentUser = $token->getUser();

        // Si l'utilisateur n'est pas connecté
        if (!$currentUser instanceof AppUser) {
            return false;
        }

        /** @var AppUser $targetUser */
        $targetUser = $subject;

        // Seul l'utilisateur lui-même peut voir son propre avatar privé
        return $currentUser->getId() === $targetUser->getId();
    }
}
