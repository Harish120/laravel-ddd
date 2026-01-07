<?php

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Entities\Product;

interface ProductRepository
{
    public function findById(string $id): ?Product;

    public function findBySku(string $sku): ?Product;

    public function save(Product $product): void;

    public function delete(Product $product): void;

    /**
     * @return Product[]
     */
    public function findAll(): array;

    /**
     * @return Product[]
     */
    public function findActive(): array;
}

