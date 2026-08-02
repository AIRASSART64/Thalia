<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le prénom du contact est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le prénom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $first_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le nom du contact est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $last_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom de la structure ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $company_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le rôle ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $role = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(
        max: 30,
        maxMessage: "Le numéro de téléphone est trop long (maximum {{ limit }} caractères)."
    )]
    #[Assert\Regex(
        pattern: "/^[0-9\+\-\s\.\(\)]+$/",
        message: "Le format du numéro de téléphone n'est pas valide."
    )]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: "L'adresse email '{{ value }}' n'est pas une adresse valide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'email ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    // /**
    //  * @var Collection<int, Show>
    //  */
    // #[ORM\ManyToMany(targetEntity: Show::class, mappedBy: 'contacts')]
    // private Collection $shows;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

  /**
     * Relation vers la table de jonction ShowContact
     * @var Collection<int, ShowContact>
     */
    #[ORM\OneToMany(targetEntity: ShowContact::class, mappedBy: 'contact', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $showContacts;

    
    public function __construct()
    {
        // $this->shows = new ArrayCollection();
        $this->showContacts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): static
    {
        $this->first_name = $first_name;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): static
    {
        $this->last_name = $last_name;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    public function setCompanyName(?string $company_name): static
    {
        $this->company_name = $company_name;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    // /**
     
    // //  * @return Collection<int, Show>
    // //  */
    // public function getShows(): Collection
    // {
    //     return $this->shows;
    // }

    // public function addShow(Show $show): static
    // {
    //     if (!$this->shows->contains($show)) {
    //         $this->shows->add($show);
    //         // $show->addContact($this); 
    //     }
    //     return $this;
    // }

    // public function removeShow(Show $show): static
    // {
    //     if ($this->shows->removeElement($show)) {
    //         // $show->removeContact($this); 
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
            $showContact->setContact($this);
        }

        return $this;
    }

    public function removeShowContact(ShowContact $showContact): static
    {
        if ($this->showContacts->removeElement($showContact)) {
            // set the owning side to null (unless already changed)
            if ($showContact->getContact() === $this) {
                $showContact->setContact(null);
            }
        }

        return $this;
    }
}