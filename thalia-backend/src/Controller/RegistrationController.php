<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Organization;
use App\Form\RegistrationFormType;
use App\Security\UserRoles;
use App\Service\CultureApiService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse; 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register( Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em, CultureApiService $cultureApiService, MailService $mailService ): Response
     {
        $user = new User();
        $isJson = str_contains($request->headers->get('Content-Type', ''), 'application/json');

        if ($request->isMethod('POST') && $isJson) {
            $data = json_decode($request->getContent(), true) ?? [];
            
            $firstName = $data['firstName'] ?? $data['first_name'] ?? null;
            $lastName = $data['lastName'] ?? $data['last_name'] ?? null;
            $email = $data['email'] ?? null;
            $password = $data['password'] ?? null;
            $siret = $data['siret'] ?? null;

            if (!$email || !$password || !$siret || !$firstName || !$lastName) {
                return new JsonResponse(['error' => 'Données JSON incomplètes'], 400);
            }

            $user->setEmail($email)->setFirstName($firstName)->setLastName($lastName);
        } else {
            // Mode classique : Formulaire HTML Twig
            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);

            if (!$form->isSubmitted() || !$form->isValid()) {
                return $this->render('registration/index.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
            
            $siret = $form->get('siret')->getData();
            $password = $form->get('password')->getData();
        }

        // Querrying MCC API
        $apiData = $cultureApiService->fetchOrganizationBySiret($siret);
        if (!$apiData) {
            if ($isJson) {
                return new JsonResponse(['error' => "Numéro de SIRET non reconnu par le MCC"], 400);
            }
            $this->addFlash('danger', "Le numéro de SIRET renseigné n'est pas reconnu");
            return $this->redirectToRoute('register');
        }

        // Handelling organizartion 
        $organization = $em->getRepository(Organization::class)->findOneBy(['name' => $apiData['name']]);
        if (!$organization) {
            $organization = (new Organization())->setName($apiData['name'])->setSiret($apiData['siret'] ?? null);
            $em->persist($organization);
        }

        $user->setOrganization($organization)
             ->setIsActive(false)
             ->setRoles([UserRoles::USER])
             ->setPassword($passwordHasher->hashPassword($user, $password));

        $em->persist($user);
        $em->flush();

        // sending the email to confirm an account creation request
        $mailService->sendRegistrationPendingEmail($user);

        // response
        if ($isJson) {
            return new JsonResponse([
                'success' => 'Inscription enregistrée avec succès !',
                'organization_created' => $apiData['declarant']
            ], 201);
        }

        $this->addFlash('success', 'Inscription enregistrée ! En attente de validation.');
        return $this->redirectToRoute('app_login');
    }
}