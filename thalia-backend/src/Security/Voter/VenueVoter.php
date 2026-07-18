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

        if (in_array($attribute, [self::EDIT, self::DELETE])) {
            return $subject instanceof Venue;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::CREATE => $this->canCreate(),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    private function hasRequiredRole(): bool
    {
    
        return $this->security->isGranted('ROLE_TECHNICIEN') 
            || $this->security->isGranted('ROLE_PROGRAMMATEUR');
    }

    private function canView(mixed $venue, User $user): bool
    {
        
        if (!$venue instanceof Venue) {
            return true;
        }
        return $venue->getOrganization() === $user->getOrganization();
    }

    private function canCreate(): bool
    {
        return $this->hasRequiredRole();
    }

    private function canEdit(Venue $venue, User $user): bool
    {
        if (!$this->hasRequiredRole()) {
            return false;
        }

        return $venue->getOrganization() === $user->getOrganization();
    }

    private function canDelete(Venue $venue, User $user): bool
    {
        if (!$this->hasRequiredRole()) {
            return false;
        }

        return $venue->getOrganization() === $user->getOrganization();
    }
}