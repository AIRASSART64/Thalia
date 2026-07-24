<?php

namespace App\Security\Voter;

use App\Entity\Performance;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security; 
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PerformanceVoter extends Voter
{
    public const VIEW = 'PERFORMANCE_VIEW';
    public const EDIT = 'PERFORMANCE_EDIT';
    public const CREATE = 'PERFORMANCE_CREATE';
    public const DELETE = 'PERFORMANCE_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::CREATE, self::DELETE])
            && ($attribute === self::VIEW || $attribute === self::CREATE || $subject instanceof Performance);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $performance = $subject instanceof Performance ? $subject : null;

        return match ($attribute) {
            self::VIEW => $this->canView($performance, $user),
            self::EDIT => $this->canEdit($performance, $user),
            self::CREATE => $this->canCreate(),
            self::DELETE => $this->canDelete($performance, $user),
            default => false,
        };
    }

    private function canView(?Performance $performance, User $user): bool
    {
        // Si aucune reprsentation n'est spécifiée (accès à la liste globale), 
        // n'importe quel utilisateur connecté peut y accéder.
        if ($performance === null) {
            return true;
        }

        // Si une représenattion est spécifiée, elle doit appartenir à l'organisation de l'utilisateur
        return $performance->getOrganization()->getId() === $user->getOrganization()->getId();
    }

    private function canCreate(): bool
    {
        // Utilisation de isGranted uniquement pour tester le rôle spécifique
        return $this->security->isGranted('ROLE_PROGRAMMATEUR'); 
    }

    private function canEdit(?Performance $performance, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_PROGRAMMATEUR')) {
            return false;
        }

        if ($performance === null) {
            return true;
        }

        return $performance->getOrganization() === $user->getOrganization();
    }

    private function canDelete(?Performance $performance, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_PROGRAMMATEUR')) {
            return false;
        }

        if ($performance === null) {
            return true;
        }

        return $performance->getOrganization() === $user->getOrganization();
    }
}