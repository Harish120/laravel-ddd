<?php

namespace App\Shared\Exceptions;

class EntityNotFoundException extends DomainException
{
    public static function forEntity(string $entityName, string $identifier): self
    {
        return new self("{$entityName} with identifier '{$identifier}' not found");
    }
}

