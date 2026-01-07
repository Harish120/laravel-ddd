<?php

namespace App\Domain\Customer\Repositories;

use App\Domain\Customer\Entities\Customer;

interface CustomerRepository
{
    public function findById(string $id): ?Customer;

    public function findByEmail(string $email): ?Customer;

    public function save(Customer $customer): void;

    public function delete(Customer $customer): void;
}
