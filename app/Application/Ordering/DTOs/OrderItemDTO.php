<?php

namespace App\Application\Ordering\DTOs;

class OrderItemDTO
{
    public function __construct(
        public readonly string $productId,
        public readonly int $quantity
    ) {}
}
