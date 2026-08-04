<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Entity\User;
use App\Enum\UserRole;
use App\Form\OrganizationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/superadmin', name: 'superadmin_')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class SuperAdminController extends AbstractController
{
    // Gestion de la désactivation multitenant
    public function __construct(EntityManagerInterface $em)
    {
        if ($em->getFilters()->isEnabled('tenant_filters')) {
            $em->getFilters()->disable('tenant_filters');
        }
    }

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('superadmin/index.html.twig');
    }
    #[Route('/user/{id}/change-role', name:'user_change_role', methods:['POST'])]
    public function changeRole(User $user, Request $request, EntityManagerInterface $em): Response
    {
        // Sécurité : évite l'auto-modification
    if ($user === $this->getUser()) {
        $this->addFlash('danger', 'Action impossible : vous ne pouvez pas modifier votre propre rôle !');
        return $this->redirectToRoute('superadmin_dashboard');
    }

    // Récupération et conversion du rôle via l'Enum
    $roleInput = $request->request->get('role', '');
    $newRoleEnum = UserRole::tryFrom($roleInput);

    if ($newRoleEnum !== null) {
        // Actualisation du rôle
        $user->setRoles([$newRoleEnum->value]);
        $em->flush();

        $this->addFlash('success', sprintf( 'Le rôle de %s a été mis à jour en "%s".', $user->getEmail(),$newRoleEnum->label()
        ));
    } else {
        $this->addFlash('danger', 'Rôle sélectionné invalide.');
    }

    return $this->redirectToRoute('superadmin_dashboard');
    }

    #[Route('/user/{id}/toggle-active', name: 'user_toggle_active', methods: ['POST'])]
    public function toggleActive(User $user, Request $request, EntityManagerInterface $em): Response
    {
       // 1. Sécurité pour éviter l'auto-désactivation
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Action impossible : vous ne pouvez pas désactiver votre propre compte !');
            return $this->redirectToRoute('superadmin_dashboard');
        }

        // 2. Vérification de la validité du jeton CSRF
        if ($this->isCsrfTokenValid('toggle_active_' . $user->getId(), $request->request->get('_token'))) {
            // Inversion du statut
            $user->setIsActive(!$user->isActive());
            $em->flush();

            $status = $user->isActive() ? 'activé et notifié par e-mail' : 'désactivé';
            $this->addFlash('success', sprintf('Le compte de %s %s a bien été %s.', $user->getFirstName(), $user->getLastName(), $status));
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide. Impossible de modifier le statut du compte.');
        }

        return $this->redirectToRoute('superadmin_dashboard');
    }

    #[Route('/organization/edit/{id}', name: 'organization_edit', methods: ['GET', 'POST'])]
    public function editOrganization(Organization $organization, Request $request, EntityManagerInterface $em): Response
    {
        // Création du formulaire de modification de l'organisation
        $form = $this->createForm(OrganizationFormType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', sprintf("L'établissement %s a été modifié", $organization->getName()));
            return $this->redirectToRoute('superadmin_dashboard');
        }

        return $this->render('superadmin/organization_edit.html.twig', [
            'organization' => $organization,
            'organizationForm' => $form->createView(),
        ]);
    }

    #[Route('/user/delete/{id}', name: 'user_delete', methods: ['POST'])]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Action impossible : vous ne pouvez pas supprimer votre propre compte !');
            return $this->redirectToRoute('superadmin_dashboard');
        }

        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->get('_token'))) {
            $email = $user->getEmail();

            $em->remove($user);
            $em->flush();
            $this->addFlash('success', sprintf('L’utilisateur %s a été supprimé définitivement.', $email));
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide. Impossible de supprimer l’utilisateur.');
        }

        return $this->redirectToRoute('superadmin_dashboard');
    }

    #[Route('/organization/delete/{id}', name: 'organization_delete', methods: ['POST'])]
    public function deleteOrganization(Organization $organization, Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete_organization_' . $organization->getId(), $request->request->get('_token'))) {
            $organizationName = $organization->getName();

            // Vérification de l'existence d'un user rattaché à une organisation
            $usersFromOrganization = $userRepository->findBy(['organization' => $organization]);
            $countUsers = count($usersFromOrganization);

            if ($countUsers > 0) {
                $this->addFlash('danger', sprintf(
                    'Action impossible : L’établissement "%s" ne peut pas être supprimé car %d utilisateur(s) y est/sont encore rattaché(s)',
                    $organizationName,
                    $countUsers
                ));
                return $this->redirectToRoute('superadmin_dashboard');
            }

            $em->remove($organization);
            $em->flush();
            $this->addFlash('success', sprintf('L’établissement "%s" a été supprimé avec succès.', $organizationName));
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide. Impossible de supprimer l’établissement.');
        }

        return $this->redirectToRoute('superadmin_dashboard');
    }
}
