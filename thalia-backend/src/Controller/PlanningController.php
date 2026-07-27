<?php

namespace App\Controller;

use App\Entity\Performance;
use App\Entity\Season;
use App\Repository\PerformanceRepository;
use App\Repository\SeasonRepository;
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
        private PerformanceRepository $performanceRepository,
    ) {}

    /**
     * Reçoit le DROP d'un spectacle OU le DEPLACEMENT/REDIMENSIONNEMENT
     */
    #[Route('/drop', name: 'planning_drop', methods: ['POST'])]
    public function handleDrop(
        Request $request,
        ShowRepository $showRepository,
        VenueRepository $venueRepository,
        SeasonRepository $seasonRepository,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);


        $performanceId = $data['performanceId'] ?? null;
        $showId        = $data['showId'] ?? null;
        $venueId       = $data['venueId'] ?? null;
        $seasonId      = $data['seasonId'] ?? null;
        $startStr      = $data['start'] ?? null;
        $endStr        = $data['end'] ?? null;


        if (!$venueId || !$startStr) {
            return new JsonResponse(['success' => false, 'message' => 'Salle ou date manquante.'], 400);
        }

        $venue = $venueRepository->find($venueId);
        if (!$venue) {
            return new JsonResponse(['success' => false, 'message' => 'Salle introuvable.'], 404);
        }

        $start = new \DateTime($startStr);
        $end   = $endStr ? new \DateTime($endStr) : (clone $start)->modify('+2 hours');

        // --- VERIFICATION ANTI-CHEVAUCHEMENT ---
        if ($this->hasOverlap($venue, $start, $end, $performanceId)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Impossible : la salle est déjà occupée sur ce créneau !'
            ], 409);
        }

        // --- CAS 1 : DEPLACEMENT / REDIMENSIONNEMENT ---
        if ($performanceId) {
            $performance = $this->performanceRepository->find($performanceId);
            if (!$performance) {
                return new JsonResponse(['success' => false, 'message' => 'Représentation introuvable.'], 404);
            }

            $performance->setVenue($venue);
            $performance->setDateTimeStart($start);
            $performance->setDateTimeEnd($end);

            $this->crudManager->update($performance);
            $season = $performance->getSeason();
            if (!$season && $seasonId) {
                $season = $seasonRepository->find($seasonId);
            }
            $totalSpent = $this->performanceRepository->getTotalCostForSeason($season);

            return new JsonResponse([
                'success' => true,
                'performanceId' => $performance->getId(),
                'budgetData' => [
                    'totalSpent' => $totalSpent,
                ],
                'message' => 'Mise à jour enregistrée.'
            ]);
        }

        // --- CAS 2 : CREATION APRES DROP DEPUIS LA SIDEBAR ---
        if (!$showId) {
            return new JsonResponse(['success' => false, 'message' => 'Spectacle manquant.'], 400);
        }

        $show = $showRepository->find($showId);
        if (!$show) {
            return new JsonResponse(['success' => false, 'message' => 'Spectacle introuvable.'], 404);
        }

        $performance = new Performance();
        $performance->setSeasonShow($show);
        $performance->setVenue($venue);
        $performance->setDateTimeStart($start);
        $performance->setDateTimeEnd($end);

        // Organisation & Saison
        if ($this->userContext->getOrganization()) {
            $performance->setOrganization($this->userContext->getOrganization());
        }
        if ($seasonId) {
            $season = $seasonRepository->find($seasonId);
            if ($season) $performance->setSeason($season);
        }

        $this->crudManager->create($performance);

        return new JsonResponse([
            'success' => true,
            'performanceId' => $performance->getId(),
            'message' => 'Représentation créée avec succès !'
        ]);
    }

    /**
     * Suppression d'une représentation (Clic sur le calendrier)
     */
    #[Route('/delete/{id}', name: 'planning_delete', methods: ['DELETE'])]
    public function deletePerformance(Performance $performance): JsonResponse
    {
        $show = $performance->getSeasonShow();
        $season = $performance->getSeason();
        $showData = null;
        $totalSpent = $this->performanceRepository->getTotalCostForSeason($season);

        if ($show) {
            $showData = [
                'id' => $show->getId(),
                'title' => $show->getTitle(),
                'companyName' => method_exists($show, 'getCompanyName') ? $show->getCompanyName() : '',
                'estimatedCost' => method_exists($show, 'getEstimatedCost') ? $show->getEstimatedCost() : 0,
                'durationMin' => method_exists($show, 'getDurationMin') ? $show->getDurationMin() : 120
            ];
        }

        $this->crudManager->delete($performance);

        return new JsonResponse([
            'success' => true,
            'show' => $showData,
            'totalSpent' => $totalSpent,
        ]);
    }

    /**
     * Liste des Salles (Colonnes)
     */
    #[Route('/venues', name: 'planning_venues', methods: ['GET'])]
    public function getVenues(VenueRepository $venueRepository): JsonResponse
    {
        $venues = $venueRepository->findAll();
        $data = [];

        foreach ($venues as $venue) {
            $data[] = [
                'id' => $venue->getId(),
                'title' => $venue->getName(),
            ];
        }

        return $this->json($data);
    }

    /**
     * Événements existants pour la saison
     */
    #[Route('/events/{season}', name: 'planning_events', methods: ['GET'])]
    public function getEvents(Season $season): JsonResponse
    {
        $performances = $this->performanceRepository->findBy(['season' => $season]);
        $events = [];

        foreach ($performances as $perf) {
            $show = $perf->getSeasonShow();
            $events[] = [
                'id' => $perf->getId(),
                'resourceId' => $perf->getVenue()?->getId(),
                'title' => $show?->getTitle() ?? 'Représentation',
                'start' => $perf->getDateTimeStart()?->format(\DateTimeInterface::ATOM),
                'end' => $perf->getDateTimeEnd()?->format(\DateTimeInterface::ATOM),
                'backgroundColor' => '#2563eb',
                'borderColor' => 'transparent',
                'extendedProps' => [
                    'showId' => $show?->getId(),
                    'company' => method_exists($show, 'getCompanyName') ? $show->getCompanyName() : ''
                ]
            ];
        }

        return $this->json($events);
    }

    /**
     * Détection des chevauchements en base
     */
    private function hasOverlap($venue, \DateTime $start, \DateTime $end, ?int $excludeId = null): bool
    {
        $qb = $this->performanceRepository->createQueryBuilder('p')
            ->where('p.venue = :venue')
            ->andWhere('p.date_time_start < :end')
            ->andWhere('p.date_time_end > :start')
            ->setParameter('venue', $venue)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if ($excludeId) {
            $qb->andWhere('p.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return count($qb->getQuery()->getResult()) > 0;
    }
}
