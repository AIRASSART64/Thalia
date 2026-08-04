<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        private EntityManagerInterface $em // <-- 1. Injection de l'EntityManager
    ) {
        // 2. Désactivation du filtre Multi-tenant à chaque appel (initial ou Live AJAX)
        if ($this->em->getFilters()->isEnabled('tenant_filters')) {
            $this->em->getFilters()->disable('tenant_filters');
        }
    }

    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    // Remise à zéro automatique de la pagination lors de la modification des filtres
    public function updatedQuery(): void { $this->page = 1; }
    public function updatedRole(): void { $this->page = 1; }
    public function updatedStatus(): void { $this->page = 1; }

    // --- Onglet 1 : Demandes à valider ---
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

    // --- Onglet 2 & 3 : Établissements et Utilisateurs ---
    public function getUsers(): array
    {
        return $this->userRepository->searchUsers(
            $this->query,
            $this->getCleanRoleValue(),
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

    // --- Calculs de pagination ---
    public function getTotalItems(): int
    {
        return $this->userRepository->countSearchUsers(
            query: $this->query,
            role: $this->getCleanRoleValue(),
            status: $this->status
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

    /**
     * Méthode d'aide pour garantir que la valeur transmise au Repository est une chaîne nettoyée
     */
    private function getCleanRoleValue(): string
    {
        if ($this->role instanceof UserRole) {
            return $this->role->value;
        }

        return $this->role;
    }
}