<?php

namespace App\Entity;

use App\Enum\EquipmentCategoyEnum;
use App\Repository\EquipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length:255, nullable: true, enumType: EquipmentCategoyEnum::class)]
    private ?EquipmentCategoyEnum $category = null;

    #[ORM\Column(nullable: true)]
    private ?int $total_quantity = null;

    #[ORM\ManyToOne(inversedBy: 'equipment')]
    private ?Venue $venue = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'equipment')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    
    public function getCategory(): ?EquipmentCategoyEnum
    {
        return $this->category;
    }

    public function setCategory(?EquipmentCategoyEnum $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getTotalQuantity(): ?int
    {
        return $this->total_quantity;
    }

    public function setTotalQuantity(?int $total_quantity): static
    {
        $this->total_quantity = $total_quantity;

        return $this;
    }

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): static
    {
        $this->venue = $venue;

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

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): static
    {
        $this->organization = $organization;

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
