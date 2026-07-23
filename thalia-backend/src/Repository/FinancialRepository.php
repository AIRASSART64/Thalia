<?php

namespace App\Repository;

use App\Entity\Financial;
use App\Entity\Season;
use App\Enum\FinancialTypeEnum;
use App\Enum\FinancialCategoryEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Financial>
 */
class FinancialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Financial::class);
    }

    /**
     * Récupère les catégories associées à un type donné (DEBIT ou CREDIT)
     * @return FinancialCategoryEnum[]
     */
    public function getCategoriesForType(FinancialTypeEnum $type): array
    {
        return array_filter(
            FinancialCategoryEnum::cases(),
            fn (FinancialCategoryEnum $category) => $category->getFinancialTypeEnum() === $type
        );
    }

    /**
     * Récupère les lignes filtrées par type (DEBIT ou CREDIT) pour une saison
     * @return Financial[]
     */
    public function findBySeasonAndType(Season $season, FinancialTypeEnum $type): array
    {
        $categories = $this->getCategoriesForType($type);

        if (empty($categories)) {
            return [];
        }

        return $this->createQueryBuilder('f')
            ->where('f.season = :season')
            ->andWhere('f.category IN (:categories)')
            ->setParameter('season', $season)
            ->setParameter('categories', $categories)
            ->orderBy('f.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le total HT pour un type donné (DEBIT ou CREDIT) directement en SQL (SUM)
     */
    public function getTotalHtBySeasonAndType(Season $season, FinancialTypeEnum $type): float
    {
        $categories = $this->getCategoriesForType($type);

        if (empty($categories)) {
            return 0.0;
        }

        $result = $this->createQueryBuilder('f')
            ->select('SUM(f.amount_ht)') 
            ->where('f.season = :season')
            ->andWhere('f.category IN (:categories)')
            ->setParameter('season', $season)
            ->setParameter('categories', $categories)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0.0);
    }
}