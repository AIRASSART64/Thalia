<?php

namespace App\Repository;

use App\Entity\Performance;
use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Performance>
 */
class PerformanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Performance::class);
    }

    /**
     * Calcule le total du budget déjà dépensé/engagé pour une saison donnée.
     * Optionnellement, on peut exclure une représentation (utile lors d'une édition).
     */
    public function getTotalCostForSeason(Season $season, ?Performance $excludedPerformance = null): float
    {
        $qb = $this->createQueryBuilder('p')
            ->select('SUM(p.total_cost) as total')
            ->where('p.season = :season')
            ->setParameter('season', $season);

        // Si on est en mode édition, on ne compte pas le coût actuel de la représentation modifiée
        if ($excludedPerformance && $excludedPerformance->getId()) {
            $qb->andWhere('p.id != :excludedId')
               ->setParameter('excludedId', $excludedPerformance->getId());
        }

        $result = $qb->getQuery()->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    //    public function findOneBySomeField($value): ?Performance
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
