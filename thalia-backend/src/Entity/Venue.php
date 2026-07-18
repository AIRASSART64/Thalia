<?php

namespace App\Entity;

use App\Repository\VenueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenueRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Venue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $max_capacity = null;

    #[ORM\Column(nullable: true)]
    private ?int $seats_count = null;

    #[ORM\Column(nullable: true)]
    private ?int $standing_count = null;

    #[ORM\Column(nullable: true)]
    private ?int $pmr_count = null;

    #[ORM\Column(nullable: true)]
    private ?int $invitation_quota = null;

    #[ORM\Column(nullable: true)]
    private ?float $stage_width = null;

    #[ORM\Column(nullable: true)]
    private ?float $stage_depth = null;

    #[ORM\Column(nullable: true)]
    private ?float $stage_height = null;

    #[ORM\ManyToOne(inversedBy: 'venues')]
    private ?Organization $organization = null;

    /**
     * @var Collection<int, Equipment>
     */
    #[ORM\OneToMany(targetEntity: Equipment::class, mappedBy: 'venue')]
    private Collection $equipments;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $venue_image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $venue_plan = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct()
    {
        $this->equipments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMaxCapacity(): ?int
    {
        return $this->max_capacity;
    }

    public function setMaxCapacity(?int $max_capacity): static
    {
        $this->max_capacity = $max_capacity;

        return $this;
    }

    public function getSeatsCount(): ?int
    {
        return $this->seats_count;
    }

    public function setSeatsCount(?int $seats_count): static
    {
        $this->seats_count = $seats_count;

        return $this;
    }

    public function getStandingCount(): ?int
    {
        return $this->standing_count;
    }

    public function setStandingCount(?int $standing_count): static
    {
        $this->standing_count = $standing_count;

        return $this;
    }

    public function getPmrCount(): ?int
    {
        return $this->pmr_count;
    }

    public function setPmrCount(?int $pmr_count): static
    {
        $this->pmr_count = $pmr_count;

        return $this;
    }

    public function getInvitationQuota(): ?int
    {
        return $this->invitation_quota;
    }

    public function setInvitationQuota(?int $invitation_quota): static
    {
        $this->invitation_quota = $invitation_quota;

        return $this;
    }

    public function getStageWidth(): ?float
    {
        return $this->stage_width;
    }

    public function setStageWidth(?float $stage_width): static
    {
        $this->stage_width = $stage_width;

        return $this;
    }

    public function getStageDepth(): ?float
    {
        return $this->stage_depth;
    }

    public function setStageDepth(?float $stage_depth): static
    {
        $this->stage_depth = $stage_depth;

        return $this;
    }

    public function getStageHeight(): ?float
    {
        return $this->stage_height;
    }

    public function setStageHeight(?float $stage_height): static
    {
        $this->stage_height = $stage_height;

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
     * @return Collection<int, Equipment>
     */
    public function getEquipment(): Collection
    {
        return $this->equipments;
    }

    public function addEquipment(Equipment $equipment): static
    {
        if (!$this->equipments->contains($equipment)) {
            $this->equipments->add($equipment);
            $equipment->setVenue($this);
        }

        return $this;
    }

    public function removeEquipment(Equipment $equipment): static
    {
        if ($this->equipments->removeElement($equipment)) {
            // set the owning side to null (unless already changed)
            if ($equipment->getVenue() === $this) {
                $equipment->setVenue(null);
            }
        }

        return $this;
    }

    public function getVenueImage(): ?string
    {
        return $this->venue_image;
    }

    public function setVenueImage(?string $venue_image): static
    {
        $this->venue_image = $venue_image;

        return $this;
    }

    public function getVenuePlan(): ?string
    {
        return $this->venue_plan;
    }

    public function setVenuePlan(?string $venue_plan): static
    {
        $this->venue_plan = $venue_plan;

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

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
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
