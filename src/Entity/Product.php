<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ['refInterne'],
        message: 'Cette référence interne existe déjà.'
)]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $designation = null;

    #[Assert\NotBlank(message: "La référence interne est obligatoire.")]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $refInterne = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refSupplier = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Category $category = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $sellPrice = null;

    /**
     * @var Collection<int, ProductUnit>
     */
    #[ORM\OneToMany(targetEntity: ProductUnit::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $productUnits;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Company $company = null;

    public function __construct()
    {
        $this->productUnits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getRefInterne(): ?string
    {
        return $this->refInterne;
    }

    public function setRefInterne(string $refInterne): static
    {
        $this->refInterne = $refInterne;

        return $this;
    }

    public function getRefSupplier(): ?string
    {
        return $this->refSupplier;
    }

    public function setRefSupplier(?string $refSupplier): static
    {
        $this->refSupplier = $refSupplier;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getSellPrice(): ?string
    {
        return $this->sellPrice;
    }

    public function setSellPrice(?string $sellPrice): static
    {
        $this->sellPrice = $sellPrice;

        return $this;
    }

    /**
     * @return Collection<int, ProductUnit>
     */
    public function getProductUnits(): Collection
    {
        return $this->productUnits;
    }

    public function addProductUnit(ProductUnit $productUnit): static
    {
        if (!$this->productUnits->contains($productUnit)) {
            $this->productUnits->add($productUnit);
            $productUnit->setProduct($this);
        }

        return $this;
    }

    public function removeProductUnit(ProductUnit $productUnit): static
    {
        if ($this->productUnits->removeElement($productUnit)) {
            // set the owning side to null (unless already changed)
            if ($productUnit->getProduct() === $this) {
                $productUnit->setProduct(null);
            }
        }

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }
}
