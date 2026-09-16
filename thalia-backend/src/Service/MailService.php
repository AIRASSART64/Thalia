<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class MailService
{
    private MailerInterface $mailer;
    private string $senderEmail = 'noreply@thalia.fr';
    private string $senderName = 'Thalia Application';

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    // Email confirming receipt of the account creation request
    public function sendRegistrationPendingEmail(User $user): void
    {
        $this->sendTemplateEmail(
            $user->getEmail(),
            'Thalia - Demande d\'inscription reçue',
            'emails/registration_pending.html.twig',
            ['user' => $user]
        );
    }

    //Account creation confirmation email
    public function sendAccountActivatedEmail(User $user): void
    {
        $this->sendTemplateEmail(
            $user->getEmail(),
            'Thalia - Votre compte a été activé !',
            'emails/account_activated.html.twig',
            ['user' => $user]
        );
    }

    // mail de reinitialisation de mail 
    public function sendResetPasswordEmail(User $user, ResetPasswordToken $resetPasswordToken): void
    {
        $this->sendTemplateEmail(
            $user->getEmail(),
            'Thalia - Réinitialisation du mot de passe',
            'emails/reset_email.html.twig',
            ['user' => $user, 'resetToken' => $resetPasswordToken]
        );
    }
    // mail retour contact support
    public function sendSupportContactEmail(?User $user,  array $contactData): void
    {
        // Si l'utilisateur est connecté, on prend son email, sinon celui saisi dans le formulaire
    $recipientEmail = $user ? $user->getEmail() : ($contactData['email'] ?? null);

    if (!$recipientEmail) {
        return; 
    }
    $this->sendTemplateEmail(
        $recipientEmail,
        'Thalia - [Support] ' . ($contactData['subject'] ?? 'Demande de contact'),
        'emails/contact_support.html.twig',
        [
            'user'        => $user,
            'contactData' => $contactData,
        ]
    );
    }
     // mail demande support envoyé par le user
    public function receiveSupportContactEmail(array $contactData): void
    {
        $this->sendTemplateEmail(
            'admin@thalia.iandre.fr', 
            'Thalia - [Support] ' . ($contactData['subject'] ?? 'Demande de contact'),
            'emails/superadmin_contact.html.twig',
            ['data' => $contactData]
        );
    }
    // centralisation of the email dispatch mechanism
    private function sendTemplateEmail(string $to, string $subject, string $template, array $context): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->senderEmail, $this->senderName))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        $this->mailer->send($email);
    }
}
