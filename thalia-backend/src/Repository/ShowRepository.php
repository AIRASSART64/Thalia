<?php

namespace App\Repository;

use App\Entity\Organization;
use App\Entity\Season;
use App\Entity\Show;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Show>
 */
class ShowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Show::class);
    }

    /**
     * Récupère les spectacles qui n'ont pas encore été planifiés pour cette saison
     */
    public function findUnassignedForSeason(Season $season): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.performances', 'p', 'WITH', 'p.season = :season')
            ->where('p.id IS NULL')
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult();
    }

    /**
     * Chargement des thèmes associés à un spectacle qui dépend d'une organisation
     */
    public function findByOrganizationWithThemes(Organization $organization): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.themes', 't')
            ->addSelect('t')
            ->where('s.organization = :org')
            ->setParameter('org', $organization)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Recherche filtrée, sécurisée par Organisation et PAGINÉE pour les Spectacles
     */
    public function searchShowsForOrganization(
        Organization $organization,
        string $query = '',
        string $discipline = 'Tous',
        string $audience = 'Tous',
        string $theme = 'Tous',
        int $page = 1,
        int $limit = 10
    ): array {
        $qb = $this->createSearchQueryBuilder($organization, $query, $discipline, $audience, $theme);

        // Application de l'offset et du limit pour la pagination
        $qb->orderBy('s.title', 'ASC')
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte le nombre TOTAL de résultats pour la pagination (sans appliquer le LIMIT)
     */
    public function countShowsForOrganization(
        Organization $organization,
        string $query = '',
        string $discipline = 'Tous',
        string $audience = 'Tous',
        string $theme = 'Tous'
    ): int {
        $qb = $this->createSearchQueryBuilder($organization, $query, $discipline, $audience, $theme);

        return (int) $qb->select('COUNT(DISTINCT s.id)')
                        ->getQuery()
                        ->getSingleScalarResult();
    }

    /**
     * Factorisation du QueryBuilder de recherche pour éviter la répétition de code
     */
    private function createSearchQueryBuilder(
        Organization $organization,
        string $query = '',
        string $discipline = 'Tous',
        string $audience = 'Tous',
        string $theme = 'Tous'
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.organization = :org')
            ->setParameter('org', $organization);

        // 1. Recherche textuelle
        if (!empty(trim($query))) {
            $qb->andWhere('(
                LOWER(s.title) LIKE LOWER(:q)
                OR LOWER(s.synopsis) LIKE LOWER(:q)
            )')
            ->setParameter('q', '%' . trim($query) . '%');
        }

        // 2. Discipline
        if ($discipline !== 'Tous' && !empty($discipline)) {
            $qb->andWhere('s.discipline = :discipline')
               ->setParameter('discipline', $discipline);
        }

        // 3. Audience
        if ($audience !== 'Tous' && !empty($audience)) {
            $qb->andWhere('s.audience = :audience')
               ->setParameter('audience', $audience);
        }

        // 4. Thème (Jointure conditionnelle)
        if ($theme !== 'Tous' && !empty($theme)) {
            $qb->innerJoin('s.themes', 't')
               ->andWhere('t.id = :themeId')
               ->setParameter('themeId', $theme);
        }

        return $qb;
    }

    
    //    public function findOneBySomeField($value): ?Show
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
