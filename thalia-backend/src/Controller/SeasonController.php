<?php

namespace App\Controller;


use App\Entity\Season;
use App\Enum\FinancialTypeEnum;
use App\Form\SeasonFormType;
use App\Repository\FinancialRepository;
use App\Repository\PerformanceRepository;
use App\Repository\SeasonRepository;
use App\Repository\ShowRepository;
use App\Repository\VenueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\CrudManagerService;
use App\Service\UserContextService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('season')]
#[IsGranted('ROLE_USER')]

class SeasonController extends AbstractController

{   
    
    public function __construct(
         private CrudManagerService $crudManager,
         private UserContextService $userContext) {}
 
    #[Route('/', name: 'season_index', methods:['GET'])]
    public function index(SeasonRepository $seasonRepository): Response
    {
         $this->denyAccessUnlessGranted('SEASON_VIEW');
         $organization = $this->userContext->getOrganization();
        
        $seasons = $seasonRepository->findByOrganization($organization);
        $activeSeason = $seasonRepository->findActiveSeason($organization);
        $openSeasons = $seasonRepository->findOpenSeason($organization);
        return $this->render('season/index.html.twig', [
            'seasons' => $seasons,
            'activeSeason' => $activeSeason,
            'openSeasons' => $openSeasons]);
    }

    #[Route('/new', name: 'season_new', methods:['GET', 'POST'])]
    public function new( Request $request): Response
    {
         $this->denyAccessUnlessGranted('SEASON_CREATE');
         
         $season = new Season();
         $season->setOrganization($this->userContext->getOrganization());
        
        $formSeason = $this->createForm(SeasonFormType::class, $season , [
            'user_organization'=> $this->userContext->getOrganization(),
        ]);
        $formSeason->handleRequest($request);

        if ($formSeason->isSubmitted() && $formSeason->isValid()) {
       
            $this->crudManager->create($season);
            $this->addFlash('success', 'La saison a bien été créée.');
            return $this->redirectToRoute('season_index');
        }
        return $this->render('season/new.html.twig', ['season'=> $season, 'form' => $formSeason]);

    }

      #[Route('/edit/{id}', name: 'season_edit', methods:['GET', 'POST'])]
    public function edit(Season $season, Request $request): Response
    {
         $this->denyAccessUnlessGranted('SEASON_EDIT', $season);
       
        $formSeason = $this->createForm(SeasonFormType::class, $season , [
            'user_organization'=> $this->userContext->getOrganization(),
        ]);
        $formSeason->handleRequest($request);

        if ($formSeason->isSubmitted() && $formSeason->isValid()) {

            $this->crudManager->update($season);
            $this->addFlash('success', 'La saison a bien été actualisée.');
            return $this->redirectToRoute('season_index');
        }
        return $this->render('season/edit.html.twig', ['season'=> $season, 'form' => $formSeason]);

    }
    #[Route('/{id}', name: 'season_show', methods:['GET'])]
    public function show(
        Season $season,
        FinancialRepository $financialRepository,
        ShowRepository $showRepository,
        VenueRepository $venueRepository,
        PerformanceRepository $performanceRepository,
         ): Response
    {
         $this->denyAccessUnlessGranted('SEASON_VIEW', $season);

         // Récupération des tableaux filtrés (Débits & Crédits)
        $debits = $financialRepository->findBySeasonAndType($season, FinancialTypeEnum::DEBIT);
        $credits = $financialRepository->findBySeasonAndType($season, FinancialTypeEnum::CREDIT);

        // Calculs agrégés en BDD
        $totalDebitHt = $financialRepository->getTotalHtBySeasonAndType($season, FinancialTypeEnum::DEBIT);
        $totalCreditHt = $financialRepository->getTotalHtBySeasonAndType($season, FinancialTypeEnum::CREDIT);
        $netBalance = $totalCreditHt - $totalDebitHt;

        // affichage des élemnts du planning des spectacles 
        $venues = $venueRepository->findAll();
        $unassignedShows = $showRepository->findUnassignedForSeason($season);
        $spentBudget = $performanceRepository->getTotalCostForSeason($season);
        $remainingBudget = $season->getRemainingBudget($spentBudget);

        return $this->render('season/show.html.twig', [
            'season' => $season,
            'debits' => $debits,
            'credits' => $credits,
            'totalDebitHt' => $totalDebitHt,
            'totalCreditHt' => $totalCreditHt,
            'netBalance' => $netBalance,
            'venues' => $venues,
            'unassignedShows' => $unassignedShows,
            'spendBudget'=> $spentBudget,
            'remainingBudget' => $remainingBudget,
            ]);

    }
    #[Route('/delete/{id}', name: 'season_delete', methods: ['POST'])]
    public function delete(Request $request, Season $season): Response
    {
        $this->denyAccessUnlessGranted('SEASON_DELETE', $season);

        if($this->isCsrfTokenValid('delete' . $season->getId() , $request->request->get('_token'))) {
            
            $this->crudManager->delete($season);
        }

        return $this->redirectToRoute('season_index');
    }

}
