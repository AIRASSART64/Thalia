<?php

namespace App\Repository;

use App\Entity\Organization;
use App\Entity\Season;
use App\Entity\Show;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    //chargement des thémes associés à un spectacle qui dépend d'une organization
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
    
     //Recherche filtrée et sécurisée par Organisation pour les Spectacles
     
    public function searchShowsForOrganization(
    Organization $organization,
    string $query = '',
    string $discipline = 'Tous',
    string $audience = 'Tous',
    string $theme = 'Tous' // 👈 Le 5ème paramètre doit être présent ici !
): array {
    $qb = $this->createQueryBuilder('s')
        ->andWhere('s.organization = :org')
        ->setParameter('org', $organization);

    // 1. Recherche textuelle
    if (!empty(trim($query))) {
        $qb->andWhere('LOWER(s.title) LIKE LOWER(:q)')
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

    // 4. Thème (Attention à la jointure)
    if ($theme !== 'Tous' && !empty($theme)) {
        $qb->innerJoin('s.themes', 't')
           ->andWhere('t.id = :themeId')
           ->setParameter('themeId', $theme);
    }

    $qb->orderBy('s.title', 'ASC');

    return $qb->getQuery()->getResult();
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
