<?php

namespace App\Security\Voter;

use App\Entity\Show;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security; 
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ShowVoter extends Voter
{
    public const VIEW = 'SHOW_VIEW';
    public const EDIT = 'SHOW_EDIT';
    public const CREATE = 'SHOW_CREATE';
    public const DELETE = 'SHOW_DELETE';

    // vérification des rôles proprement
    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::CREATE, self::DELETE])
            && ($attribute === self::VIEW || $attribute === self::CREATE || $subject instanceof Show);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if (!$user) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView(),
            self::EDIT => $this->canEdit(),
            self::CREATE => $this->canCreate(),
            self::DELETE => $this->canDelete(),
            default => false,
        };
    }

    private function canView(): bool
    {
        // Accessible à n'importe quel utilisateur connecté (qui a donc au moins ROLE_USER)
        return $this->security->isGranted('ROLE_USER'); 
    }

    private function canCreate(): bool
    {
        // Vérifie le rôle en prenant en compte la hiérarchie du security.yaml
        return $this->security->isGranted('ROLE_PROGRAMMATEUR'); 
    }

    private function canEdit(): bool
    {
        return $this->security->isGranted('ROLE_PROGRAMMATEUR');
    }

    private function canDelete(): bool
    {
        return $this->security->isGranted('ROLE_PROGRAMMATEUR');
    }
}