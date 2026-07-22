<?php

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Season
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $start_date = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $end_date = null;

    #[ORM\Column]
    private ?bool $is_active = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'seasons')]
    private ?Organization $organization = null;

    /**
     * @var Collection<int, Financial>
     */
    #[ORM\OneToMany(targetEntity: Financial::class, mappedBy: 'season')]
    private Collection $financials;

    public function __construct()
    {
        $this->financials = new ArrayCollection();
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

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeImmutable $start_date): static
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeImmutable $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

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

    }

      /**
       * @return Collection<int, Financial>
       */
      public function getFinancials(): Collection
      {
          return $this->financials;
      }

      public function addFinancial(Financial $financial): static
      {
          if (!$this->financials->contains($financial)) {
              $this->financials->add($financial);
              $financial->setSeason($this);
          }

          return $this;
      }

      public function removeFinancial(Financial $financial): static
      {
          if ($this->financials->removeElement($financial)) {
              // set the owning side to null (unless already changed)
              if ($financial->getSeason() === $this) {
                  $financial->setSeason(null);
              }
          }

          return $this;
      }

}
