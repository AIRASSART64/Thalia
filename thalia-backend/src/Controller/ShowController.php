<?php

namespace App\Controller;

use App\Entity\Show;
use App\Form\ShowFormType;
use App\Repository\ShowRepository;
use App\Service\CrudManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/show')]
#[IsGranted('ROLE_USER')]

class ShowController extends AbstractController
{
    public function __construct(private CrudManagerService $crudManager)
    {}
 
    #[Route('/', name: 'show_index', methods:['GET'])]
    public function index(ShowRepository $showRepository): Response
    {
        $this->denyAccessUnlessGranted('SHOW_VIEW');
        $shows = $showRepository->findAll();

        return $this->render('show/index.html.twig', [ 'shows' => $shows]);
    }

    #[ Route('/new', name:'show_new', methods:['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SHOW_CREATE');
        $show = new Show();
        $user = $this->getUser();

    if (!$user instanceof \App\Entity\User) {
    throw new \LogicException('L\'utilisateur doit être connecté avec un compte valide.');
    }
        $formShow = $this->createForm(ShowFormType::class, $show , [
            'current_organization'=> $user->getOrganization(),
        ]);
        $formShow->handleRequest($request);

        if($formShow->isSubmitted() && $formShow->isValid()){
            $this->crudManager->create($show);
            return $this->redirectToRoute('show_index');
        }
        return $this->render('show/new.html.twig', ['show'=> $show, 'form' => $formShow]);


    }
     #[ Route('/edit/{id}', name:'show_edit', methods:['GET', 'POST'])]
    public function edit(Request $request, Show $show): Response
    {
        $this->denyAccessUnlessGranted('SHOW_EDIT', $show);

        $formShow = $this->createForm(ShowFormType::class, $show);
        $formShow->handleRequest($request);

        if($formShow->isSubmitted() && $formShow->isValid()){
            $this->crudManager->update($show);
            return $this->redirectToRoute('show');
        }
        return $this->render('show/edit.html.twig', ['show'=> $show, 'form' => $formShow]);


    }
      #[ Route('/{id}', name:'show_show', methods:['GET'])]
    public function show(Show $show): Response
    {
        $this->denyAccessUnlessGranted('SHOW_VIEW', $show);

        return $this->render('show/show.html.twig', ['show'=> $show]);


    }
    #[ Route('/delete/{id}', name:'show_delete', methods:['POST'])]
    public function delete(Request $request, Show $show): Response
    {
        $this->denyAccessUnlessGranted('SHOW_DELETE' , $show);

        if($this->isCsrfTokenValid('delete' . $show->getId() , $request->get('_token'))) {
            $this->crudManager->delete($show);
        };

       
        return $this->redirectToRoute('show_index', []);


    }
    


}
