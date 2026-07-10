<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\RegistrationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register( Request $request, RegistrationManager $registrationManager ): Response 
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

        // Interrogation de l'API du MCC

        try {
            $orgName = $registrationManager->registerUser($user, $siret, $password);
            
            if ($isJson) {
                return new JsonResponse(['success' => 'Inscription enregistrée avec succès !', 'organization_created' => $orgName], 201);
            }
            $this->addFlash('success', 'Inscription enregistrée ! En attente de validation.');
            return $this->redirectToRoute('app_login');

        } catch (\RuntimeException $e) { // Email doublon
            if ($isJson) return new JsonResponse(['error' => $e->getMessage()], 409);
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('register');

        } catch (\InvalidArgumentException $e) { // SIRET mal formé
            if ($isJson) return new JsonResponse(['error' => $e->getMessage()], 400);
            $form->get('siret')->addError(new FormError('Le numéro de SIRET renseigné est invalide (14 chiffres attendus).'));

        } catch (\LogicException $e) { // SIRET absent ou invalide MCC
            if ($isJson) return new JsonResponse(['error' => $e->getMessage()], 400);
            $form->get('siret')->addError(new FormError($e->getMessage()));

        } catch (\Exception $e) { // Api inaccessible 
            if ($isJson) return new JsonResponse(['error' => 'Service indisponible, réessayez plus tard'], 503);
            $this->addFlash('danger', 'Le service de vérification est temporairement indisponible.');
        }

        // Si on arrive ici, c'est qu'une exception Formulaire (HTML) a été attrapée : on réaffiche la vue avec ses erreurs
        return $this->render('registration/index.html.twig', ['registrationForm' => $form->createView()]);
    }
}














