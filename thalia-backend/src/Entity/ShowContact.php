<?php

namespace App\Entity;

use App\Repository\ShowContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShowContactRepository::class)]
#[ORM\Table(name: 'show_contact')] 
class ShowContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'showContacts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Show $event = null;

    #[ORM\ManyToOne(inversedBy: 'showContacts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Contact $contact = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $report = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Show
    {
        return $this->event;
    }

    public function setEvent(?Show $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): static
    {
        $this->contact = $contact;

        return $this;
    }

    public function getReport(): ?string
    {
        return $this->report;
    }

    public function setReport(?string $report): static
    {
        $this->report = $report;

        return $this;
    }
}
