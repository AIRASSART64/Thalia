<?php

namespace App\Controller;

use App\Form\ContactSupportFormType;
use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SupportController extends AbstractController
{
    #[Route('/support', name: 'support', methods:['GET', 'POST'])]
    public function contactSupport(Request $request , MailService $mailService): Response
    {
       $form = $this->createForm(ContactSupportFormType::class);
       $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid()) {
        $contactData = $form->getData();
        $mailService->sendSupportContactEmail($contactData);
        $this->addFlash('success', 'Votre message a bien été envoyé au support Thalia. Nous vous répondrons dans les plus brefs délais.');

        return $this->redirectToRoute('home');
       }
       return $this->render('legal/contact_support.html.twig', ['supportForm' => $form->createView(),]);
    }
}
