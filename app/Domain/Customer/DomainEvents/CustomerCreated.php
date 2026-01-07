<?php

namespace App\Domain\Customer\DomainEvents;

use App\Domain\Customer\Entities\Customer;

class CustomerCreated
{
    public function __construct(
        public readonly Customer $customer
    ) {}
}
