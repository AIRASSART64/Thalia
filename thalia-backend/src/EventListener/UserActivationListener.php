<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\MailService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
class UserActivationListener
{
    private MailService $mailService;

   
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function preUpdate(User $user, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('isActive')) {
            $oldValue = $event->getOldValue('isActive');
            $newValue = $event->getNewValue('isActive');

            
            if (!$oldValue && $newValue) {
                $this->mailService->sendAccountActivatedEmail($user);
            }
        }
    }
}