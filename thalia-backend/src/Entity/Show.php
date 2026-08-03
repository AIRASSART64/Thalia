<?php

namespace App\Entity;

use App\Enum\AudienceClassificationEnum;
use App\Enum\DisciplineEnum;
use App\Enum\PipelineStatusEnum;
use App\Repository\ShowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ShowRepository::class)]
#[ORM\Table(name: '`show`')]
#[ORM\HasLifecycleCallbacks]
class Show
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le titre du spectacle est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true, enumType: DisciplineEnum::class)]
    private ?DisciplineEnum $discipline = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "La durée doit être un nombre positif.")]
    private ?int $duration_min = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $synopsis = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "La largeur d'ouverture doit être un nombre positif.")]
    private ?float $min_stage_width = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "La profondeur scénique doit être un nombre positif.")]
    private ?float $min_stage_depth = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "La hauteur sous perches doit être un nombre positif.")]
    private ?float $min_stage_height = null;

    #[ORM\Column(length: 100, nullable: true, enumType: PipelineStatusEnum::class)]
    private ?PipelineStatusEnum $pipeline_status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $artwork_url = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    // /**
    //  * @var Collection<int, Contact>
    //  */
    // #[ORM\ManyToMany(targetEntity: Contact::class, inversedBy: 'shows', cascade: ['persist'])]
    // private Collection $contacts;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, Performance>
     */
    #[ORM\OneToMany(targetEntity: Performance::class, mappedBy: 'season_show')]
    private Collection $performances;

    #[ORM\Column(enumType: AudienceClassificationEnum::class, nullable: true)]
    private ?AudienceClassificationEnum $audience = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $artistic_file = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $artistic_information = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $technical_information = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: "Le coût unitaire doit être un montant positif.")]
    private ?string $global_unit_cost = null;

    /**
     * @var Collection<int, Theme>
     */
    #[ORM\ManyToMany(targetEntity: Theme::class, inversedBy: 'shows', cascade: ['persist'])]
    private Collection $themes;

    /**
     * @var Collection<int, ShowContact>
     */
    #[ORM\OneToMany(targetEntity: ShowContact::class, mappedBy: 'event', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $showContacts;

    public function __construct()
    {
        // $this->contacts = new ArrayCollection();
        $this->performances = new ArrayCollection();
        $this->themes = new ArrayCollection();
        $this->showContacts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDiscipline(): ?DisciplineEnum
    {
        return $this->discipline;
    }

    public function setDiscipline(?DisciplineEnum $discipline): static
    {
        $this->discipline = $discipline;
        return $this;
    }

    public function getDurationMin(): ?int
    {
        return $this->duration_min;
    }

    public function setDurationMin(?int $duration_min): static
    {
        $this->duration_min = $duration_min;
        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(?string $synopsis): static
    {
        $this->synopsis = $synopsis;
        return $this;
    }

    public function getMinStageWidth(): ?float
    {
        return $this->min_stage_width;
    }

    public function setMinStageWidth(?float $min_stage_width): static
    {
        $this->min_stage_width = $min_stage_width;
        return $this;
    }

    public function getMinStageDepth(): ?float
    {
        return $this->min_stage_depth;
    }

    public function setMinStageDepth(?float $min_stage_depth): static
    {
        $this->min_stage_depth = $min_stage_depth;
        return $this;
    }

    public function getMinStageHeight(): ?float
    {
        return $this->min_stage_height;
    }

    public function setMinStageHeight(?float $min_stage_height): static
    {
        $this->min_stage_height = $min_stage_height;
        return $this;
    }

    public function getPipelineStatus(): ?PipelineStatusEnum
    {
        return $this->pipeline_status;
    }

    public function setPipelineStatus(?PipelineStatusEnum $pipeline_status): static
    {
        $this->pipeline_status = $pipeline_status;
        return $this;
    }

    public function getArtworkUrl(): ?string
    {
        return $this->artwork_url;
    }

    public function setArtworkUrl(?string $artwork_url): static
    {
        $this->artwork_url = $artwork_url;
        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): static
    {
        $this->organization = $organization;
        return $this;
    }

    // /**
    //  * @return Collection<int, Contact>
    //  */
    // public function getContacts(): Collection
    // {
    //     return $this->contacts;
    // }

    // public function addContact(Contact $contact): static
    // {
    //     if (!$this->contacts->contains($contact)) {
    //         $this->contacts->add($contact);
    //         $contact->addShow($this);
    //     }
    //     return $this;
    // }

    // public function removeContact(Contact $contact): static
    // {
    //     if ($this->contacts->removeElement($contact)) {
    //         $contact->removeShow($this);
    //     }
    //     return $this;
    // }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    #[ORM\PrePersist]
    public function setInitialDates(): void
    {
        $now = new \DateTimeImmutable();
        $this->created_at = $now;
        $this->updated_at = $now;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, Performance>
     */
    public function getPerformances(): Collection
    {
        return $this->performances;
    }

    public function addPerformance(Performance $performance): static
    {
        if (!$this->performances->contains($performance)) {
            $this->performances->add($performance);
            $performance->setSeasonShow($this);
        }
        return $this;
    }

    public function removePerformance(Performance $performance): static
    {
        if ($this->performances->removeElement($performance)) {
            if ($performance->getSeasonShow() === $this) {
                $performance->setSeasonShow(null);
            }
        }
        return $this;
    }

    public function getAudience(): ?AudienceClassificationEnum
    {
        return $this->audience;
    }

    public function setAudience(?AudienceClassificationEnum $audience): static
    {
        $this->audience = $audience;
        return $this;
    }

    public function getArtisticFile(): ?string
    {
        return $this->artistic_file;
    }

    public function setArtisticFile(?string $artistic_file): static
    {
        $this->artistic_file = $artistic_file;
        return $this;
    }

    public function getArtisticInformation(): ?string
    {
        return $this->artistic_information;
    }

    public function setArtisticInformation(?string $artistic_information): static
    {
        $this->artistic_information = $artistic_information;
        return $this;
    }

    public function getTechnicalInformation(): ?string
    {
        return $this->technical_information;
    }

    public function setTechnicalInformation(?string $technical_information): static
    {
        $this->technical_information = $technical_information;
        return $this;
    }

    public function getGlobalUnitCost(): ?string
    {
        return $this->global_unit_cost;
    }

    public function setGlobalUnitCost(?string $global_unit_cost): static
    {
        $this->global_unit_cost = $global_unit_cost;
        return $this;
    }

    /**
     * @return Collection<int, Theme>
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): static
    {
        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
        }
        return $this;
    }

    public function removeTheme(Theme $theme): static
    {
        $this->themes->removeElement($theme);
        return $this;
    }

    /**
     * @return Collection<int, ShowContact>
     */
    public function getShowContacts(): Collection
    {
        return $this->showContacts;
    }

    public function addShowContact(ShowContact $showContact): static
    {
        if (!$this->showContacts->contains($showContact)) {
            $this->showContacts->add($showContact);
            $showContact->setEvent($this);
        }

        return $this;
    }

    public function removeShowContact(ShowContact $showContact): static
    {
        if ($this->showContacts->removeElement($showContact)) {
            // set the owning side to null (unless already changed)
            if ($showContact->getEvent() === $this) {
                $showContact->setEvent(null);
            }
        }

        return $this;
    }
}