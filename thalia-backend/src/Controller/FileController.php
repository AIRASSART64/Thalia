<?php

namespace App\Controller;

use App\Entity\Show;
use App\Entity\Venue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }
    #[Route('/secure-uploads/venues/image/{id}', name: 'secure_venue_image', methods: ['GET'])]
    #[IsGranted('VENUE_VIEW', subject: 'venue')] 
    public function getVenueImage(Venue $venue): Response
    {
        $filename = $venue->getVenueImage(); 

        if (!$filename) {
            throw $this->createNotFoundException("Cette salle n'a pas d'image enregistrée.");
        }

        // Utilise le paramètre correspondant au dossier des salles (ex: 'venues_directory')
        $filePath = $this->params->get('venues_images_directory') . '/' . $filename;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException("Le fichier image de la salle n'existe plus sur le serveur.");
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }
      #[Route('/secure-uploads/venues/plan/{id}', name: 'secure_venue_paln', methods: ['GET'])]
    #[IsGranted('VENUE_VIEW', subject: 'venue')] 
    public function getVenuePlan(Venue $venue): Response
    {
        $filename = $venue->getVenuePlan(); 

        if (!$filename) {
            throw $this->createNotFoundException("Cette salle n'a pas de plan enregistrée.");
        }

        // Utilise le paramètre correspondant au dossier des salles (ex: 'venues_directory')
        $filePath = $this->params->get('venues_plans_directory') . '/' . $filename;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException("Le plan de la salle n'existe plus sur le serveur.");
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }
}