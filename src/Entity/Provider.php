<?php

namespace App\Entity;

use App\Repository\ProviderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProviderRepository::class)]
class Provider
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cop = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address1_liv = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address2_liv = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cop_liv = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city_liv = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact_firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact_lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact_phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact_email = null;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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

    public function getPhone1(): ?string
    {
        return $this->phone1;
    }

    public function setPhone1(?string $phone1): static
    {
        $this->phone1 = $phone1;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): static
    {
        $this->phone2 = $phone2;

        return $this;
    }

    public function getAddress1Liv(): ?string
    {
        return $this->address1_liv;
    }

    public function setAddress1Liv(?string $address1_liv): static
    {
        $this->address1_liv = $address1_liv;

        return $this;
    }

    public function getAddress2Liv(): ?string
    {
        return $this->address2_liv;
    }

    public function setAddress2Liv(?string $address2_liv): static
    {
        $this->address2_liv = $address2_liv;

        return $this;
    }

    public function getCopLiv(): ?string
    {
        return $this->cop_liv;
    }

    public function setCopLiv(?string $cop_liv): static
    {
        $this->cop_liv = $cop_liv;

        return $this;
    }

    public function getCityLiv(): ?string
    {
        return $this->city_liv;
    }

    public function setCityLiv(?string $city_liv): static
    {
        $this->city_liv = $city_liv;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

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

    public function getContactFirstname(): ?string
    {
        return $this->contact_firstname;
    }

    public function setContactFirstname(?string $contact_firstname): static
    {
        $this->contact_firstname = $contact_firstname;

        return $this;
    }

    public function getContactLastname(): ?string
    {
        return $this->contact_lastname;
    }

    public function setContactLastname(?string $contact_lastname): static
    {
        $this->contact_lastname = $contact_lastname;

        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contact_phone;
    }

    public function setContactPhone(?string $contact_phone): static
    {
        $this->contact_phone = $contact_phone;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contact_email;
    }

    public function setContactEmail(?string $contact_email): static
    {
        $this->contact_email = $contact_email;

        return $this;
    }
}
