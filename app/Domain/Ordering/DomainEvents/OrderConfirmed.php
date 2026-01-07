<?php

namespace App\Domain\Ordering\DomainEvents;

use App\Domain\Ordering\Entities\Order;

class OrderConfirmed
{
    public function __construct(
        public readonly Order $order
    ) {
    }
}

