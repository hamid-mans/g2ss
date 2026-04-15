<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cop = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'company', cascade: ['remove'])]
    private Collection $users;

    /**
     * @var Collection<int, Deposit>
     */
    #[ORM\OneToMany(targetEntity: Deposit::class, mappedBy: 'company', cascade: ['remove'])]
    private Collection $deposits;

    /**
     * @var Collection<int, Modules>
     */
    #[ORM\ManyToMany(targetEntity: Modules::class, inversedBy: 'companies')]
    private Collection $modules;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'company', cascade: ['remove'])]
    private Collection $products;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $categories;

    /**
     * @var Collection<int, Brand>
     */
    #[ORM\OneToMany(targetEntity: Brand::class, mappedBy: 'company')]
    private Collection $brands;

    /**
     * @var Collection<int, Color>
     */
    #[ORM\OneToMany(targetEntity: Color::class, mappedBy: 'company')]
    private Collection $colors;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dimensions_unit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $weight_unit = null;

    /**
     * @var Collection<int, TVA>
     */
    #[ORM\OneToMany(targetEntity: TVA::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $tVAs;

    /**
     * @var Collection<int, Movement>
     */
    #[ORM\OneToMany(targetEntity: Movement::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $movements;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->deposits = new ArrayCollection();
        $this->modules = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->brands = new ArrayCollection();
        $this->colors = new ArrayCollection();
        $this->tVAs = new ArrayCollection();
        $this->movements = new ArrayCollection();
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

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1(?string $address1): static
    {
        $this->address1 = $address1;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): static
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getCop(): ?string
    {
        return $this->cop;
    }

    public function setCop(?string $cop): static
    {
        $this->cop = $cop;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

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
            $user->setCompany($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCompany() === $this) {
                $user->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Deposit>
     */
    public function getDeposits(): Collection
    {
        return $this->deposits;
    }

    public function addDeposit(Deposit $deposit): static
    {
        if (!$this->deposits->contains($deposit)) {
            $this->deposits->add($deposit);
            $deposit->setCompany($this);
        }

        return $this;
    }

    public function removeDeposit(Deposit $deposit): static
    {
        if ($this->deposits->removeElement($deposit)) {
            // set the owning side to null (unless already changed)
            if ($deposit->getCompany() === $this) {
                $deposit->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Modules>
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(Modules $module): static
    {
        if (!$this->modules->contains($module)) {
            $this->modules->add($module);
        }

        return $this;
    }

    public function removeModule(Modules $module): static
    {
        $this->modules->removeElement($module);

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCompany($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCompany() === $this) {
                $product->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setCompany($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getCompany() === $this) {
                $category->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Brand>
     */
    public function getBrands(): Collection
    {
        return $this->brands;
    }

    public function addBrand(Brand $brand): static
    {
        if (!$this->brands->contains($brand)) {
            $this->brands->add($brand);
            $brand->setCompany($this);
        }

        return $this;
    }

    public function removeBrand(Brand $brand): static
    {
        if ($this->brands->removeElement($brand)) {
            // set the owning side to null (unless already changed)
            if ($brand->getCompany() === $this) {
                $brand->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Color>
     */
    public function getColors(): Collection
    {
        return $this->colors;
    }

    public function addColor(Color $color): static
    {
        if (!$this->colors->contains($color)) {
            $this->colors->add($color);
            $color->setCompany($this);
        }

        return $this;
    }

    public function removeColor(Color $color): static
    {
        if ($this->colors->removeElement($color)) {
            // set the owning side to null (unless already changed)
            if ($color->getCompany() === $this) {
                $color->setCompany(null);
            }
        }

        return $this;
    }

    public function getDimensionsUnit(): ?string
    {
        return $this->dimensions_unit;
    }

    public function setDimensionsUnit(?string $dimensions_unit): static
    {
        $this->dimensions_unit = $dimensions_unit;

        return $this;
    }

    public function getWeightUnit(): ?string
    {
        return $this->weight_unit;
    }

    public function setWeightUnit(?string $weight_unit): static
    {
        $this->weight_unit = $weight_unit;

        return $this;
    }

    /**
     * @return Collection<int, TVA>
     */
    public function getTVAs(): Collection
    {
        return $this->tVAs;
    }

    public function addTVA(TVA $tVA): static
    {
        if (!$this->tVAs->contains($tVA)) {
            $this->tVAs->add($tVA);
            $tVA->setCompany($this);
        }

        return $this;
    }

    public function removeTVA(TVA $tVA): static
    {
        if ($this->tVAs->removeElement($tVA)) {
            // set the owning side to null (unless already changed)
            if ($tVA->getCompany() === $this) {
                $tVA->setCompany(null);
            }
        }

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
            $movement->setCompany($this);
        }

        return $this;
    }

    public function removeMovement(Movement $movement): static
    {
        if ($this->movements->removeElement($movement)) {
            // set the owning side to null (unless already changed)
            if ($movement->getCompany() === $this) {
                $movement->setCompany(null);
            }
        }

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
}
