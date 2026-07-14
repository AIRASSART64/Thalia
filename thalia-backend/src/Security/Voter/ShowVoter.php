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
        if (!$user) {
            return false;
        }

        // On récupère le spectacle s'il fait partie du sujet (subject)
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
        // Vérification des droits du user
        if (!$this->security->isGranted('ROLE_USER')) {
            return false;
        }

        // Les users connectés ne peuvent afficher que les spectacles de leur organization
        if ($show !== null) {
            return $show->getOrganization() === $user->getOrganization();
        }

        return true;
    }

    private function canCreate(): bool
    {
        // Vérification du rôle
        return $this->security->isGranted('ROLE_PROGRAMMATEUR'); 
    }

    private function canEdit(?Show $show, User $user): bool
    {
        // Vérification du rôle
        if (!$this->security->isGranted('ROLE_PROGRAMMATEUR')) {
            return false;
        }

        // Vérification de l'organizattion
        if ($show !== null) {
            return $show->getOrganization() === $user->getOrganization();
        }

        return true;
    }

    private function canDelete(?Show $show, User $user): bool
    {
        // L'utilisateur doit être programmateur
        if (!$this->security->isGranted('ROLE_PROGRAMMATEUR')) {
            return false;
        }

        // Le spectacle doit appartenir à son organisation
        if ($show !== null) {
            return $show->getOrganization() === $user->getOrganization();
        }

        return true;
    }
}