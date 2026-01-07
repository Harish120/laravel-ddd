<?php

namespace App\Domain\Catalog\DomainEvents;

use App\Domain\Catalog\Entities\Product;

class ProductCreated
{
    public function __construct(
        public readonly Product $product
    ) {}
}
