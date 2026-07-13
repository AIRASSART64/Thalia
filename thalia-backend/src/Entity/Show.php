<?php

namespace App\Entity;

use App\Enum\DisciplineEnum;
use App\Enum\PipelineStatusEnum;
use App\Repository\ShowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?DisciplineEnum $discipline = null;

    #[ORM\Column(nullable: true)]
    private ?int $duration_min = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $synopsis = null;

    #[ORM\Column(nullable: true)]
    private ?float $min_stage_width = null;

    #[ORM\Column(nullable: true)]
    private ?float $min_stage_depth = null;

    #[ORM\Column(nullable: true)]
    private ?float $min_stage_height = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?PipelineStatusEnum $pipeline_status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $artwork_url = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    /**
     * @var Collection<int, Contact>
     */
    #[ORM\ManyToMany(targetEntity: Contact::class, inversedBy: 'shows')]
    private Collection $contacts;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
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

    /**
     * @return Collection<int, Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): static
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
        }

        return $this;
    }

    public function removeContact(Contact $contact): static
    {
        $this->contacts->removeElement($contact);

        return $this;
    }

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
}
