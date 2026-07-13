<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CrudManagerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

   // affichage des éléments de l'organisation pour les users de l'orgnisation connecté
    private function getTargetOrganization(): mixed
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if (!$user) {
            throw new AccessDeniedHttpException("Vous devez être connecté.");
        }
        return $user->getOrganization();
    }
    // creation
    public function create(object $entity): void
    {
        // Si l'entité dispose d'une méthode setOrganization, on injecte l'organisation courante
        if (method_exists($entity, 'setOrganization')) {
            $entity->setOrganization($this->getTargetOrganization());
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

   // modification
    public function update(object $entity): void
    {
        $this->entityManager->flush();
    }

    // suppression
    public function delete(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}