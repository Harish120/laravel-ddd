<?php

namespace App\Domain\Catalog\DomainEvents;

use App\Domain\Catalog\Entities\Product;

class ProductStockReduced
{
    public function __construct(
        public readonly Product $product,
        public readonly int $quantity
    ) {}
}
