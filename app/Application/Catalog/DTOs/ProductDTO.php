<?php

namespace App\Application\Catalog\DTOs;

use App\Shared\ValueObjects\Money;

class ProductDTO
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $sku,
        public readonly string $name,
        public readonly string $description,
        public readonly Money $price,
        public readonly int $stockQuantity,
        public readonly string $status,
        public readonly ?string $categoryId,
        public readonly array $images
    ) {
    }
}

