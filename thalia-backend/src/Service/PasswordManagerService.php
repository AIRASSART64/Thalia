<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordManagerService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager
    ) {}

   
    public function upgradePassword(User $user, string $plainPassword): void
    {
        // Hachage sécurisé du mot de passe en clair
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Préparation de l'envoi et envoi en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}