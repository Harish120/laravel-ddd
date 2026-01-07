<?php

namespace App\Domain\Ordering\Entities;

use App\Shared\Entity;
use App\Shared\ValueObjects\Money;
use InvalidArgumentException;

class OrderItem extends Entity
{
    private string $productId;
    private string $productName;
    private string $productSku;
    private Money $unitPrice;
    private int $quantity;
    private Money $totalPrice;

    public function __construct(
        string $productId,
        string $productName,
        string $productSku,
        Money $unitPrice,
        int $quantity
    ) {
        $this->setProductId($productId);
        $this->setProductName($productName);
        $this->setProductSku($productSku);
        $this->setUnitPrice($unitPrice);
        $this->setQuantity($quantity);
        $this->calculateTotal();
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    private function setProductId(string $productId): void
    {
        if (empty(trim($productId))) {
            throw new InvalidArgumentException('Product ID cannot be empty');
        }
        $this->productId = $productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    private function setProductName(string $productName): void
    {
        if (empty(trim($productName))) {
            throw new InvalidArgumentException('Product name cannot be empty');
        }
        $this->productName = trim($productName);
    }

    public function getProductSku(): string
    {
        return $this->productSku;
    }

    private function setProductSku(string $productSku): void
    {
        if (empty(trim($productSku))) {
            throw new InvalidArgumentException('Product SKU cannot be empty');
        }
        $this->productSku = trim($productSku);
    }

    public function getUnitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(Money $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
        $this->calculateTotal();
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero');
        }
        $this->quantity = $quantity;
        $this->calculateTotal();
    }

    public function getTotalPrice(): Money
    {
        return $this->totalPrice;
    }

    private function calculateTotal(): void
    {
        $this->totalPrice = $this->unitPrice->multiply($this->quantity);
    }

    public function increaseQuantity(int $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero');
        }
        $this->setQuantity($this->quantity + $amount);
    }

    public function decreaseQuantity(int $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero');
        }
        if ($this->quantity <= $amount) {
            throw new InvalidArgumentException('Cannot decrease quantity below zero');
        }
        $this->setQuantity($this->quantity - $amount);
    }
}

