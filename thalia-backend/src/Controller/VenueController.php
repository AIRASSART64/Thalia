<?php

namespace App\Controller;

use App\Entity\Venue;
use App\Form\VenueFormType;
use App\Repository\VenueRepository;
use App\Service\CrudManagerService;
use App\Service\FileUpLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    ) {}

    #[Route('/', name: 'venue_index', methods:['GET'])]
    public function index(VenueRepository $venueRepository): Response
    {
        $this->denyAccessUnlessGranted('VENUE_VIEW');

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('L\'utilisateur doit être connecté avec un compte valide.');
        }
        $venues = $venueRepository->findBy(['organization' => $user->getOrganization()]);
        return $this->render('venue/index.html.twig', [
            'venues' => $venues,
        ]);
    } 
    #[Route('/new', name:'venue_new', methods:['GET', 'POST'])]
    public function new(Request $request, VenueRepository $venueRepository): Response
    {
        $this->denyAccessUnlessGranted('VENUE_CREATE');

        $venue = new Venue();

        $user = $this->getUser();
         if (!$user instanceof \App\Entity\User) {
        throw new \LogicException('L\'utilisateur doit être connecté avec un compte valide.');
        }

        $formVenue = $this->createForm(VenueFormType::class, $venue, [
            'current_organization'=> $user->getOrganization(),
        ]);
        $formVenue->handleRequest($request);
        if($formVenue->isSubmitted() && $formVenue->isValid()){
            /** @var UploadedFile|null $imageFile */
            $imageFile = $formVenue->get('venueImageFile')->getData();
            if ($imageFile) {
                $newImageName = $this->fileUpLoader->upload($imageFile, $this->getParameter('venues_images_directory'));
                if ($newImageName) {
                    $venue->setVenueImage($newImageName);
                } else {
                    $this->addFlash('error', 'Erreur lors du traitement de l\'image.');
                }
            }

            /** @var UploadedFile|null $planFile */
            $planFile = $formVenue->get('venuePlanFile')->getData();
            if ($planFile) {
                $newPlanName = $this->fileUpLoader->upload($planFile, $this->getParameter('venues_plans_directory'));
                if ($newPlanName) {
                    $venue->setVenuePlan($newPlanName);
                } else {
                    $this->addFlash('error', 'Erreur lors du traitement du plan technique.');
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

       $user = $this->getUser();
         if (!$user instanceof \App\Entity\User) {
        throw new \LogicException('L\'utilisateur doit être connecté avec un compte valide.');
        }
        $oldImage = $venue->getVenueImage();
        $oldPlan = $venue->getVenuePlan();
        $formVenue = $this->createForm(VenueFormType::class, $venue, [
            'current_organization'=> $user->getOrganization(),
        ]);
        $formVenue->handleRequest($request);
        if($formVenue->isSubmitted() && $formVenue->isValid()){
            /** @var UploadedFile|null $imageFile */
            $imageFile = $formVenue->get('venueImageFile')->getData();
            if ($imageFile) {
                $newImageName = $this->fileUpLoader->upload($imageFile, $this->getParameter('venues_images_directory'));
                if ($newImageName) {
                    $venue->setVenueImage($newImageName);
                    if ($oldImage) {
                        $this->fileUpLoader->remove($this->getParameter('venues_images_directory'), $oldImage);
                    }
                }
            }
            /** @var UploadedFile|null $planFile */
            $planFile = $formVenue->get('venuePlanFile')->getData();
            if ($planFile) {
                $newPlanName = $this->fileUpLoader->upload($planFile, $this->getParameter('venues_plans_directory'));
                if ($newPlanName) {
                    $venue->setVenuePlan($newPlanName);
                    if ($oldPlan) {
                        $this->fileUpLoader->remove($this->getParameter('venues_plans_directory'), $oldPlan);
                    }
                }
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
            $oldImage = $venue->getVenueImage() ? $this->getParameter('venues_images_directory') . '/' . $venue->getVenueImage() : null;
            $oldPlan = $venue->getVenuePlan() ? $this->getParameter('venues_plans_directory') . '/' . $venue->getVenuePlan() : null;

            $this->crudManager->delete($venue);

           $this->fileUpLoader->remove($this->getParameter('venues_images_directory'), $oldImage);
           $this->fileUpLoader->remove($this->getParameter('venues_plans_directory'), $oldPlan);

            $this->addFlash('success', 'Suppression réussie.');
        }

        return $this->redirectToRoute('venue_index');
    }

   
}