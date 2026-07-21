<?php

namespace App\Security\Voter;

use App\Entity\Equipment;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security; 
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EquipmentVoter extends Voter
{
    public const VIEW = 'EQUIPMENT_VIEW';
    public const EDIT = 'EQUIPMENT_EDIT';
    public const CREATE = 'EQUIPMENT_CREATE';
    public const DELETE = 'EQUIPMENT_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::CREATE, self::DELETE])
            && ($attribute === self::VIEW || $attribute === self::CREATE || $subject instanceof Equipment);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $equipment = $subject instanceof Equipment ? $subject : null;

        return match ($attribute) {
            self::VIEW => $this->canView($equipment, $user),
            self::EDIT => $this->canEdit($equipment, $user),
            self::CREATE => $this->canCreate(),
            self::DELETE => $this->canDelete($equipment, $user),
            default => false,
        };
    }

    private function canView(?Equipment $equipment, User $user): bool
    {
        // Si aucun équipement n'est spécifié (accès à la liste globale), 
        // n'importe quel utilisateur connecté peut y accéder.
        if ($equipment === null) {
            return true;
        }

        // Si un équipement est spécifié, il doit appartenir à l'organisation de l'utilisateur
        return $equipment->getOrganization() === $user->getOrganization();
    }

    private function canCreate(): bool
    {
        // Utilisation propre de isGranted uniquement pour tester le rôle spécifique
        return $this->security->isGranted('ROLE_TECHNICIEN'); 
    }

    private function canEdit(?Equipment $equipment, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_TECHNICIEN')) {
            return false;
        }

        if ($equipment === null) {
            return true;
        }

        return $equipment->getOrganization() === $user->getOrganization();
    }

    private function canDelete(?Equipment $equipment, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_TECHNICIEN')) {
            return false;
        }

        if ($equipment === null) {
            return true;
        }

        return $equipment->getOrganization() === $user->getOrganization();
    }
}