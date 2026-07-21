<?php

namespace App\Controller;

use App\Entity\Equipment;
use App\Form\EquipmentFormType;
use App\Repository\EquipmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\CrudManagerService;
use App\Service\UserContextService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('equipment')]
#[IsGranted('ROLE_USER')]

class EquipmentController extends AbstractController

{   
    
    public function __construct(
         private CrudManagerService $crudManager,
         private UserContextService $userContext) {}
 
    #[Route('/', name: 'equipment_index', methods:['GET'])]
    public function index(EquipmentRepository $equipmentRepository): Response
    {
         $this->denyAccessUnlessGranted('EQUIPMENT_VIEW');
        
        $equipments = $equipmentRepository->findBy(['organization' => $this->userContext->getOrganization()]);
        return $this->render('equipment/index.html.twig', ['equipments' => $equipments]);
    }
    #[Route('/new', name: 'equipment_new', methods:['GET', 'POST'])]
    public function new( Request $request): Response
    {
         $this->denyAccessUnlessGranted('EQUIPMENT_CREATE');
         
         $equipment = new Equipment();
         $equipment->setOrganization($this->userContext->getOrganization());
        
        $formEqupment = $this->createForm(EquipmentFormType::class, $equipment , [
            'user_organization'=> $this->userContext->getOrganization(),
        ]);
        $formEqupment->handleRequest($request);

        if ($formEqupment->isSubmitted() && $formEqupment->isValid()) {

            $this->crudManager->create($equipment);
            $this->addFlash('success', 'L\'équipement a bien été créé.');
            return $this->redirectToRoute('equipment_index');
        }
        return $this->render('equipment/new.html.twig', ['equipment'=> $equipment, 'form' => $formEqupment]);

    }
      #[Route('/edit/{id}', name: 'equipment_edit', methods:['GET', 'POST'])]
    public function edit(Equipment $equipment, Request $request): Response
    {
         $this->denyAccessUnlessGranted('EQUIPMENT_EDIT', $equipment);
       
        $formEqupment = $this->createForm(EquipmentFormType::class, $equipment , [
            'user_organization'=> $this->userContext->getOrganization(),
        ]);
        $formEqupment->handleRequest($request);

        if ($formEqupment->isSubmitted() && $formEqupment->isValid()) {

            $this->crudManager->update($equipment);
            $this->addFlash('success', 'L\'équipement a bien été actualisé.');
            return $this->redirectToRoute('equipment_index');
        }
        return $this->render('equipment/edit.html.twig', ['equipment'=> $equipment, 'form' => $formEqupment]);

    }
    #[Route('/{id}', name: 'equipment_show', methods:['GET'])]
    public function show(Equipment $equipment): Response
    {
         $this->denyAccessUnlessGranted('EQUIPMENT_VIEW', $equipment);
     
        return $this->render('equipment/show.html.twig', ['equipment'=> $equipment]);

    }
    #[Route('/delete/{id}', name: 'equipment_delete', methods: ['POST'])]
    public function delete(Request $request, Equipment $equipment): Response
    {
        $this->denyAccessUnlessGranted('EQUIPMENT_DELETE', $equipment);

        if($this->isCsrfTokenValid('delete' . $equipment->getId() , $request->get('_token'))) {
            
            $this->crudManager->delete($equipment);
        }

        return $this->redirectToRoute('equipment_index');
    }


}
