<?php

namespace App\Repository;

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
