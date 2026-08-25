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
     * @throws \LogicException Si le SIRET n'est pas reconnu comme entrepreneur de spectacles vivants
     * @throws \Exception Si l'API est indisponible
     */
    public function registerUser(User $user, string $siret, string $plainPassword): string
    {
        // suppression des espaces dans la saisie du SIRET
        $siret = preg_replace('/\s+/', '', $siret);

        // Vérification email dupliqué
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if ($existingUser) {
            throw new \RuntimeException('Un compte existe déjà avec cet email.');
        }

        // Vérification auprès de l'API Recherche Entreprises (flag entrepreneur de spectacles)
        $apiData = $this->fetchApiService->verifyEntrepreneurSpectacle($siret);

        if (!$apiData) {
            throw new \LogicException("Ce numéro de SIRET n'est pas reconnu comme appartenant à un entrepreneur de spectacles vivants.");
        }

        // Déduplication ou création de l'organisation
        $organization = $this->em->getRepository(Organization::class)->findOneBy(['siret' => $apiData['siret']]);
        if (!$organization) {
            $organization = (new Organization())
                ->setName($apiData['name'])
                ->setSiret($apiData['siret'])
                ->setBusinessName($apiData['business_name']);

            $this->em->persist($organization);
        } else {
            // L'organisation existe déjà : on complète les champs manquants
            if (empty($organization->getBusinessName()) && !empty($apiData['business_name'])) {
                $organization->setBusinessName($apiData['business_name']);
            }

            if (empty($organization->getName()) && !empty($apiData['name'])) {
                $organization->setName($apiData['name']);
            }
        }

        // Hydratation finale du User
        $user->setOrganization($organization)
             ->setIsActive(false)
             ->setRoles([UserRoles::USER])
             ->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->em->persist($user);
        $this->em->flush();

        // Notification
        $this->mailService->sendRegistrationPendingEmail($user);

        return $apiData['name'];
    }
}