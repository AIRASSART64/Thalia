<?php

namespace App\Controller;

use App\Entity\Show;
use App\Form\ShowFormType;
use App\Repository\ShowRepository;
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

#[Route('/show')]
#[IsGranted('ROLE_USER')]

class ShowController extends AbstractController
{
    public function __construct(
        private CrudManagerService $crudManager,
        private FileUpLoader $fileUpLoader,
        private UserContextService $userContext,
        private ParameterBagInterface $params)
    {}
 
    #[Route('/', name: 'show_index', methods:['GET'])]
    public function index(ShowRepository $showRepository): Response
    {
        $this->denyAccessUnlessGranted('SHOW_VIEW');

        $shows = $showRepository->findBy(['organization'=> $this->userContext->getOrganization()]);

        return $this->render('show/index.html.twig', [ 'shows' => $shows]);
            
        
    }

    #[ Route('/new', name:'show_new', methods:['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SHOW_CREATE');
        $show = new Show();
       
        $formShow = $this->createForm(ShowFormType::class, $show , [
            'user_organization'=> $this->userContext->getOrganization(),
        ]);
        $formShow->handleRequest($request);

        if ($formShow->isSubmitted() && $formShow->isValid()) {
       
            /** @var UploadedFile|null $imageFile */
            $imageFile = $formShow->get('artworkUrl')->getData();

            if ($imageFile) {
                $newFilename = $this->fileUpLoader->upload($imageFile, $this->params->get('shows_directory'));
                if ($newFilename) {
                    $show->setArtworkUrl($newFilename);
                }
            }

            $this->crudManager->create($show);
            return $this->redirectToRoute('show_setup_contacts', ['id'=>$show->getId()]);
        }
        return $this->render('show/new.html.twig', ['show'=> $show, 'form' => $formShow]);


    }
     #[ Route('/edit/{id}', name:'show_edit', methods:['GET', 'POST'])]
    public function edit(Request $request, Show $show): Response
    {
        $this->denyAccessUnlessGranted('SHOW_EDIT', $show);
        $oldArtwork = $show->getArtworkUrl();
       
        $formShow = $this->createForm(ShowFormType::class, $show, [
            'user_organization' => $this->userContext->getOrganization(),
        ]);

        $formShow->handleRequest($request);

        if($formShow->isSubmitted() && $formShow->isValid()){
            /** @var UploadedFile|null $imageFile */
            $imageFile = $formShow->get('artworkUrl')->getData();
            if ($imageFile) {
                $newFilename = $this->fileUpLoader->upload($imageFile, $this->params->get('shows_directory'));
                
                if ($newFilename) {
                    //  Suppression de l'ancienne affiche physique
                    if ($oldArtwork) {
                        $oldFilePath = $this->params->get('shows_directory') . '/' . $oldArtwork;
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


            $this->crudManager->update($show);
            return $this->redirectToRoute('show_index');
        }
        return $this->render('show/edit.html.twig', ['show'=> $show, 'form' => $formShow]);


    }
      #[ Route('/{id}', name:'show_show', methods:['GET'])]
    public function show(Show $show): Response
    {
        $this->denyAccessUnlessGranted('SHOW_VIEW', $show);

        return $this->render('show/show.html.twig', ['show'=> $show]);


    }
    
    #[Route('/{id}/setup-contacts', name:'show_setup_contacts', requirements:['id'=>'\d+'], methods:['GET'])]
    public function setupContacts(Show $show, ): Response
    {
        $this->denyAccessUnlessGranted('SHOW_CREATE');
        return $this->render('show/setup_contacts.html.twig', [
            'show'=> $show,
        ]);


    }

    #[ Route('/delete/{id}', name:'show_delete', methods:['POST'])]
    public function delete(Request $request, Show $show): Response
    {
        $this->denyAccessUnlessGranted('SHOW_DELETE' , $show);

        if($this->isCsrfTokenValid('delete' . $show->getId() , $request->request->get('_token'))) {
            // suppression de l'image associée au spectacle
            if($show->getArtworkUrl()){
                $filePath = $this->params->get('shows_directory') . '/' . $show->getArtworkUrl();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $this->crudManager->delete($show);
        };

       
        return $this->redirectToRoute('show_index', []);

    }
    
}
