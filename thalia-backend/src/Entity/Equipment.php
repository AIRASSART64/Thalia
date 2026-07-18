<?php

namespace App\Entity;

use App\Enum\EquipmentCategoyEnum;
use App\Repository\EquipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true, enumType: EquipmentCategoyEnum::class)]
    private ?array $category = null;

    #[ORM\Column(nullable: true)]
    private ?int $total_quantity = null;

    #[ORM\ManyToOne(inversedBy: 'equipment')]
    private ?Venue $venue = null;

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

    /**
     * @return EquipmentCategoyEnum[]|null
     */
    public function getCategory(): ?array
    {
        return $this->category;
    }

    public function setCategory(?array $category): static
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
}
