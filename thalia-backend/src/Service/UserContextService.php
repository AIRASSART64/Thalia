<?php

namespace App\Service;

use App\Entity\Organization;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class UserContextService
{
    public function __construct(
        private Security $security
    ) {}

    
     // Récupèration de l'utilisateur connecté et verification de la validité de l'instance par rapport au User.

    public function getUser(): User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('L\'utilisateur doit être connecté avec un compte valide.');
        }

        return $user;
    }

    
     //Récupéreration de l'organisation de l'utilisateur connecté.
     
    public function getOrganization(): ?Organization
    {
        return $this->getUser()->getOrganization();
    }
}