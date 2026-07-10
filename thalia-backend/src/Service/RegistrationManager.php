<?php

namespace App\Service;

use App\Entity\Organization;
use App\Entity\User;
use App\Security\UserRoles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationManager
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FetchApiService $fetchApiService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailService $mailService
    ) {}

    /**
     * @throws \RuntimeException Si l'email existe déjà
     * @throws \InvalidArgumentException Si le SIRET est syntaxiquement invalide
     * @throws \Exception Si l'API est indisponible ou si l'organisation n'est pas reconnue/valide
     */
    public function registerUser(User $user, string $siret, string $plainPassword): string
    {
        // 1. Vérification email dupliqué
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if ($existingUser) {
            throw new \RuntimeException('Un compte existe déjà avec cet email.');
        }

        // 2. Récupération API du MCC (Gère l'indisponibilité et le format)
        $apiData = $this->fetchApiService->fetchOrganizationBySiret($siret);

        if (!$apiData) {
            throw new \LogicException("Ce numéro de SIRET n'est pas reconnu ou ne dispose pas d'une déclaration valide auprès du MCC.");
        }

        // 3. Déduplication ou création de l'organisation
        $organization = $this->em->getRepository(Organization::class)->findOneBy(['siret' => $apiData['siret']]);
        if (!$organization) {
            $organization = (new Organization())
                ->setName($apiData['name'])
                ->setSiret($apiData['siret'])
                ->setLicenceNumber($apiData['licence_number'])
                 ->setBusinessName($apiData['business_name']);
                
                
            $this->em->persist($organization);
        }

        // 4. Hydratation finale du User
        $user->setOrganization($organization)
             ->setIsActive(false)
             ->setRoles([UserRoles::USER])
             ->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->em->persist($user);
        $this->em->flush();

        // 5. Notification
        $this->mailService->sendRegistrationPendingEmail($user);

        return $apiData['name']; // On retourne le nom de l'organisation créée
    }
}