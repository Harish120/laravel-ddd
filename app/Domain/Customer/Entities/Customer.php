<?php

namespace App\Domain\Customer\Entities;

use App\Shared\Entity;
use App\Shared\ValueObjects\Address;
use InvalidArgumentException;

class Customer extends Entity
{
    private string $email;
    private string $firstName;
    private string $lastName;
    private ?string $phone = null;
    private ?Address $billingAddress = null;
    private ?Address $shippingAddress = null;
    private bool $isActive = true;

    public function __construct(
        string $email,
        string $firstName,
        string $lastName
    ) {
        $this->setEmail($email);
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address');
        }
        $this->email = strtolower(trim($email));
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        if (empty(trim($firstName))) {
            throw new InvalidArgumentException('First name cannot be empty');
        }
        $this->firstName = trim($firstName);
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        if (empty(trim($lastName))) {
            throw new InvalidArgumentException('Last name cannot be empty');
        }
        $this->lastName = trim($lastName);
    }

    public function getFullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone ? trim($phone) : null;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?Address $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?Address $shippingAddress): void
    {
        $this->shippingAddress = $shippingAddress;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function hasBillingAddress(): bool
    {
        return $this->billingAddress !== null;
    }

    public function hasShippingAddress(): bool
    {
        return $this->shippingAddress !== null;
    }
}

