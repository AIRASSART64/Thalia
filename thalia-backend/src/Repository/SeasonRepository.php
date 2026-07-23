<?php

namespace App\Repository;

use App\Entity\Organization;
use App\Entity\Season;
use App\Enum\SeasonStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Season>
 */
class SeasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    //récuparation de la saison active d'une organization
    public function findActiveSeason(Organization $organization): ?Season
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.organization = :org')
            ->andWhere('s.season_status = :status')
            ->setParameter('org', $organization)
            ->setParameter('status', SeasonStatusEnum::ACTIVE)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
           
    }

       /**
        * Récupérttion de toutes les saisons ouvertes (active et en préparation)
        * @return Season[] Returns an array of Season objects
        */
       public function findOpenSeason(Organization $organization): array
       {
           return $this->createQueryBuilder('s')
               ->andWhere('s.organization = :org')
               ->andWhere('s.season_status IN (:statuses)')
               ->setParameter('org', $organization)
               ->setParameter('statuses', [SeasonStatusEnum::ACTIVE, SeasonStatusEnum::DRAFT])
               ->orderBy('s.start_date', 'DESC')
               ->getQuery()
               ->getResult();

       }

        // récupération de toutes les saisons d'une organization 
       public function findByOrganization(Organization $organization): array
       {
           return $this->createQueryBuilder('s')
               ->andWhere('s.organization = :org')
               ->setParameter('org', $organization)
                ->orderBy('s.start_date', 'DESC')
               ->getQuery()
               ->getResult()
           ;
       }
}
