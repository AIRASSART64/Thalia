<?php

namespace App\Controller;


use App\Entity\Financial;
use App\Form\FinancialFormType;
use App\Repository\FinancialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\CrudManagerService;
use App\Service\UserContextService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('financial')]
#[IsGranted('ROLE_USER')]

class FinancialController extends AbstractController

{   
    
    public function __construct(
         private CrudManagerService $crudManager,
         private UserContextService $userContext) {}
 
    #[Route('/', name: 'financial_index', methods:['GET'])]
    public function index(FinancialRepository $financialRepository): Response
    {
         $this->denyAccessUnlessGranted('FINANCIAL_VIEW');
        
        $financials = $financialRepository->findBy(['organization' => $this->userContext->getOrganization()]);
        return $this->render('financial/index.html.twig', ['financials' => $financials]);
    }

    #[Route('/new', name: 'financial_new', methods:['GET', 'POST'])]
    public function new( Request $request): Response
    {
         $this->denyAccessUnlessGranted('FINANCIAL_CREATE');
         
         $financial = new Financial();
         $financial->setOrganization($this->userContext->getOrganization());
        
        $formFinancial = $this->createForm(FinancialFormType::class, $financial , [
            'user_organization'=> $this->userContext->getOrganization(),
        ]);
        $formFinancial->handleRequest($request);

        if ($formFinancial->isSubmitted() && $formFinancial->isValid()) {
       
            $this->crudManager->create($financial);
            $this->addFlash('success', 'La ligne budgétaire a bien été créée.');
            return $this->redirectToRoute('financial_index');
        }
        return $this->render('financial/new.html.twig', ['financial'=> $financial, 'form' => $formFinancial]);

    }

      #[Route('/edit/{id}', name: 'financial_edit', methods:['GET', 'POST'])]
    public function edit(Financial $financial, Request $request): Response
    {
         $this->denyAccessUnlessGranted('FINANCIAL_EDIT', $financial);
       
        $formFinancial = $this->createForm(FinancialFormType::class, $financial , [
            'user_organization'=> $this->userContext->getOrganization(),
        ]);
        $formFinancial->handleRequest($request);

        if ($formFinancial->isSubmitted() && $formFinancial->isValid()) {

            $this->crudManager->update($financial);
            $this->addFlash('success', 'La ligne budgétaire a bien été actualisée.');
            return $this->redirectToRoute('financial_index');
        }
        return $this->render('financial/edit.html.twig', ['financial'=> $financial, 'form' => $formFinancial]);

    }
    #[Route('/{id}', name: 'financial_show', methods:['GET'])]
    public function show(Financial $financial): Response
    {
         $this->denyAccessUnlessGranted('FINANCIAL_VIEW', $financial);
     
        return $this->render('financial/show.html.twig', ['financial'=> $financial]);

    }
    #[Route('/delete/{id}', name: 'financial_delete', methods: ['POST'])]
    public function delete(Request $request, Financial $financial): Response
    {
        $this->denyAccessUnlessGranted('FINANCIAL_DELETE', $financial);

        if($this->isCsrfTokenValid('delete' . $financial->getId() , $request->get('_token'))) {
            
            $this->crudManager->delete($financial);
        }

        return $this->redirectToRoute('financial_index');
    }


}