<?php

namespace App\Security\Voter;

use App\Entity\Season;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security; 
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SeasonVoter extends Voter
{
    public const VIEW = 'SEASON_VIEW';
    public const EDIT = 'SEASON_EDIT';
    public const CREATE = 'SEASON_CREATE';
    public const DELETE = 'SEASON_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::CREATE, self::DELETE])
            && ($attribute === self::VIEW || $attribute === self::CREATE || $subject instanceof Season);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $programmateur = $subject instanceof Season ? $subject : null;

        return match ($attribute) {
            self::VIEW => $this->canView($programmateur, $user),
            self::EDIT => $this->canEdit($programmateur, $user),
            self::CREATE => $this->canCreate(),
            self::DELETE => $this->canDelete($programmateur, $user),
            default => false,
        };
    }

    private function canView(?Season $season, User $user): bool
    {
        // Si aucune saison n'est spécifiée (accès à la liste globale), 
        // n'importe quel utilisateur connecté peut y accéder.
        if ($season === null) {
            return true;
        }

        // Si une saison est spécifiée, elle doit appartenir à l'organisation de l'utilisateur
        return $season->getOrganization() === $user->getOrganization();
    }

    private function canCreate(): bool
    {
        // Utilisation propre de isGranted uniquement pour tester le rôle spécifique
        return $this->security->isGranted('ROLE_PROGRAMMATEUR'); 
    }

    private function canEdit(?Season $season, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_PROGRAMMATEUR')) {
            return false;
        }

        if ($season === null) {
            return true;
        }

        return $season->getOrganization() === $user->getOrganization();
    }

    private function canDelete(?Season $season, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_PROGRAMMATEUR')) {
            return false;
        }

        if ($season === null) {
            return true;
        }

        return $season->getOrganization() === $user->getOrganization();
    }
}