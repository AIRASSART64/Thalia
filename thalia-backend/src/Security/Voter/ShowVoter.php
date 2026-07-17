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
        if (!$user instanceof User) {
            return false;
        }

        $show = $subject instanceof Show ? $subject : null;

        return match ($attribute) {
            self::VIEW => $this->canView($show, $user),
            self::EDIT => $this->canEdit($show, $user),
            self::CREATE => $this->canCreate(),
            self::DELETE => $this->canDelete($show, $user),
            default => false,
        };
    }

    private function canView(?Show $show, User $user): bool
    {
        // Si aucun spectacle n'est spécifié (accès à la liste globale), 
        // n'importe quel utilisateur connecté peut y accéder.
        if ($show === null) {
            return true;
        }

        // Si un spectacle est spécifié, il doit appartenir à l'organisation de l'utilisateur
        return $show->getOrganization() === $user->getOrganization();
    }

    private function canCreate(): bool
    {
        // Utilisation propre de isGranted uniquement pour tester le rôle spécifique
        return $this->security->isGranted('ROLE_PROGRAMMATEUR'); 
    }

    private function canEdit(?Show $show, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_PROGRAMMATEUR')) {
            return false;
        }

        if ($show === null) {
            return true;
        }

        return $show->getOrganization() === $user->getOrganization();
    }

    private function canDelete(?Show $show, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_PROGRAMMATEUR')) {
            return false;
        }

        if ($show === null) {
            return true;
        }

        return $show->getOrganization() === $user->getOrganization();
    }
}