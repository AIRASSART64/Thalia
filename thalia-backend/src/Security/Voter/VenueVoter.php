<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Venue;
use Symfony\Bundle\SecurityBundle\Security; 
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VenueVoter extends Voter
{
    public const VIEW = 'VENUE_VIEW';
    public const EDIT = 'VENUE_EDIT';
    public const CREATE = 'VENUE_CREATE';
    public const DELETE = 'VENUE_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::CREATE, self::DELETE])) {
            return false;
        }

        // Pour VIEW, EDIT et DELETE, le sujet DOIT être une instance de Venue
        if (in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return $subject instanceof Venue;
        }

        return true; // Uniquement pour CREATE 
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        $venue = $subject instanceof Venue ? $subject : null;
        return match ($attribute) {
            self::VIEW => $this->canView($venue, $user),
            self::CREATE => $this->canCreate(),
            self::EDIT => $this->canEdit($venue, $user),
            self::DELETE => $this->canDelete($venue, $user),
            default => false,
        };
    }

    /**
     * Vérifie si l'utilisateur possède au moins un des rôles autorisés
     */
    private function hasRequiredRole(): bool
    {
        return $this->security->isGranted('ROLE_TECHNICIEN') 
            || $this->security->isGranted('ROLE_PROGRAMMATEUR')
            || $this->security->isGranted('ROLE_FINANCIER'); 
    }

    private function canView(Venue $venue, User $user): bool
    {
        // L'utilisateur doit avoir un rôle autorisé (Technicien, Programmateur ou Financier)
        if (!$this->hasRequiredRole()) {
            return false;
        }

        // La salle doit appartenir à la même organisation que l'utilisateur
        return $venue->getOrganization() === $user->getOrganization();
    }

    private function canCreate(): bool
    {
        return $this->security->isGranted('ROLE_TECHNICIEN');
    }

    private function canEdit(Venue $venue, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_TECHNICIEN')) {
            return false;
        }

        if ($venue === null) {
            return true;
        }

        return $venue->getOrganization() === $user->getOrganization();
    }

    private function canDelete(Venue $venue, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_TECHNICIEN')) {
            return false;
        }
        if($venue === null) {
            return true;
        }

        return $venue->getOrganization() === $user->getOrganization();
    }
}