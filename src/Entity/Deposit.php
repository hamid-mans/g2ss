<?php

namespace App\Entity;

use App\Repository\DepositRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepositRepository::class)]
class Deposit
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

    #[ORM\ManyToOne(inversedBy: 'deposits')]
    private ?Company $company = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'deposits', orphanRemoval: true)]
    private Collection $users;

    /**
     * @var Collection<int, ProductUnit>
     */
    #[ORM\OneToMany(targetEntity: ProductUnit::class, mappedBy: 'deposit', orphanRemoval: true)]
    private Collection $productUnits;

    /**
     * @var Collection<int, Movement>
     */
    #[ORM\OneToMany(targetEntity: Movement::class, mappedBy: 'deposit', orphanRemoval: true)]
    private Collection $movements;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->productUnits = new ArrayCollection();
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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

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
            $user->addDeposit($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeDeposit($this);
        }

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
            $productUnit->setDeposit($this);
        }

        return $this;
    }

    public function removeProductUnit(ProductUnit $productUnit): static
    {
        if ($this->productUnits->removeElement($productUnit)) {
            // set the owning side to null (unless already changed)
            if ($productUnit->getDeposit() === $this) {
                $productUnit->setDeposit(null);
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
            $movement->setDeposit($this);
        }

        return $this;
    }

    public function removeMovement(Movement $movement): static
    {
        if ($this->movements->removeElement($movement)) {
            // set the owning side to null (unless already changed)
            if ($movement->getDeposit() === $this) {
                $movement->setDeposit(null);
            }
        }

        return $this;
    }
}
