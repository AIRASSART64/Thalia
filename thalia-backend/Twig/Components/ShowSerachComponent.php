<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Repository\ShowRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('show_search')]
#[IsGranted('ROLE_USER')] 
class ShowSearchComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $query = '';

    #[LiveProp(writable: true, url: true)]
    public string $discipline = 'Tous';

    #[LiveProp(writable: true, url: true)]
    public string $audience = 'Tous';

    public function __construct(
        private ShowRepository $showRepository,
        private Security $security
    ) {
    }

    public function getShows(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $organization = $user->getOrganization();

        return $this->showRepository->searchShowsForOrganization(
            $organization,
            $this->query,
            $this->discipline,
            $this->audience
        );
    }
}