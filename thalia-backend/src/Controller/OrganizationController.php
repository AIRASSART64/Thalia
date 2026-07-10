<?php

namespace App\Controller;

use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/organization')]
#[IsGranted('ROLE_USER')] 
final class OrganizationController extends AbstractController
{
    #[Route('', name: 'organization', methods: ['GET'])]
    public function index(OrganizationRepository $organizationRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $organization = $organizationRepository->findByUser($user) ;

        // Si l'utilisateur n'a pas d'organisation (cas particulier), on gère l'erreur ou on redirige
        if (!$organization) {
            $this->addFlash('danger', 'Aucune organisation associée à votre compte.');
            return $this->redirectToRoute('app_forgot_password_request'); // Ou une autre route par défaut
        }

        return $this->render('organization/index.html.twig', [
            'organization' => $organization,
        ]);
    }
 
}