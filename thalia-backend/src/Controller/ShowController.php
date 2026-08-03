<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Show;
use App\Entity\ShowContact;
use App\Form\ShowFormType;
use App\Repository\ShowRepository;
use App\Service\CrudManagerService;
use App\Service\FileDownLoaderService;
use App\Service\FileUpLoader;
use App\Service\UserContextService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/show')]
#[IsGranted('ROLE_USER')]

class ShowController extends AbstractController
{
    public function __construct(
        private CrudManagerService $crudManager,
        private FileUpLoader $fileUpLoader,
        private UserContextService $userContext,
        private ParameterBagInterface $params
    ) {}

    #[Route('/', name: 'show_index', methods: ['GET'])]
    public function index(ShowRepository $showRepository): Response
    {
        $this->denyAccessUnlessGranted('SHOW_VIEW');

        $shows = $showRepository->findByOrganizationWithThemes($this->userContext->getOrganization());

        return $this->render('show/index.html.twig', ['shows' => $shows]);
    }

    #[Route('/new', name: 'show_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SHOW_CREATE');
        $show = new Show();

        $formShow = $this->createForm(ShowFormType::class, $show, [
            'user_organization' => $this->userContext->getOrganization(),
        ]);
        $formShow->handleRequest($request);

        if ($formShow->isSubmitted() && $formShow->isValid()) {

            /** @var UploadedFile|null $imageFile */
            $imageFile = $formShow->get('artworkUrl')->getData();

            if ($imageFile) {
                $newFilename = $this->fileUpLoader->upload($imageFile, $this->params->get('shows_images_directory'));
                if ($newFilename) {
                    $show->setArtworkUrl($newFilename);
                }
            }
            /** @var UploadedFile|null $artisticFile */
            $artisticFile = $formShow->get('artistic_file')->getData();

            if ($artisticFile) {
                $newFilename = $this->fileUpLoader->upload($artisticFile, $this->params->get('shows_documents_directory'));
                if ($newFilename) {
                    $show->setArtisticFile($newFilename);
                }
            }

            $this->crudManager->create($show);
            return $this->redirectToRoute('show_show', ['id' => $show->getId()]);
        }
        return $this->render('show/new.html.twig', ['show' => $show, 'form' => $formShow]);
    }
    #[Route('/edit/{id}', name: 'show_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Show $show): Response
    {
        $this->denyAccessUnlessGranted('SHOW_EDIT', $show);
        $oldArtwork = $show->getArtworkUrl();
        $oldArtisticFile = $show->getArtisticFile();

        $formShow = $this->createForm(ShowFormType::class, $show, [
            'user_organization' => $this->userContext->getOrganization(),
        ]);

        $formShow->handleRequest($request);

        if ($formShow->isSubmitted() && $formShow->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $formShow->get('artworkUrl')->getData();
            if ($imageFile) {
                $newFilename = $this->fileUpLoader->upload($imageFile, $this->params->get('shows_images_directory'));

                if ($newFilename) {
                    //  Suppression de l'ancienne affiche physique
                    if ($oldArtwork) {
                        $oldFilePath = $this->params->get('shows_images_directory') . '/' . $oldArtwork;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    $show->setArtworkUrl($newFilename);
                }
            } else {
                // Si aucune image n'est soumise, on réinjecte l'ancienne 
                $show->setArtworkUrl($oldArtwork);
            }
            // supression de l'ancien document
            /** @var UploadedFile|null $artisticFile */
            $artisticFile = $formShow->get('artistic_file')->getData();
            if ($artisticFile) {
                $newFilename = $this->fileUpLoader->upload($artisticFile, $this->params->get('shows_documents_directory'));

                if ($newFilename) {
                    //  Suppression de l'ancien document
                    if ($oldArtisticFile) {
                        $oldFilePath = $this->params->get('shows_documents_directory') . '/' . $oldArtisticFile;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    $show->setArtisticFile($newFilename);
                }
            } else {
                // Si aucun document n'est soumis, on réinjecte l'ancien 
                $show->setArtisticFile($oldArtisticFile);
            }


            $this->crudManager->update($show);
            return $this->redirectToRoute('show_index');
        }
        return $this->render('show/edit.html.twig', ['show' => $show, 'form' => $formShow]);
    }
    #[Route('/{id}', name: 'show_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Show $show): Response
    {
        $this->denyAccessUnlessGranted('SHOW_VIEW', $show);

        return $this->render('show/show.html.twig', ['show' => $show]);
    }

   #[Route('/{id}/contact/add', name: 'show_contact_add', methods: ['GET', 'POST'])]
    public function addShow(Request $request, Contact $contact): Response
    {
        $this->denyAccessUnlessGranted('CONTACT_EDIT', $contact);

        $show = new Show();
        $organization = $contact->getOrganization();

        // 1. Définition de l'organisation et de la relation ShowContact
        $show->setOrganization($organization);
        
        $showContact = new ShowContact();
        $showContact->setContact($contact);
        $show->addShowContact($showContact);

        // 2. Formulaire
        $formShow = $this->createForm(ShowFormType::class, $show, [
            'user_organization' => $organization,
        ]);
        $formShow->handleRequest($request);

        if ($formShow->isSubmitted() && $formShow->isValid()) {

            // ── Traitement Affiche (Artwork) ──
            /** @var UploadedFile|null $imageFile */
            $imageFile = $formShow->get('artworkUrl')->getData();

            if ($imageFile) {
                $newFilename = $this->fileUpLoader->upload($imageFile, $this->params->get('shows_images_directory'));
                if ($newFilename) {
                    $show->setArtworkUrl($newFilename);
                }
            }

            // ── Traitement Dossier Artistique (PDF) ──
            /** @var UploadedFile|null $artisticFile */
            $artisticFile = $formShow->get('artistic_file')->getData();

            if ($artisticFile) {
                $newFilename = $this->fileUpLoader->upload($artisticFile, $this->params->get('shows_documents_directory'));
                if ($newFilename) {
                    $show->setArtisticFile($newFilename);
                }
            }

            // 3. Persistance
            $this->crudManager->create($show);

            $this->addFlash('success', 'Le spectacle a été créé et rattaché avec succès.');

            return $this->redirectToRoute('contact_show', [
                'id' => $contact->getId(),
                '_fragment' => 'tab-shows'
            ]);
        }

        return $this->render('show/new.html.twig', [
            'contact' => $contact,
            'form' => $formShow->createView(),
        ]);
    }
    #[Route('/{showId}/contact/{id}/detach', name: 'show_contact_detach', methods: ['POST'])]
    public function detachContact(Request $request, int $showId, ShowContact $showContact): Response
    {

        $this->denyAccessUnlessGranted('SHOW_EDIT', $showContact->getEvent());

        if ($this->isCsrfTokenValid('detach_' . $showContact->getId(), $request->request->get('_token'))) {

            $this->crudManager->delete($showContact);

            $this->addFlash('success', 'Le contact a été détaché du spectacle.');
        } else {
            $this->addFlash('error', 'Jeton de sécurité invalide.');
        }

        return $this->redirectToRoute('show_show', [
            'id' => $showId,
            '_fragment' => 'tab_contacts'
        ]);
    }

    // #[Route('/{id}/setup-contacts', name:'show_setup_contacts', requirements:['id'=>'\d+'], methods:['GET'])]
    // public function setupContacts(Show $show, ): Response
    // {
    //     $this->denyAccessUnlessGranted('SHOW_CREATE');
    //     return $this->render('show/setup_contacts.html.twig', [
    //         'show'=> $show,
    //     ]);


    // }

    #[Route('/{id}/pdf', name: 'secure_show_pdf', methods: ['GET'])]
    public function downLoadPdf(Show $show, FileDownLoaderService $fileDownloader): BinaryFileResponse
    {
        if (!$show->getArtisticFile()) {
            throw $this->createNotFoundException('Aucun dossier artistique associé.');
        }
        return $fileDownloader->downloadFromUploads('shows/documents', $show->getArtisticFile());
    }

    #[Route('/delete/{id}', name: 'show_delete', methods: ['POST'])]
    public function delete(Request $request, Show $show): Response
    {
        $this->denyAccessUnlessGranted('SHOW_DELETE', $show);

        if ($this->isCsrfTokenValid('delete' . $show->getId(), $request->request->get('_token'))) {
            // suppression de l'image associée au spectacle
            if ($show->getArtworkUrl()) {
                $filePath = $this->params->get('shows_images_directory') . '/' . $show->getArtworkUrl();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            // suppression du document associé au spectacle
            if ($show->getArtisticFile()) {
                $filePath = $this->params->get('shows_documents_directory') . '/' . $show->getArtisticFile();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $this->crudManager->delete($show);
        }


        return $this->redirectToRoute('show_index', []);
    }
}
