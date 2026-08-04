<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Recherche paginée d'utilisateurs
     * 
     * @return User[]
     */
    public function searchUsers(string $query, string $role, string $status, int $page = 1, int $itemsPerPage = 10): array
    {
        $qb = $this->createSearchQueryBuilder($query, $role, $status);

        // Pagination via Doctrine
        $firstResult = ($page - 1) * $itemsPerPage;
        $qb->setFirstResult($firstResult)
            ->setMaxResults($itemsPerPage)
            ->orderBy('u.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Nombre total d'utilisateurs correspondant aux filtres
     */
    public function countSearchUsers(string $query, string $role, string $status): int
    {
        $qb = $this->createSearchQueryBuilder($query, $role, $status);

        $qb->select('COUNT(DISTINCT u.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Facteur commun pour la construction de la requête de recherche
     */
    // src/Repository/UserRepository.php

    // src/Repository/UserRepository.php

   // src/Repository/UserRepository.php

// src/Repository/UserRepository.php

    /**
     * Factorisation du QueryBuilder de recherche pour éviter la répétition de code
     */
    private function createSearchQueryBuilder(
        string $query = '',
        string $role = 'Tous',
        string $status = 'Tous'
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.organization', 'o')
            ->addSelect('o');

        // Exclure le Super Admin de la liste
        $qb->andWhere('u.roles NOT LIKE :superAdminRole')
            ->setParameter('superAdminRole', '%"ROLE_SUPER_ADMIN"%');

        // 1. Recherche textuelle (Format DQL direct comme dans ShowRepository)
        if (!empty(trim($query))) {
            $qb->andWhere('(
            LOWER(u.firstName) LIKE LOWER(:q)
            OR LOWER(u.lastName) LIKE LOWER(:q)
            OR LOWER(u.email) LIKE LOWER(:q)
            OR LOWER(o.name) LIKE LOWER(:q)
        )')
                ->setParameter('q', '%' . trim($query) . '%');
        }

        // 2. Filtre par Rôle
        if ($role !== 'Tous' && !empty($role)) {
            // Garantit la présence du préfixe ROLE_
            $formattedRole = str_starts_with($role, 'ROLE_') ? $role : 'ROLE_' . $role;

            $qb->andWhere('u.roles LIKE :roleParam')
                ->setParameter('roleParam', '%"' . $formattedRole . '"%');
        }

        // 3. Filtre par Statut
        if ($status !== 'Tous' && !empty($status)) {
            if ($status === 'active') {
                $qb->andWhere('u.isActive = :isActive')
                    ->setParameter('isActive', true);
            } elseif ($status === 'inactive') {
                $qb->andWhere('u.isActive = :isActive')
                    ->setParameter('isActive', false);
            }
        }

        return $qb;
    }
    /**
     * Récupère les utilisateurs en attente de validation (isActive = false)
     * 
     * @return User[]
     */
    public function getPendingUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.organization', 'o')
            ->addSelect('o')
            ->where('u.isActive = :isActive')
            ->andWhere('u.roles NOT LIKE :superAdminRole')
            ->setParameter('isActive', false)
            ->setParameter('superAdminRole', '%"ROLE_SUPER_ADMIN"%')
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
