<?php

namespace App\Shared\ValueObjects;

abstract class ValueObject
{
    /**
     * Compare two value objects for equality
     */
    public function equals(ValueObject $other): bool
    {
        return get_class($this) === get_class($other) && $this->getValues() === $other->getValues();
    }

    /**
     * Get all values as an array for comparison
     */
    abstract protected function getValues(): array;
}

