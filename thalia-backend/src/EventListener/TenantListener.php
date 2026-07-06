<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest', priority: 1)]
class TenantListener
{
    private EntityManagerInterface $em;
    private TokenStorageInterface $tokenStorage;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // On n'applique rien sur les requêtes secondaires (comme les sous-générations Twig)
        if (!$event->isMainRequest()) {
            return;
        }

        // 1. On récupère le token de sécurité
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        // 2. On récupère l'utilisateur connecté
        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        // 3. Si l'utilisateur est lié à une organisation, on active et configure le filtre
        if ($user->getOrganization()) {
            $filters = $this->em->getFilters();
            
            //La méthode enable() de Doctrine configure ET retourne le filtre actif !
            $filters->enable('tenant_filters')->setParameter('current_tenant_id', $user->getOrganization()->getId());
        }
    }
}