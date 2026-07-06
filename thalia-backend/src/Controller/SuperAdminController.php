<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function dashboard(UserRepository $userRepository): Response
    {
        return $this->render('superadmin/index.html.twig', [
            'users' => $userRepository->findAll(),
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
}