<?php

namespace App\Repository;

use App\Entity\Contact;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /**
     * Recherche filtrée et sécurisée par Organisation pour les Contacts (avec pagination)
     */
    public function searchContactsForOrganization(
        Organization $organization,
        string $query = '',
        string $companyName = 'Tous',
        string $role = 'Tous',
        string $sortField = 'lastName',
        string $sortDirection = 'ASC',
        int $limit = 5,
        int $offset = 0
    ): array {
        $qb = $this->createFilteredQueryBuilder($organization, $query, $companyName, $role);

        // Mappage entre le champ demandé et les propriétés PHP de l'entité Contact ($last_name, $first_name, $company_name)
        $allowedFields = [
            'lastName'     => 'c.last_name',
            'last_name'    => 'c.last_name',
            'firstName'    => 'c.first_name',
            'first_name'   => 'c.first_name',
            'companyName'  => 'c.company_name',
            'company_name' => 'c.company_name',
            'role'         => 'c.role',
        ];

        $field = $allowedFields[$sortField] ?? 'c.last_name';
        $direction = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        $qb->orderBy($field, $direction);

        // Tri secondaire pour éviter l'aléa en cas d'égalités
        if ($field === 'c.last_name') {
            $qb->addOrderBy('c.first_name', 'ASC');
        }

        $qb->setMaxResults($limit)
           ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte le nombre TOTAL de contacts correspondant aux filtres
     */
    public function countContactsForOrganization(
        Organization $organization,
        string $query = '',
        string $companyName = 'Tous',
        string $role = 'Tous'
    ): int {
        $qb = $this->createFilteredQueryBuilder($organization, $query, $companyName, $role);
        
        $qb->select('COUNT(c.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Factorisation du QueryBuilder
     */
    private function createFilteredQueryBuilder(
        Organization $organization,
        string $query,
        string $companyName,
        string $role
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.organization = :org')
            ->setParameter('org', $organization);

        // 1. Recherche textuelle (c.last_name et c.first_name)
        if (!empty(trim($query))) {
            $qb->andWhere('(
                    LOWER(c.last_name) LIKE LOWER(:q)
                    OR LOWER(c.first_name) LIKE LOWER(:q)
                    OR LOWER(c.notes) LIKE LOWER(:q)
                )')
               ->setParameter('q', '%' . trim($query) . '%');
        }

        // 2. Filtre par structure (propriété c.company_name)
        if ($companyName !== 'Tous' && !empty($companyName)) {
            $qb->andWhere('c.company_name = :companyName')
               ->setParameter('companyName', $companyName);
        }

        // 3. Filtre par rôle
        if ($role !== 'Tous' && !empty($role)) {
            $qb->andWhere('c.role = :role')
               ->setParameter('role', $role);
        }

        return $qb;
    }

    /**
     * Récupère la liste des noms de structures uniques pour une organisation.
     */
    public function findUniqueCompaniesForOrganization(Organization $organization): array
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.company_name') // 👈 Cible $company_name de l'entité Contact
            ->where('c.organization = :org')
            ->andWhere('c.company_name IS NOT NULL')
            ->andWhere("c.company_name != ''")
            ->setParameter('org', $organization)
            ->orderBy('c.company_name', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * Récupère la liste des rôles uniques pour une organisation.
     */
    public function findUniqueRolesForOrganization(Organization $organization): array
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.role')
            ->where('c.organization = :org')
            ->andWhere('c.role IS NOT NULL')
            ->andWhere("c.role != ''")
            ->setParameter('org', $organization)
            ->orderBy('c.role', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }



//    /**
//     * @return Contact[] Returns an array of Contact objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Contact
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
