<?php

namespace App\Domain\Ordering\DomainEvents;

use App\Domain\Ordering\Entities\Order;

class OrderCreated
{
    public function __construct(
        public readonly Order $order
    ) {}
}
