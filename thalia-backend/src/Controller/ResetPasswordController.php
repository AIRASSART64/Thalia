<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Service\MailService;
use App\Service\PasswordManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {}

    // formulaire de réinitialisation de mot de passe 
    #[Route('', name: 'app_forgot_password_request', methods:['GET', 'POST'])]
    public function request(Request $request, MailService $mailService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('profile_index');
        }

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingToken(
                $form->get('email')->getData(),
                $mailService
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    // message de confirmation d'envoi de la demande de reinitialisation
    #[Route('/check-email', name: 'app_check_email', methods:['GET'])]
    public function checkEmail(): Response
    {
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    
     // Validation du lien reçu par email & Changement effectif du mot de passe
     
   
    #[Route('/reset/{token}', name: 'app_reset_password', defaults: ['token' => null])]
    public function reset(Request $request, PasswordManagerService $passwordManager, ?string $token = null): Response
    {
        // 1. Si le token est dans l'URL (l'utilisateur vient de cliquer sur son mail)
        if ($token) {
            // On le sauvegarde de manière chiffrée en session PHP
            $this->storeTokenInSession($token);
            
            // 🎯 REDIRECTION : On recharge la page sans le token dans l'URL pour le masquer
            return $this->redirectToRoute('app_reset_password');
        }

        // 2. Après la redirection, l'URL est propre, on récupère le token mis de côté en session
        $token = $this->getTokenFromSession();
        
        if (null === $token) {
            throw $this->createNotFoundException("Aucun jeton de réinitialisation trouvé (Le lien a expiré ou la session a été perdue).");
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                'Un problème est survenu lors de la validation de votre demande',
                $e->getReason()
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordHelper->removeResetRequest($token);

            $plainPassword = $form->get('plainPassword')->getData();
            $passwordManager->upgradePassword($user, $plainPassword);

            $this->cleanSessionAfterReset();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    // recherche du user correspondant au mail renseigné et genertaion du lien de reinitialisation par mail
    private function processSendingToken(string $emailAttribute, MailService $mailService): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailAttribute,
        ]);

        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->redirectToRoute('app_check_email');
        }

        $mailService->sendResetPasswordEmail($user, $resetToken);

        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}