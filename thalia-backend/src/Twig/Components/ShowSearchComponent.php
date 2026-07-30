<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Enum\AudienceClassificationEnum;
use App\Enum\DisciplineEnum;
use App\Repository\ShowRepository;
use App\Repository\ThemeRepository;
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

    #[LiveProp(writable: true, url: true)]
    public string $theme = 'Tous';

    public function __construct(
        private ShowRepository $showRepository,
        private ThemeRepository $themeRepository,
        private Security $security
    ) {
    }
    public function getDisciplines(): array
    {
        return DisciplineEnum::cases();
    }
       public function getAudiences(): array
    {
        return AudienceClassificationEnum::cases();
    }
    public function getThemes(): array
    {
        return $this->themeRepository->findBy([], ['name' => 'ASC']);
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
            $this->audience,
            $this->theme,
        );
    }
}