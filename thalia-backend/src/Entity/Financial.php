<?php

namespace App\Entity;

use App\Enum\FinancialCategoryEnum;
use App\Enum\FinancialTypeEnum;
use App\Enum\VatRateEnum;
use App\Repository\FinancialRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: FinancialRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    fields: ['organization', 'season', 'category'],
    message: 'Une ligne budgétaire pour cette catégorie existe déjà pour cette saison culturelle.'
)]
class Financial
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: FinancialCategoryEnum::class)]
    private ?FinancialCategoryEnum $category = null;

    #[ORM\Column(type: 'string', enumType: FinancialTypeEnum::class)]
    private ?FinancialTypeEnum $type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'financials')]
    private ?Organization $organization = null;

    #[ORM\Column]
    private ?float $amount_ht = null;

    #[ORM\Column(type:'string', enumType: VatRateEnum::class, nullable: true)]
    private ?VatRateEnum $vat_rate = null;

    #[ORM\ManyToOne(inversedBy: 'financials')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Season $season = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?FinancialCategoryEnum
    {
        return $this->category;
    }

    public function setCategory(FinancialCategoryEnum $category): static
    {   
        $this->category =$category;
        if($category !== null){
           $this->setType($category->getFinancialTypeEnum());
        }
        

        return $this;
    }

    public function getType(): ?FinancialTypeEnum
    {
        return $this->type;
    }

    public function setType(FinancialTypeEnum $type): static
    {
        $this->type = $type;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getAmountHt(): ?float
    {
        return $this->amount_ht;
    }

    public function setAmountHt(float $amount_ht): static
    {
        $this->amount_ht = $amount_ht;

        return $this;
    }

    public function getVatRate(): ?VatRateEnum
    {
        return $this->vat_rate;
    }

    public function setVatRate(?VatRateEnum $vat_rate): static
    {
        $this->vat_rate = $vat_rate;

        return $this;
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
