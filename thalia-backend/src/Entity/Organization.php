<?php

namespace App\Entity;


use App\Enum\ErpCategory;
use App\Enum\ErpType;
use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
#[ORM\HasLifecycleCallbacks]


class Organization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $licenceNumber = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $adress = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $defaultVatRate = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'organization', orphanRemoval: true)]
    private Collection $users;

    /**
     * @var Collection<int, Show>
     */
    #[ORM\OneToMany(targetEntity: Show::class, mappedBy: 'organization', orphanRemoval: true)]
    private Collection $shows;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $business_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vat_number = null;

    #[ORM\Column(nullable: true)]
    private ?int $safety_capacity = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true, enumType: ErpCategory::class)]
    private ?ErpCategory $erp_category = null;

    #[ORM\Column(length: 255, nullable: true, enumType: ErpType::class)]
    private ?ErpType $erp_type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $spectacle_vat_rate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $manager_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $manager_title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $legal_status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'organization', orphanRemoval: true)]
    private Collection $notifications;

    /**
     * @var Collection<int, Contact>
     */
    #[ORM\OneToMany(targetEntity: Contact::class, mappedBy: 'organization', orphanRemoval: true)]
    private Collection $contacts;

    /**
     * @var Collection<int, Venue>
     */
    #[ORM\OneToMany(targetEntity: Venue::class, mappedBy: 'organization')]
    private Collection $venues;

    /**
     * @var Collection<int, Equipment>
     */
    #[ORM\OneToMany(targetEntity: Equipment::class, mappedBy: 'organization', orphanRemoval: true)]
    private Collection $equipment;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->shows = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->venues = new ArrayCollection();
        $this->equipment = new ArrayCollection();
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

    public function getLicenceNumber(): ?string
    {
        return $this->licenceNumber;
    }

    public function setLicenceNumber(?string $licenceNumber): static
    {
        $this->licenceNumber = $licenceNumber;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(?string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }

    public function getDefaultVatRate(): ?string
    {
        return $this->defaultVatRate;
    }

    public function setDefaultVatRate(?string $defaultVatRate): static
    {
        $this->defaultVatRate = $defaultVatRate;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setOrganization($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getOrganization() === $this) {
                $user->setOrganization(null);
            }
        }

        return $this;
    }
    /**
     * @return Collection<int, Show>
     */
    public function getShows(): Collection
    {
        return $this->shows;
    }

    public function addShow(Show $show): static
    {
        if (!$this->shows->contains($show)) {
            $this->shows->add($show);
            $show->setOrganization($this);
        }
        return $this;
    }

    public function removeShow(Show $show): static
    {
        if ($this->shows->removeElement($show)) {
            if ($show->getOrganization() === $this) {
                $show->setOrganization(null);
            }
        }
        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getBusinessName(): ?string
    {
        return $this->business_name;
    }

    public function setBusinessName(?string $business_name): static
    {
        $this->business_name = $business_name;

        return $this;
    }

    public function getVatNumber(): ?string
    {
        return $this->vat_number;
    }

    public function setVatNumber(?string $vat_number): static
    {
        $this->vat_number = $vat_number;

        return $this;
    }

    public function getSafetyCapacity(): ?int
    {
        return $this->safety_capacity;
    }

    public function setSafetyCapacity(?int $safety_capacity): static
    {
        $this->safety_capacity = $safety_capacity;

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

    public function getErpCategory(): ?ErpCategory
    {
        return $this->erp_category;
    }

    public function setErpCategory(?ErpCategory $erp_category): static
    {
        $this->erp_category = $erp_category;
        return $this;
    }

    public function getErpType(): ?ErpType
    {
        return $this->erp_type;
    }

    public function setErpType(?ErpType $erp_type): static
    {
        $this->erp_type = $erp_type;
        return $this;
    }

    public function getSpectacleVatRate(): ?string
    {
        return $this->spectacle_vat_rate;
    }

    public function setSpectacleVatRate(?string $spectacle_vat_rate): static
    {
        $this->spectacle_vat_rate = $spectacle_vat_rate;

        return $this;
    }

    public function getManagerName(): ?string
    {
        return $this->manager_name;
    }

    public function setManagerName(?string $manager_name): static
    {
        $this->manager_name = $manager_name;

        return $this;
    }

    public function getManagerTitle(): ?string
    {
        return $this->manager_title;
    }

    public function setManagerTitle(?string $manager_title): static
    {
        $this->manager_title = $manager_title;

        return $this;
    }

    public function getLegalStatus(): ?string
    {
        return $this->legal_status;
    }

    public function setLegalStatus(?string $legal_status): static
    {
        $this->legal_status = $legal_status;

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

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setOrganization($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getOrganization() === $this) {
                $notification->setOrganization(null);
            }
        }

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
            $contact->setOrganization($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): static
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getOrganization() === $this) {
                $contact->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Venue>
     */
    public function getVenues(): Collection
    {
        return $this->venues;
    }

    public function addVenue(Venue $venue): static
    {
        if (!$this->venues->contains($venue)) {
            $this->venues->add($venue);
            $venue->setOrganization($this);
        }

        return $this;
    }

    public function removeVenue(Venue $venue): static
    {
        if ($this->venues->removeElement($venue)) {
            // set the owning side to null (unless already changed)
            if ($venue->getOrganization() === $this) {
                $venue->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Equipment>
     */
    public function getEquipment(): Collection
    {
        return $this->equipment;
    }

    public function addEquipment(Equipment $equipment): static
    {
        if (!$this->equipment->contains($equipment)) {
            $this->equipment->add($equipment);
            $equipment->setOrganization($this);
        }

        return $this;
    }

    public function removeEquipment(Equipment $equipment): static
    {
        if ($this->equipment->removeElement($equipment)) {
            // set the owning side to null (unless already changed)
            if ($equipment->getOrganization() === $this) {
                $equipment->setOrganization(null);
            }
        }

        return $this;
    }
}
