<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
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
    public string $sortField = 'lastName'; 

    #[LiveProp(writable: true)]
    public string $sortDirection = 'ASC';

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp]
    public int $itemsPerPage = 5;

    public function __construct(
        private ContactRepository $contactRepository
    ) {}

    /**
     * Réinitialise la page à 1 à chaque fois qu'un filtre de recherche est modifié
     */
    public function updatedQuery(): void
    {
        $this->page = 1;
    }

    public function updatedCompanyName(): void
    {
        $this->page = 1;
    }

    public function updatedRole(): void
    {
        $this->page = 1;
    }

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
            sortDirection: $this->sortDirection,
            limit: $this->itemsPerPage,
            offset: ($this->page - 1) * $this->itemsPerPage
        );
    }

    public function getTotalContacts(): int
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user || !$user->getOrganization()) {
            return 0;
        }

        return $this->contactRepository->countContactsForOrganization(
            $user->getOrganization(),
            query: $this->query,
            companyName: $this->companyName,
            role: $this->role
        );
    }

    public function getTotalPages(): int
    {
        $total = $this->getTotalContacts();
        return (int) ceil($total / $this->itemsPerPage);
    }

    #[LiveAction] 
    public function setPage(#[LiveArg] int $page): void
    {
        $this->page = max(1, min($page, max(1, $this->getTotalPages())));
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