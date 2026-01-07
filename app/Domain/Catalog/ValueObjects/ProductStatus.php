<?php

namespace App\Domain\Catalog\ValueObjects;

use App\Shared\ValueObjects\ValueObject;
use InvalidArgumentException;

class ProductStatus extends ValueObject
{
    private const DRAFT = 'draft';
    private const ACTIVE = 'active';
    private const ARCHIVED = 'archived';

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, [self::DRAFT, self::ACTIVE, self::ARCHIVED], true)) {
            throw new InvalidArgumentException("Invalid product status: {$value}");
        }
    }

    public static function DRAFT(): self
    {
        return new self(self::DRAFT);
    }

    public static function ACTIVE(): self
    {
        return new self(self::ACTIVE);
    }

    public static function ARCHIVED(): self
    {
        return new self(self::ARCHIVED);
    }

    public static function from(string $value): self
    {
        return match ($value) {
            self::DRAFT => self::DRAFT(),
            self::ACTIVE => self::ACTIVE(),
            self::ARCHIVED => self::ARCHIVED(),
            default => throw new InvalidArgumentException("Invalid product status: {$value}"),
        };
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this->value === self::ARCHIVED;
    }

    protected function getValues(): array
    {
        return ['value' => $this->value];
    }
}

