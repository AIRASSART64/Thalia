<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\PasswordManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route('/profile/password', name: 'profile_password_')]
#[IsGranted('ROLE_USER')] // vérification de la connection de l'utilisateur
class ProfilePasswordController extends AbstractController
{
    #[Route('/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PasswordManagerService $passwordManager ): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        // Construction du formulaire de changement de mot de passe
        $form = $this->createFormBuilder()
            ->add('currentPassword', PasswordType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre mot de passe actuel.',
                    ]),
                    new UserPassword([
                        'message' => 'Votre mot de passe actuel est incorrect.',
                    ]),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent être identiques.',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un nouveau mot de passe.',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre nouveau mot de passe doit faire au moins {{ limit }} caractères.',
                        'max' => 4096, // Limite de sécurité standard pour le hachage
                    ]),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du nouveau mot de passe validé
            $newPassword = $form->get('newPassword')->getData();
            
            // Traitement via le service PasswordManager 
            $passwordManager->upgradePassword($user, $newPassword);

            // Notification de succès
            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}