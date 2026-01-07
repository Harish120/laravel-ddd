<?php

namespace App\Application\Ordering\DTOs;

use App\Shared\ValueObjects\Address;

class CreateOrderDTO
{
    /**
     * @param OrderItemDTO[] $items
     */
    public function __construct(
        public readonly string $customerId,
        public readonly array $items,
        public readonly ?Address $shippingAddress = null,
        public readonly ?Address $billingAddress = null,
        public readonly ?string $notes = null
    ) {
    }
}

