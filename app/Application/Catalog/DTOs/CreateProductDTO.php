<?php

namespace App\Application\Catalog\DTOs;

use App\Shared\ValueObjects\Money;

class CreateProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly Money $price,
        public readonly int $stockQuantity = 0,
        public readonly ?string $sku = null,
        public readonly ?string $categoryId = null
    ) {}
}
