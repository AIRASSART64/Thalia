<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ContactSupportFormType;
use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SupportController extends AbstractController
{
    #[Route('/support', name: 'support', methods: ['GET', 'POST'])]
    public function contactSupport(Request $request, MailService $mailService): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $form = $this->createForm(ContactSupportFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $contactData = array_merge($data, [
                'fullName' => $user ? ($user->getFirstName() . ' ' . $user->getLastName()) : ($data['fullName'] ?? 'Visiteur'),
                'email'    => $user ? $user->getEmail() : ($data['email'] ?? null),
            ]);
            $mailService->receiveSupportContactEmail($contactData);
            $mailService->sendSupportContactEmail($this->getUser(), $contactData);
            $this->addFlash('success', 'Votre message a bien été envoyé au support Thalia. Nous vous répondrons dans les plus brefs délais.');

            return $this->redirectToRoute('home');
        }
        return $this->render('legal/contact_support.html.twig', ['supportForm' => $form->createView(),]);
    }
}
