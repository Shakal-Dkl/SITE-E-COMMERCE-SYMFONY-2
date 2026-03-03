<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column]
    private int $stockXs = 2;

    #[ORM\Column]
    private int $stockS = 2;

    #[ORM\Column]
    private int $stockM = 2;

    #[ORM\Column]
    private int $stockL = 2;

    #[ORM\Column]
    private int $stockXl = 2;

    #[ORM\Column]
    private bool $featured = false;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getStockXs(): int
    {
        return $this->stockXs;
    }

    public function setStockXs(int $stockXs): static
    {
        $this->stockXs = $stockXs;

        return $this;
    }

    public function getStockS(): int
    {
        return $this->stockS;
    }

    public function setStockS(int $stockS): static
    {
        $this->stockS = $stockS;

        return $this;
    }

    public function getStockM(): int
    {
        return $this->stockM;
    }

    public function setStockM(int $stockM): static
    {
        $this->stockM = $stockM;

        return $this;
    }

    public function getStockL(): int
    {
        return $this->stockL;
    }

    public function setStockL(int $stockL): static
    {
        $this->stockL = $stockL;

        return $this;
    }

    public function getStockXl(): int
    {
        return $this->stockXl;
    }

    public function setStockXl(int $stockXl): static
    {
        $this->stockXl = $stockXl;

        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): static
    {
        $this->featured = $featured;

        return $this;
    }
}
