<?php

namespace App\Controller;

use App\Entity\Show;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FileController extends AbstractController
{
    public function __construct(
        private ParameterBagInterface $params
    ) {}

    #[Route('/secure-uploads/shows/{id}', name: 'secure_show_image', methods: ['GET'])]
    #[IsGranted('SHOW_VIEW', subject: 'show')] 
    public function getShowImage(Show $show): Response
    {
        $filename = $show->getArtworkUrl();

        // Gestion du cas où aucun ficher n'est enregistré
        if (!$filename) {
            throw $this->createNotFoundException("Ce spectacle n'a pas d'affiche enregistrée.");
        }

        // Récupération du fichier dans son dossier de stockage
        $filePath = $this->params->get('shows_directory') . '/' . $filename;

        // Gestion du fichier introuvable
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException("Le fichier image n'existe plus sur le serveur.");
        }

        // Le fichier est envoyé directement au navigateur
        return new BinaryFileResponse($filePath);
    }
}