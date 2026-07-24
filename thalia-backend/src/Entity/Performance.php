<?php

namespace App\Entity;

use App\Repository\PerformanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PerformanceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Performance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $date_time_start = null;

    #[ORM\Column]
    private ?\DateTime $date_time_end = null;

    #[ORM\Column(nullable: true)]
    private ?int $setup_duration_min = null;

    #[ORM\Column(nullable: true)]
    private ?int $teardown_duration_min = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?string $ticket_price_standard = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?string $ticket_price_reduced = null;

    #[ORM\Column(nullable: true)]
    private ?int $estimated_attendance_percent = null;

    #[ORM\ManyToOne(inversedBy: 'performances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    #[ORM\ManyToOne(inversedBy: 'performances')]
    private ?Venue $venue = null;

    #[ORM\ManyToOne(inversedBy: 'performances')]
    private ?Show $season_show = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $total_cost = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'performances')]
    private ?Season $season = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateTimeStart(): ?\DateTime
    {
        return $this->date_time_start;
    }

    public function setDateTimeStart(\DateTime $date_time_start): static
    {
        $this->date_time_start = $date_time_start;

        return $this;
    }

    public function getDateTimeEnd(): ?\DateTime
    {
        return $this->date_time_end;
    }

    public function setDateTimeEnd(\DateTime $date_time_end): static
    {
        $this->date_time_end = $date_time_end;

        return $this;
    }

    public function getSetupDurationMin(): ?int
    {
        return $this->setup_duration_min;
    }

    public function setSetupDurationMin(?int $setup_duration_min): static
    {
        $this->setup_duration_min = $setup_duration_min;

        return $this;
    }

    public function getTeardownDurationMin(): ?int
    {
        return $this->teardown_duration_min;
    }

    public function setTeardownDurationMin(?int $teardown_duration_min): static
    {
        $this->teardown_duration_min = $teardown_duration_min;

        return $this;
    }

    public function getTicketPriceStandard(): ?string
    {
        return $this->ticket_price_standard;
    }

    public function setTicketPriceStandard(?string $ticket_price_standard): static
    {
        $this->ticket_price_standard = $ticket_price_standard;

        return $this;
    }

    public function getTicketPriceReduced(): ?string
    {
        return $this->ticket_price_reduced;
    }

    public function setTicketPriceReduced(?string $ticket_price_reduced): static
    {
        $this->ticket_price_reduced = $ticket_price_reduced;

        return $this;
    }

    public function getEstimatedAttendancePercent(): ?int
    {
        return $this->estimated_attendance_percent;
    }

    public function setEstimatedAttendancePercent(?int $estimated_attendance_percent): static
    {
        $this->estimated_attendance_percent = $estimated_attendance_percent;

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

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): static
    {
        $this->venue = $venue;

        return $this;
    }

    public function getSeasonShow(): ?Show
    {
        return $this->season_show;
    }

    public function setSeasonShow(?Show $season_show): static
    {
        $this->season_show = $season_show;

        return $this;
    }

    public function getTotalCost(): ?string
    {
        return $this->total_cost;
    }

    public function setTotalCost(?string $total_cost): static
    {
        $this->total_cost = $total_cost;

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

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): static
    {
        $this->season = $season;

        return $this;
    }
}
