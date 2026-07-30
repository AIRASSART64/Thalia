<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('contact_search')]
class ContactSearchComponent extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public string $companyName = 'Tous';

    #[LiveProp(writable: true)]
    public string $role = 'Tous';

    #[LiveProp(writable: true)]
    public string $sortField = 'last_name';

    #[LiveProp(writable: true)]
    public string $sortDirection = 'ASC';

    public function __construct(
        private ContactRepository $contactRepository
    ) {}

    public function getContacts(): array
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user || !$user->getOrganization()) {
            return [];
        }

        return $this->contactRepository->searchContactsForOrganization(
            $user->getOrganization(),
            query: $this->query,
            companyName: $this->companyName,
            role: $this->role,
            sortField: $this->sortField,
            sortDirection: $this->sortDirection
        );
    }

    public function getCompanies(): array
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user || !$user->getOrganization()) {
            return [];
        }

        return $this->contactRepository->findUniqueCompaniesForOrganization($user->getOrganization());
    }

    public function getRoles(): array
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user || !$user->getOrganization()) {
            return [];
        }

        return $this->contactRepository->findUniqueRolesForOrganization($user->getOrganization());
    }
}