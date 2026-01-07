<?php

namespace App\Shared;

interface Repository
{
    public function findById(string $id): ?Entity;

    public function save(Entity $entity): void;

    public function delete(Entity $entity): void;
}
