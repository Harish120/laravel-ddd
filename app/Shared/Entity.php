<?php

namespace App\Shared;

abstract class Entity
{
    protected ?string $id = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function equals(Entity $other): bool
    {
        return get_class($this) === get_class($other) && $this->id === $other->id;
    }
}
