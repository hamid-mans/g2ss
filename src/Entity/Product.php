<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use BcMath\Number;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[ORM\Column(length: 255, unique: false)]
    private ?string $refInterne = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refSupplier = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
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

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Brand $brands = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Color $color = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $weightKg = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $length = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $width = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $height = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?TVA $tva = null;

    /**
     * @var Collection<int, Movement>
     */
    #[ORM\OneToMany(targetEntity: Movement::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $movements;

    public function __construct()
    {
        $this->productUnits = new ArrayCollection();
        $this->movements = new ArrayCollection();
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

    public function getPriceTtc(): float
    {
        if (!$this->tva) {
            return $this->sellPrice;
        }

        return $this->sellPrice * (1 + ($this->tva->getValue() / 100));
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

    public function getBrands(): ?Brand
    {
        return $this->brands;
    }

    public function setBrands(?Brand $brands): static
    {
        $this->brands = $brands;

        return $this;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getWeightKg(): ?string
    {
        return $this->weightKg;
    }

    public function setWeightKg(?string $weightKg): static
    {
        $this->weightKg = $weightKg;

        return $this;
    }

    public function getLength(): ?string
    {
        return $this->length;
    }

    public function setLength(?string $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function setWidth(?string $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getTva(): ?TVA
    {
        return $this->tva;
    }

    public function setTva(?TVA $tva): static
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * @return Collection<int, Movement>
     */
    public function getMovements(): Collection
    {
        return $this->movements;
    }

    public function addMovement(Movement $movement): static
    {
        if (!$this->movements->contains($movement)) {
            $this->movements->add($movement);
            $movement->setProduct($this);
        }

        return $this;
    }

    public function removeMovement(Movement $movement): static
    {
        if ($this->movements->removeElement($movement)) {
            // set the owning side to null (unless already changed)
            if ($movement->getProduct() === $this) {
                $movement->setProduct(null);
            }
        }

        return $this;
    }
}
