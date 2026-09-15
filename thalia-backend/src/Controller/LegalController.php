<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalController extends AbstractController
{
    #[Route('/privacy_policy', name: 'privacy_policy', methods:['GET'])]
    public function rgpd(): Response
    {
        return $this->render('legal/rgpd.html.twig', []);
    }
     #[Route('/accessibility', name: 'accessibility', methods:['GET'])]
    public function rgaa(): Response
    {
        return $this->render('legal/accessibility.html.twig', []);
    }
}
