<?php

namespace App\Security\Voter;

use App\Entity\Financial;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security; 
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FinancialVoter extends Voter
{
    public const VIEW = 'FINANCIAL_VIEW';
    public const EDIT = 'FINANCIAL_EDIT';
    public const CREATE = 'FINANCIAL_CREATE';
    public const DELETE = 'FINANCIAL_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::CREATE, self::DELETE])
            && ($attribute === self::VIEW || $attribute === self::CREATE || $subject instanceof Financial);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $financial = $subject instanceof Financial ? $subject : null;

        return match ($attribute) {
            self::VIEW => $this->canView($financial, $user),
            self::EDIT => $this->canEdit($financial, $user),
            self::CREATE => $this->canCreate(),
            self::DELETE => $this->canDelete($financial, $user),
            default => false,
        };
    }

    private function canView(?Financial $financial, User $user): bool
    {
        // Si aucune ligne budgétaire n'est spécifiée (accès à la liste globale), 
        // n'importe quel utilisateur connecté peut y accéder.
        if ($financial === null) {
            return true;
        }

        // Si une ligne budgétaire est spécifiée, elle doit appartenir à l'organisation de l'utilisateur
        return $financial->getOrganization() === $user->getOrganization();
    }

    private function canCreate(): bool
    {
        // Utilisation propre de isGranted uniquement pour tester le rôle spécifique
        return $this->security->isGranted('ROLE_FINANCIER'); 
    }

    private function canEdit(?Financial $financial, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_FINANCIER')) {
            return false;
        }

        if ($financial === null) {
            return true;
        }

        return $financial->getOrganization() === $user->getOrganization();
    }

    private function canDelete(?Financial $financial, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_FINANCIER')) {
            return false;
        }

        if ($financial === null) {
            return true;
        }

        return $financial->getOrganization() === $user->getOrganization();
    }
}