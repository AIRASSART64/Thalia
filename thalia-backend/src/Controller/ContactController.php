<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactFormType;
use App\Repository\ContactRepository;
use App\Repository\ShowRepository;
use App\Service\CrudManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/contact')]
#[IsGranted('ROLE_USER')]
class ContactController extends AbstractController
{
    public function __construct(
        private CrudManagerService $crudManager
    ) {}

    #[Route('/', name: 'contact_index', methods:['GET'])]
    public function index(ContactRepository $contactRepository): Response
    {
        
        $this->denyAccessUnlessGranted('CONTACT_VIEW');

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('L\'utilisateur doit être connecté avec un compte valide.');
        }

        $contacts = $contactRepository->findBy(['organization' => $user->getOrganization()]);

        return $this->render('contact/index.html.twig', [
            'contacts' => $contacts,
        ]);
    }

    #[Route('/new', name: 'contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ShowRepository $showRepository): Response
    {
        $this->denyAccessUnlessGranted('CONTACT_CREATE');

        $contact = new Contact();
        
        // si redirection depuis un spectacle, association du contact au spectacle
        $showId = $request->query->get('show_id');
        $show = null;
         if ($showId) {
             $show = $showRepository->find($showId);
             if ($show) {
           
                if (method_exists($contact, 'setShow')) {
                $contact->setShow($show);
                    } elseif (method_exists($contact, 'addShow')) {
                    $contact->addShow($show);
                    }   
                }
            }
        
        $user = $this->getUser();
         if (!$user instanceof \App\Entity\User) {
        throw new \LogicException('L\'utilisateur doit être connecté avec un compte valide.');
        }

        $formContact = $this->createForm(ContactFormType::class, $contact, [
            'current_organization'=> $user->getOrganization(),
        ]);
        $formContact->handleRequest($request);

        if ($formContact->isSubmitted() && $formContact->isValid()) {
            $this->crudManager->create($contact);

            $this->addFlash('success', 'Le contact a bien été ajouté.');
            //redirection sur le show associé au contact
            if ($show) {
            return $this->redirectToRoute('show_show', ['id' => $show->getId()]);
                }
            // sinon redirection vers la base de tous les contacts
            return $this->redirectToRoute('contact_index');
        }

        return $this->render('contact/new.html.twig', ['contact' => $contact,'form' => $formContact, 'show'=>$show,
        ]);
    }

    #[Route('/edit/{id}', name: 'contact_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contact $contact): Response
    {
        $this->denyAccessUnlessGranted('CONTACT_EDIT', $contact);

       $user = $this->getUser();
         if (!$user instanceof \App\Entity\User) {
        throw new \LogicException('L\'utilisateur doit être connecté avec un compte valide.');
        }

        $formContact = $this->createForm(ContactFormType::class, $contact, [
            'current_organization'=> $user->getOrganization(),
        ]);
        $formContact->handleRequest($request);

        if ($formContact->isSubmitted() && $formContact->isValid()) {
            $this->crudManager->update($contact);

            $this->addFlash('success', 'Le contact a bien été mis à jour.');

            return $this->redirectToRoute('contact_index');
        }

        return $this->render('contact/edit.html.twig', ['contact' => $contact,'form' => $formContact,
        ]);
    }

    #[Route('/{id}', name: 'contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        $this->denyAccessUnlessGranted('CONTACT_VIEW', $contact);

        return $this->render('contact/show.html.twig', ['contact' => $contact, ]);
    }

    #[Route('/delete/{id}', name: 'contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact): Response
    {
        $this->denyAccessUnlessGranted('CONTACT_DELETE', $contact);

        if ($this->isCsrfTokenValid('delete' . $contact->getId(), $request->request->get('_token'))) {
            $this->crudManager->delete($contact);
            $this->addFlash('success', 'Le contact a été supprimé avec succès.');
        }

        return $this->redirectToRoute('contact_index');
    }
}