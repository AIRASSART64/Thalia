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
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
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

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    public int $itemsPerPage = 3;

    public function __construct(
        private ShowRepository $showRepository,
        private ThemeRepository $themeRepository,
        private Security $security
    ) {
    }

    /**
     * Action déclenchée par la pagination LiveComponent
     */
    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    public function updatedQuery(): void { $this->page = 1; }
    public function updatedDiscipline(): void { $this->page = 1; }
    public function updatedAudience(): void { $this->page = 1; }
    public function updatedTheme(): void { $this->page = 1; }

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
            $this->page,
            $this->itemsPerPage
        );
    }

    public function getTotalItems(): int
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $organization = $user->getOrganization();

        return $this->showRepository->countShowsForOrganization(
            $organization,
            $this->query,
            $this->discipline,
            $this->audience,
            $this->theme
        );
    }

    public function getMaxPages(): int
    {
        $total = $this->getTotalItems();
        return $total > 0 ? (int) ceil($total / $this->itemsPerPage) : 1;
    }

    public function getPageStart(): int
    {
        if ($this->getTotalItems() === 0) {
            return 0;
        }
        return (($this->page - 1) * $this->itemsPerPage) + 1;
    }

    public function getPageEnd(): int
    {
        return min($this->page * $this->itemsPerPage, $this->getTotalItems());
    }
}