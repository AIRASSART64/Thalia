<?php
namespace App\Controller;

use App\Entity\Performance;
use App\Entity\Season;
use App\Repository\PerformanceRepository;
use App\Repository\ShowRepository;
use App\Repository\VenueRepository;
use App\Service\CrudManagerService;
use App\Service\UserContextService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/planning')]
class PlanningController extends AbstractController
{
    public function __construct(
        private UserContextService $userContext,
        private CrudManagerService $crudManager,
        private PerformanceRepository $performanceRepo
    ) {}

    
     //Reçoit le drop d'un spectacle et crée/déplace la représentation
    
    #[Route('/drop', name: 'planning_drop', methods: ['POST'])]
    public function handleDrop(Request $request, ShowRepository $showRepo, VenueRepository $venueRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Données envoyées par JS lors du drop
        $showId = $data['show_id'] ?? null;
        $venueId = $data['venue_id'] ?? null;
        $seasonId = $data['season_id'] ?? null;
        $startStr = $data['start_time'] ?? null; // Ex: '2026-10-24T16:00:00'
        $endStr = $data['end_time'] ?? null;     // Ex: '2026-10-24T19:30:00'

        if (!$showId || !$venueId || !$startStr) {
            return new JsonResponse(['success' => false, 'message' => 'Données incomplètes.'], 400);
        }

        $show = $showRepo->find($showId);
        $venue = $venueRepo->find($venueId);
        $start = new \DateTime($startStr);
        $end = $endStr ? new \DateTime($endStr) : (clone $start)->modify('+2 hours');

        // Création de la nouvelle représentation
        $performance = new Performance();
        $performance->setSeasonShow($show);
        $performance->setVenue($venue);
        $performance->setDateTimeStart($start);
        $performance->setDateTimeEnd($end);
        $performance->setOrganization($this->userContext->getOrganization());
        
        // Temps de montage/démontage par défaut si spécifiés sur le spectacle
        $performance->setSetupDurationMin(120); 
        $performance->setTeardownDurationMin(60);

        $this->crudManager->create($performance);
      
        return new JsonResponse([
            'success' => true,
            'performance_id' => $performance->getId(),
            'message' => 'Représentation positionnée avec succès !'
        ]);
    }
    // récupération des venues 
    #[Route('/venues', name: 'planning_venues', methods: ['GET'])]
    public function getVenues(VenueRepository $venueRepository): JsonResponse
    {
        $venues = $venueRepository->findAll();
        $data = [];

        foreach ($venues as $venue) {
            $data[] = [
                'id' => $venue->getId(),
                'name' => $venue->getName(),
            ];
        }

        return $this->json($data);
    }
    
     //Fournit les événements au calendrier (JSON)
     
    #[Route('/events/{season}', name: 'planning_events', methods: ['GET'])]
    public function getEvents(Season $season): JsonResponse
    {
        $performances = $this->performanceRepo->findBy(['season' => $season]);
        $events = [];

        foreach ($performances as $perf) {
            $events[] = [
                'id' => $perf->getId(),
                'resourceId' => $perf->getVenue()?->getId(), // ID de la salle (Colonne)
                'title' => $perf->getSeasonShow()?->getTitle(),
                'start' => $perf->getDateTimeStart()?->format('c'),
                'end' => $perf->getDateTimeEnd()?->format('c'),
                'setupDuration' => $perf->getSetupDurationMin(),
                'teardownDuration' => $perf->getTeardownDurationMin(),
                'cost' => $perf->getTotalCost(),
            ];
        }

        return new JsonResponse($events);
    }
}