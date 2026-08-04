<?php

namespace App\Repository;

use App\Entity\Organization;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Organization>
 */
class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    //    /**
    //     * @return Organization[] Returns an array of Organization objects
    //     */
    public function findByUser(User $user): ?Organization
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.users', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    /**
     * Recherche paginée d'organisations
     * 
     * @return Organization[]
     */
    public function searchOrganizations(string $query, string $status, int $page = 1, int $itemsPerPage = 10, ?Organization $excludedOrganization = null): array
    {
        $qb = $this->createSearchQueryBuilder($query, $status, $excludedOrganization);

        $firstResult = ($page - 1) * $itemsPerPage;
        $qb->setFirstResult($firstResult)
            ->setMaxResults($itemsPerPage)
            ->orderBy('o.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Nombre total d'organisations correspondant aux filtres
     */
    public function countSearchOrganizations(string $query, string $status, ?Organization $excludedOrganization = null): int
    {
        $qb = $this->createSearchQueryBuilder($query, $status, $excludedOrganization);

        $qb->select('COUNT(DISTINCT o.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Facteur commun pour la construction de la requête de recherche
     */
    private function createSearchQueryBuilder(string $query, string $status, ?Organization $excludedOrganization = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');

        if ($excludedOrganization !== null) {
            $qb->andWhere('o.id != :excludedOrgId')
                ->setParameter('excludedOrgId', $excludedOrganization->getId());
        }

        // 1. Recherche textuelle (Nom de l'organisation, SIRET, Ville, Email)
        if (!empty(trim($query))) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'LOWER(o.name) LIKE LOWER(:query)',
                    'LOWER(o.siret) LIKE LOWER(:query)',
                    'LOWER(o.city) LIKE LOWER(:query)',
                    'LOWER(o.email) LIKE LOWER(:query)'
                )
            )->setParameter('query', '%' . trim($query) . '%');
        }

        // 2. Filtre par Statut
        if ($status !== 'Tous' && !empty($status)) {
            $isActive = filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($isActive !== null) {
                $qb->andWhere('o.isActive = :isActive')
                    ->setParameter('isActive', $isActive);
            }
        }

        return $qb;
    }

    /**
     * Récupère les établissements non modifiés depuis leur création (createdAt = updatedAt)
     * 
     * @param int|string|null $excludedOrgId ID de l'organisation à exclure (ex: l'organisation du SuperAdmin)
     * @return Organization[]
     */
    public function getPendingOrganizations(?int $excludedOrgId = null): array
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.created_at = o.updated_at');

        // On n'ajoute l'exclusion que si un ID a été fourni
        if ($excludedOrgId !== null) {
            $qb->andWhere('o.id != :excludedId')
               ->setParameter('excludedId', $excludedOrgId);
        }

        return $qb->orderBy('o.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?Organization
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
