<?php

namespace App\Controller;

use App\Entity\Performance;
use App\Entity\Season;
use App\Form\PerformanceFormType;
use App\Repository\PerformanceRepository;
use App\Service\CrudManagerService;
use App\Service\UserContextService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('performance')]
#[IsGranted('ROLE_USER')]

class PerformanceController extends AbstractController
{
    public function __construct(
         private CrudManagerService $crudManager,
         private UserContextService $userContext) {}

    #[Route('/', name: 'performance_index', methods:['GET'])]
    public function index(PerformanceRepository $performanceRepository): Response
    {
         $this->denyAccessUnlessGranted('PERFORMANCE_VIEW');
        
        $performances = $performanceRepository->findBy(['organization' => $this->userContext->getOrganization()]);
        return $this->render('performance/index.html.twig', ['performances' => $performances]);
    }
    #[Route('/new/{season}', name: 'performance_new', methods:['GET','POST'])]
    public function new( Request $request, Season $season, PerformanceRepository $performanceRepository): Response
    {
         $this->denyAccessUnlessGranted('PERFORMANCE_CREATE');
         $spentBudget = $performanceRepository->getTotalCostForSeason($season);
         $organization = $this->userContext->getOrganization();
         $performance = new Performance();
         $performance->setSeason($season);
         $performance->setOrganization($this->userContext->getOrganization());
        
         $formPerformance = $this->createForm(PerformanceFormType::class, $performance, [
            'user_organization'=> $organization,
         ]);
         $formPerformance->handleRequest($request);

        if ($formPerformance->isSubmitted() && $formPerformance->isValid()) {
       
            $this->crudManager->create($performance);
            $this->addFlash('success', 'La représentation a bien été créée.');
            return $this->redirectToRoute('season_show', ['id' => $season->getId()]);
        }
        return $this->render('performance/new.html.twig', [
                'performance'=> $performance,
                'season'=>$season,
                'form'=>$formPerformance->createView(),
        ]);
    }
    #[Route('/edit/{id}', name: 'performance_edit', methods:['GET', 'POST'])]
    public function edit(Performance $performance, Request $request): Response
    {
         $this->denyAccessUnlessGranted('PERFORMANCE_EDIT', $performance);
        $season = $performance->getSeason();
        $organization = $this->userContext->getOrganization();
        $formPerformance = $this->createForm(PerformanceFormType::class, $performance, [
            'user_organization'=>$organization,
        ]);
        $formPerformance->handleRequest($request);

        if ($formPerformance->isSubmitted() && $formPerformance->isValid()) {

            $this->crudManager->update($performance);
            $this->addFlash('success', 'La représentation a bien été actualisée.');
             return $this->redirectToRoute('season_show', ['id'=> $season->getId()]);
        }

       return $this->render('performance/edit.html.twig', [
                'performance'=> $performance,
                'season'=>$season,
                'form'=>$formPerformance->createView(),
        ]);

    }
    #[Route('/{id}', name: 'performance_show', methods:['GET'])]
    public function show(Performance $performance): Response
    {
         $this->denyAccessUnlessGranted('PERFORMANCE_VIEW');
        
        return $this->render('performance/show.html.twig', ['performance' => $performance]);
    }

    #[Route('/delete/{id}', name: 'performance_delete', methods: ['POST'])]
    public function delete(Request $request, Performance $performance): Response
    {
        $this->denyAccessUnlessGranted('PERFORMANCE_DELETE', $performance);
        $season = $performance->getSeason();

        if($this->isCsrfTokenValid('delete' . $performance->getId() , $request->request->get('_token'))) {
            $this->crudManager->delete($performance);
            $this->addFlash('success', 'La représenation a bien été supprimée.');
        }else{ $this->addFlash('danger', "CSRF invalide");}

        return $this->redirectToRoute('season_show', ['id'=> $season->getId()]);
    }


}