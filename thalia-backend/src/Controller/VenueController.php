<?php

namespace App\Controller;

use App\Entity\Venue;
use App\Form\VenueFormType;
use App\Repository\VenueRepository;
use App\Service\CrudManagerService;
use App\Service\FileUpLoader;
use App\Service\UserContextService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/venue')]
#[IsGranted('ROLE_USER')]
class VenueController extends AbstractController
{
    public function __construct(
        private CrudManagerService $crudManager,
        private FileUpLoader $fileUpLoader,
        private ParameterBagInterface $params,
        private UserContextService $userContext,
    ) {}

    #[Route('/', name: 'venue_index', methods:['GET'])]
    public function index(VenueRepository $venueRepository): Response
    {
        $this->denyAccessUnlessGranted('VENUE_VIEW');

        $venues = $venueRepository->findBy(['organization' => $this->userContext->getOrganization()]);
        return $this->render('venue/index.html.twig', [
            'venues' => $venues,
        ]);
    } 
    #[Route('/new', name:'venue_new', methods:['GET', 'POST'])]
    public function new(Request $request, VenueRepository $venueRepository): Response
    {
        $this->denyAccessUnlessGranted('VENUE_CREATE');

        $venue = new Venue();

        $formVenue = $this->createForm(VenueFormType::class, $venue, [
            'user_organization'=> $this->userContext->getOrganization(),
        ]);
        $formVenue->handleRequest($request);
        if($formVenue->isSubmitted() && $formVenue->isValid()){
            // 1. Upload de l'Image de la salle
            /** @var UploadedFile|null $imageFile */
            $imageFile = $formVenue->get('venueImageFile')->getData();
            if ($imageFile) {
                $newImageName = $this->fileUpLoader->upload($imageFile, $this->params->get('venues_images_directory'));
                if ($newImageName) {
                    $venue->setVenueImage($newImageName);
                }
            }

            // 2. Upload du Plan technique
            /** @var UploadedFile|null $planFile */
            $planFile = $formVenue->get('venuePlanFile')->getData();
            if ($planFile) {
                $newPlanName = $this->fileUpLoader->upload($planFile, $this->params->get('venues_plans_directory'));
                if ($newPlanName) {
                    $venue->setVenuePlan($newPlanName);
                }
            }
  
            $this->crudManager->create($venue);
            $this->addFlash('success', 'La nouvelle salle a bien été créée.');
            return $this->redirectToRoute('organization');
        }
        return $this->render('venue/new.html.twig', [ 'venue'=>$venue, 'form'=>$formVenue]);
    }
     #[Route('/edit/{id}', name: 'venue_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Venue $venue): Response
    {
        $this->denyAccessUnlessGranted('VENUE_EDIT', $venue);

      
        $oldImage = $venue->getVenueImage();
        $oldPlan = $venue->getVenuePlan();
        $formVenue = $this->createForm(VenueFormType::class, $venue, [
            'user_organization'=> $this->userContext->getOrganization(),
        ]);
        $formVenue->handleRequest($request);
        if($formVenue->isSubmitted() && $formVenue->isValid()){
            // 1. Traitement de l'Image
            /** @var UploadedFile|null $imageFile */
            $imageFile = $formVenue->get('venueImageFile')->getData();
            if ($imageFile) {
                $newImageName = $this->fileUpLoader->upload($imageFile, $this->params->get('venues_images_directory'));
                if ($newImageName) {
                    // Suppression de l'ancienne image physique
                    if ($oldImage) {
                        $oldImagePath = $this->params->get('venues_images_directory') . '/' . $oldImage;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $venue->setVenueImage($newImageName);
                }
            } else {
                $venue->setVenueImage($oldImage);
            }

            // 2. Traitement du Plan
            /** @var UploadedFile|null $planFile */
            $planFile = $formVenue->get('venuePlanFile')->getData();
            if ($planFile) {
                $newPlanName = $this->fileUpLoader->upload($planFile, $this->params->get('venues_plans_directory'));
                if ($newPlanName) {
                    // Suppression du plan obsolète
                    if ($oldPlan) {
                        $oldPlanPath = $this->params->get('venues_plans_directory') . '/' . $oldPlan;
                        if (file_exists($oldPlanPath)) {
                            unlink($oldPlanPath);
                        }
                    }
                    $venue->setVenuePlan($newPlanName);
                }
            } else {
                $venue->setVenuePlan($oldPlan);
            }
            $this->crudManager->update($venue);
            $this->addFlash('success', 'Modification réussie.');
            return $this->redirectToRoute('organization');
        }
        return $this->render('venue/edit.html.twig', ['venue'=>$venue, 'form'=>$formVenue]);
    }
     #[Route('/{id}', name: 'venue_show', methods: ['GET'])]
    public function show(Venue $venue): Response
    {
        $this->denyAccessUnlessGranted('VENUE_VIEW', $venue);

        return $this->render('venue/show.html.twig', ['venue' => $venue, ]);
    }

    #[Route('/delete/{id}', name: 'venue_delete', methods: ['POST'])]
    public function delete(Request $request, Venue $venue): Response
    {
        $this->denyAccessUnlessGranted('VENUE_DELETE', $venue);
        
        $token = $request->getPayload()->get('_token') ?? $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $venue->getId(), $token)) {
            if ($venue->getVenueImage()) {
                $imagePath = $this->params->get('venues_images_directory') . '/' . $venue->getVenueImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Suppression physique du plan de la salle
            if ($venue->getVenuePlan()) {
                $planPath = $this->params->get('venues_plans_directory') . '/' . $venue->getVenuePlan();
                if (file_exists($planPath)) {
                    unlink($planPath);
                }
            }

            $this->crudManager->delete($venue);
        }

        return $this->redirectToRoute('organization');
    }

   
}