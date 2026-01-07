<?php

namespace App\Domain\Ordering\Repositories;

use App\Domain\Ordering\Entities\Order;

interface OrderRepository
{
    public function findById(string $id): ?Order;

    public function findByOrderNumber(string $orderNumber): ?Order;

    /**
     * @return Order[]
     */
    public function findByCustomerId(string $customerId): array;

    public function save(Order $order): void;

    public function delete(Order $order): void;

    public function generateOrderNumber(): string;
}
