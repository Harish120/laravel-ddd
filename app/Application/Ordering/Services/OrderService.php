<?php

namespace App\Application\Ordering\Services;

use App\Application\Ordering\DTOs\CreateOrderDTO;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Domain\Ordering\Entities\Order;
use App\Domain\Ordering\Entities\OrderItem;
use App\Domain\Ordering\Repositories\OrderRepository;
use App\Domain\Ordering\DomainEvents\OrderCreated;
use App\Shared\Exceptions\EntityNotFoundException;
use App\Shared\ValueObjects\Money;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly ProductRepository $productRepository
    ) {
    }

    public function createOrder(CreateOrderDTO $dto): Order
    {
        $order = new Order(
            $dto->customerId,
            $this->orderRepository->generateOrderNumber()
        );

        $order->setId(Str::uuid()->toString());

        if ($dto->shippingAddress) {
            $order->setShippingAddress($dto->shippingAddress);
        }

        if ($dto->billingAddress) {
            $order->setBillingAddress($dto->billingAddress);
        }

        if ($dto->notes) {
            $order->setNotes($dto->notes);
        }

        // Add order items
        foreach ($dto->items as $itemDTO) {
            $product = $this->productRepository->findById($itemDTO->productId);

            if (!$product) {
                throw EntityNotFoundException::forEntity('Product', $itemDTO->productId);
            }

            if (!$product->isAvailable()) {
                throw new \DomainException("Product {$product->getName()} is not available");
            }

            if ($product->getStockQuantity() < $itemDTO->quantity) {
                throw new \DomainException("Insufficient stock for product {$product->getName()}");
            }

            $orderItem = new OrderItem(
                $product->getId(),
                $product->getName(),
                $product->getSku() ?? '',
                $product->getPrice(),
                $itemDTO->quantity
            );

            $orderItem->setId(Str::uuid()->toString());
            $order->addItem($orderItem);

            // Reduce stock
            $product->reduceStock($itemDTO->quantity);
            $this->productRepository->save($product);
        }

        // Set default shipping cost (can be calculated by shipping service)
        $order->setShippingCost(new Money(10.00));
        $order->setTax(new Money($order->getSubtotal()->getAmount() * 0.1)); // 10% tax

        $this->orderRepository->save($order);

        return $order;
    }

    public function confirmOrder(string $orderId): Order
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            throw EntityNotFoundException::forEntity('Order', $orderId);
        }

        $order->confirm();
        $this->orderRepository->save($order);

        return $order;
    }

    public function getOrder(string $orderId): Order
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            throw EntityNotFoundException::forEntity('Order', $orderId);
        }

        return $order;
    }

    public function getCustomerOrders(string $customerId): array
    {
        return $this->orderRepository->findByCustomerId($customerId);
    }
}

