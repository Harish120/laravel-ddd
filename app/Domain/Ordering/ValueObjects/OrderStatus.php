<?php

namespace App\Domain\Ordering\ValueObjects;

use App\Shared\ValueObjects\ValueObject;
use InvalidArgumentException;

class OrderStatus extends ValueObject
{
    private const PENDING = 'pending';
    private const CONFIRMED = 'confirmed';
    private const PROCESSING = 'processing';
    private const SHIPPED = 'shipped';
    private const DELIVERED = 'delivered';
    private const CANCELLED = 'cancelled';

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, [
            self::PENDING,
            self::CONFIRMED,
            self::PROCESSING,
            self::SHIPPED,
            self::DELIVERED,
            self::CANCELLED,
        ], true)) {
            throw new InvalidArgumentException("Invalid order status: {$value}");
        }
    }

    public static function PENDING(): self
    {
        return new self(self::PENDING);
    }

    public static function CONFIRMED(): self
    {
        return new self(self::CONFIRMED);
    }

    public static function PROCESSING(): self
    {
        return new self(self::PROCESSING);
    }

    public static function SHIPPED(): self
    {
        return new self(self::SHIPPED);
    }

    public static function DELIVERED(): self
    {
        return new self(self::DELIVERED);
    }

    public static function CANCELLED(): self
    {
        return new self(self::CANCELLED);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        $allowedTransitions = [
            self::PENDING => [self::CONFIRMED, self::CANCELLED],
            self::CONFIRMED => [self::PROCESSING, self::CANCELLED],
            self::PROCESSING => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED => [self::DELIVERED],
            self::DELIVERED => [],
            self::CANCELLED => [],
        ];

        return in_array($newStatus->value, $allowedTransitions[$this->value] ?? [], true);
    }

    protected function getValues(): array
    {
        return ['value' => $this->value];
    }
}

