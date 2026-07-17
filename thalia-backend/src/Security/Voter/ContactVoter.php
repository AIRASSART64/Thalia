<?php

namespace App\Security\Voter;

use App\Entity\Contact;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security; 
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class ContactVoter extends Voter
{
    public const VIEW = 'CONTACT_VIEW';
    public const EDIT = 'CONTACT_EDIT';
    public const CREATE = 'CONTACT_CREATE';
    public const DELETE = 'CONTACT_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::CREATE, self::DELETE])
            && ($attribute === self::VIEW || $attribute === self::CREATE || $subject instanceof Contact);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $contact = $subject instanceof Contact ? $subject : null;

        return match ($attribute) {
            self::VIEW => $this->canView($contact, $user),
            self::EDIT => $this->canEdit($contact, $user),
            self::CREATE => $this->canCreate(),
            self::DELETE => $this->canDelete($contact, $user),
            default => false,
        };
    }

    private function canView(?Contact $contact, User $user): bool
    {
        // Si la liste globale est appelée (sujet null), on autorise l'utilisateur connecté
        if ($contact === null) {
            return true;
        }

        // Sinon, le contact doit appartenir à la même organisation
        return $contact->getOrganization() === $user->getOrganization();
    }

    private function canCreate(): bool
    {
        return $this->security->isGranted('ROLE_PROGRAMMATEUR'); 
    }

    private function canEdit(?Contact $contact, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_PROGRAMMATEUR')) {
            return false;
        }

        if ($contact === null) {
            return true;
        }

        return $contact->getOrganization() === $user->getOrganization();
    }

    private function canDelete(?Contact $contact, User $user): bool
    {
        if (!$this->security->isGranted('ROLE_PROGRAMMATEUR')) {
            return false;
        }

        if ($contact === null) {
            return true;
        }

        return $contact->getOrganization() === $user->getOrganization();
    }
}