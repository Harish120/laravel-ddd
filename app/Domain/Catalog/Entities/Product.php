<?php

namespace App\Domain\Catalog\Entities;

use App\Domain\Catalog\ValueObjects\ProductStatus;
use App\Shared\Entity;
use App\Shared\ValueObjects\Money;
use InvalidArgumentException;

class Product extends Entity
{
    private ?string $sku = null;

    private string $name;

    private string $description;

    private Money $price;

    private int $stockQuantity = 0;

    private ProductStatus $status;

    private ?string $categoryId = null;

    private array $images = [];

    public function __construct(
        string $name,
        string $description,
        Money $price,
        int $stockQuantity = 0
    ) {
        $this->setName($name);
        $this->setDescription($description);
        $this->setPrice($price);
        $this->setStockQuantity($stockQuantity);
        $this->status = ProductStatus::DRAFT();
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): void
    {
        if (empty(trim($sku))) {
            throw new InvalidArgumentException('SKU cannot be empty');
        }
        $this->sku = $sku;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Product name cannot be empty');
        }
        $this->name = trim($name);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = trim($description);
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    public function getStockQuantity(): int
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(int $stockQuantity): void
    {
        if ($stockQuantity < 0) {
            throw new InvalidArgumentException('Stock quantity cannot be negative');
        }
        $this->stockQuantity = $stockQuantity;
    }

    public function getStatus(): ProductStatus
    {
        return $this->status;
    }

    public function setStatus(ProductStatus $status): void
    {
        $this->status = $status;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function setCategoryId(?string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function addImage(string $imageUrl): void
    {
        if (! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid image URL');
        }
        if (! in_array($imageUrl, $this->images, true)) {
            $this->images[] = $imageUrl;
        }
    }

    public function removeImage(string $imageUrl): void
    {
        $this->images = array_values(array_filter($this->images, fn ($img) => $img !== $imageUrl));
    }

    public function isAvailable(): bool
    {
        return $this->status->equals(ProductStatus::ACTIVE()) && $this->stockQuantity > 0;
    }

    public function reduceStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero');
        }
        if ($this->stockQuantity < $quantity) {
            throw new InvalidArgumentException('Insufficient stock');
        }
        $this->stockQuantity -= $quantity;
    }

    public function increaseStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero');
        }
        $this->stockQuantity += $quantity;
    }

    public function publish(): void
    {
        if (empty($this->sku)) {
            throw new InvalidArgumentException('Product must have a SKU before publishing');
        }
        $this->status = ProductStatus::ACTIVE();
    }

    public function unpublish(): void
    {
        $this->status = ProductStatus::DRAFT();
    }
}
