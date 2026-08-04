<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('admin_dashboard')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class AdminDashboardComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $query = '';

    #[LiveProp(writable: true, url: true)]
    public string $role = 'Tous';

    #[LiveProp(writable: true, url: true)]
    public string $status = 'Tous';

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    public int $itemsPerPage = 10;

    public function __construct(
        private UserRepository $userRepository,
        private OrganizationRepository $organizationRepository,
        private Security $security,
    ) {
    }

    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    // Remise à zéro de la pagination lors du changement de filtre
    public function updatedQuery(): void { $this->page = 1; }
    public function updatedRole(): void { $this->page = 1; }
    public function updatedStatus(): void { $this->page = 1; }

    // --- Données spécifiques à l'onglet 1 (À valider) ---
    public function getPendingUsers(): array
    {
        return $this->userRepository->getPendingUsers();
    }

    public function getPendingOrganizations(): array
    { 
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        $excludedOrgId = $currentUser?->getOrganization()?->getId();
        return $this->organizationRepository->getPendingOrganizations($excludedOrgId);
    }

    // --- Données pour l'onglet 2 (Établissements) et l'onglet 3 (Utilisateurs) ---
    public function getUsers(): array
    {
        return $this->userRepository->searchUsers(
            $this->query,
            $this->role,
            $this->status,
            $this->page,
            $this->itemsPerPage
        );
    }

    public function getOrganizations(): array
    { 
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        $excludedOrg = $currentUser?->getOrganization();

        return $this->organizationRepository->searchOrganizations(
            $this->query,
            $this->status,
            $this->page,
            $this->itemsPerPage,
            $excludedOrg
        );
    }
    public function getTotalOrganizations(): int
    {
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        $excludedOrg = $currentUser?->getOrganization();

        return $this->organizationRepository->countSearchOrganizations(
            $this->query,
            $this->status,
            $excludedOrg
        );
    }


    // --- Calcule de la pagination unifiée ---
    public function getTotalItems(): int
    {
        return $this->userRepository->countSearchUsers(
            $this->query,
            $this->role,
            $this->status
        );
    }

    public function getMaxPages(): int
    {
        $total = $this->getTotalItems();
        return $total > 0 ? (int) ceil($total / $this->itemsPerPage) : 1;
    }

    public function getPageStart(): int
    {
        if ($this->getTotalItems() === 0) {
            return 0;
        }
        return (($this->page - 1) * $this->itemsPerPage) + 1;
    }

    public function getPageEnd(): int
    {
        return min($this->page * $this->itemsPerPage, $this->getTotalItems());
    }
    public function getRoles(): array
    {
    return UserRole::cases();
    }
}