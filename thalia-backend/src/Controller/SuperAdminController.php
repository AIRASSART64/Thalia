<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Entity\User;
use App\Form\OrganizationFormType;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/superadmin', name: 'superadmin_')]
class SuperAdminController extends AbstractController
{
    // Gestion de la desactivation multitenant
    public function __construct(EntityManagerInterface $em)
    {
        if ($em->getFilters()->isEnabled('tenant_filters')) {
            $em->getFilters()->disable('tenant_filters');
        }
    }

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(UserRepository $userRepository, OrganizationRepository $organizationRepository): Response
    {
        return $this->render('superadmin/index.html.twig', [
            'users' => $userRepository->findAll(),
            'organizations'=> $organizationRepository->findAll(),
        ]);
    }

    
    #[Route('/user/{id}/toggle-active', name: 'user_toggle_active', methods: ['POST'])]
    public function toggleActive(User $user, EntityManagerInterface $em): Response
    {
        // Sécurité pour éviter l'auto-désactivation
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Action impossible : vous ne pouvez pas désactiver votre propre compte !');
            return $this->redirectToRoute('superadmin_dashboard');
        }
       
        // inversion du statut
        $user->setIsActive(!$user->isActive());
        
        $em->flush();

        $status = $user->isActive() ? 'activé et notifié par e-mail' : 'désactivé';
        $this->addFlash('success', sprintf('Le compte de %s %s a bien été %s.', $user->getFirstName(), $user->getLastName(), $status));

        return $this->redirectToRoute('superadmin_dashboard');
    }

    #[Route('organization/edit/{id}', name:'organization_edit', methods:['GET', 'POST'])]
    public function editOrganization(Organization $organization, Request $request, EntityManagerInterface $em): Response
    {
        // création du formaulaire de modification de l'organization
        $form = $this->createForm(OrganizationFormType::class, $organization );
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->flush();

            $this->addFlash('success', sprintf("L'établissement %s a été modifié", $organization->getName()));
            return $this->redirectToRoute('superadmin_dashboard');
        }

        return $this->render('superadmin/organization_edit.html.twig', [
            'organization'=>$organization,
            'organizationForm'=> $form->createView(),

        ]);

    }

    #[Route('user/delete/{id}', name:'user_delete', methods:['POST'])]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if($user === $this->getUser()){
            $this->addFlash('danger', 'Action impossible : vous ne pouvez pas supprimer votre propre compte !');
            return $this->redirectToRoute('superadmin_dashboard');
        }
        if($this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->get('_token'))){
            $email = $user->getEmail();

            $em->remove($user);
            $em->flush();
            $this->addFlash('success',sprintf('L’utilisateur %s a été supprimé définitivement.', $email));
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide. Impossible de supprimer l’utilisateur.');
        }
        return $this->redirectToRoute('superadmin_dashboard');

    }
    #[Route('organization/delete/{id}', name:'organization_delete', methods:['POST'])]
    public function deleteOrganieation(Organization $organization, Request $request, EntityManagerInterface $em, UserRepository $userRepository) : Response
    {
        if($this->isCsrfTokenValid('delete_organization_' . $organization->getId(), $request->get('_token'))){
           $organizationName = $organization->getName();

           // vérification de l'existence d'un user rattaché à une orrganization
           $usersFromOrganization = $userRepository->findBy(['organization' => $organization]);
           $countUsers = count($usersFromOrganization);

           if($countUsers > 0){
            $this->addFlash('danger', sprintf( 'Action impossible : L’établissement "%s" ne peut pas être supprimé car %d utilisateur(s) y est/sont encore rattaché(s)',
                    $organizationName,
                    $countUsers
                ));
              return $this->redirectToRoute('superadmin_dashboard');
           }
          $em->remove($organization);
          $em->flush();
           $this->addFlash( 'success', sprintf('L’établissement "%s" a été supprimé avec succès.', $organizationName));

        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide. Impossible de supprimer l’établissement.');
        }

        return $this->redirectToRoute('superadmin_dashboard');

    }

}
