<?php

namespace App\Domain\Ordering\Entities;

use App\Domain\Ordering\ValueObjects\OrderStatus;
use App\Shared\Entity;
use App\Shared\ValueObjects\Address;
use App\Shared\ValueObjects\Money;
use InvalidArgumentException;

class Order extends Entity
{
    private string $customerId;

    private string $orderNumber;

    private OrderStatus $status;

    private array $items = [];

    private Money $subtotal;

    private Money $shippingCost;

    private Money $tax;

    private Money $total;

    private ?Address $shippingAddress = null;

    private ?Address $billingAddress = null;

    private ?string $notes = null;

    private \DateTimeImmutable $createdAt;

    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(
        string $customerId,
        string $orderNumber
    ) {
        $this->setCustomerId($customerId);
        $this->setOrderNumber($orderNumber);
        $this->status = OrderStatus::PENDING();
        $this->subtotal = new Money(0);
        $this->shippingCost = new Money(0);
        $this->tax = new Money(0);
        $this->total = new Money(0);
        $this->createdAt = new \DateTimeImmutable;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    private function setCustomerId(string $customerId): void
    {
        if (empty(trim($customerId))) {
            throw new InvalidArgumentException('Customer ID cannot be empty');
        }
        $this->customerId = $customerId;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    private function setOrderNumber(string $orderNumber): void
    {
        if (empty(trim($orderNumber))) {
            throw new InvalidArgumentException('Order number cannot be empty');
        }
        $this->orderNumber = trim($orderNumber);
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): void
    {
        $this->status = $status;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getSubtotal(): Money
    {
        return $this->subtotal;
    }

    public function getShippingCost(): Money
    {
        return $this->shippingCost;
    }

    public function getTax(): Money
    {
        return $this->tax;
    }

    public function getTotal(): Money
    {
        return $this->total;
    }

    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?Address $shippingAddress): void
    {
        $this->shippingAddress = $shippingAddress;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?Address $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes ? trim($notes) : null;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function addItem(OrderItem $item): void
    {
        if ($this->status->getValue() !== OrderStatus::PENDING()->getValue()) {
            throw new InvalidArgumentException('Cannot add items to a non-pending order');
        }

        $this->addItemInternal($item);
    }

    private function addItemInternal(OrderItem $item): void
    {
        // Check if item with same product already exists
        foreach ($this->items as $existingItem) {
            if ($existingItem->getProductId() === $item->getProductId()) {
                $existingItem->increaseQuantity($item->getQuantity());
                $this->recalculateTotals();

                return;
            }
        }

        $this->items[] = $item;
        $this->recalculateTotals();
    }

    public function removeItem(string $itemId): void
    {
        if ($this->status->getValue() !== OrderStatus::PENDING()->getValue()) {
            throw new InvalidArgumentException('Cannot remove items from a non-pending order');
        }

        $this->items = array_values(array_filter($this->items, function ($item) use ($itemId) {
            return $item->getId() !== $itemId;
        }));

        $this->recalculateTotals();
    }

    public function setShippingCost(Money $shippingCost): void
    {
        if ($shippingCost->getAmount() < 0) {
            throw new InvalidArgumentException('Shipping cost cannot be negative');
        }
        $this->shippingCost = $shippingCost;
        $this->recalculateTotals();
    }

    public function setTax(Money $tax): void
    {
        if ($tax->getAmount() < 0) {
            throw new InvalidArgumentException('Tax cannot be negative');
        }
        $this->tax = $tax;
        $this->recalculateTotals();
    }

    public function confirm(): void
    {
        if (! $this->status->canTransitionTo(OrderStatus::CONFIRMED())) {
            throw new InvalidArgumentException('Order cannot be confirmed from current status');
        }

        if (empty($this->items)) {
            throw new InvalidArgumentException('Cannot confirm order without items');
        }

        if ($this->shippingAddress === null) {
            throw new InvalidArgumentException('Shipping address is required to confirm order');
        }

        $this->status = OrderStatus::CONFIRMED();
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function cancel(): void
    {
        if (! $this->status->canTransitionTo(OrderStatus::CANCELLED())) {
            throw new InvalidArgumentException('Order cannot be cancelled from current status');
        }

        $this->status = OrderStatus::CANCELLED();
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function markAsProcessing(): void
    {
        if (! $this->status->canTransitionTo(OrderStatus::PROCESSING())) {
            throw new InvalidArgumentException('Order cannot be marked as processing from current status');
        }

        $this->status = OrderStatus::PROCESSING();
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function markAsShipped(): void
    {
        if (! $this->status->canTransitionTo(OrderStatus::SHIPPED())) {
            throw new InvalidArgumentException('Order cannot be marked as shipped from current status');
        }

        $this->status = OrderStatus::SHIPPED();
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function markAsDelivered(): void
    {
        if (! $this->status->canTransitionTo(OrderStatus::DELIVERED())) {
            throw new InvalidArgumentException('Order cannot be marked as delivered from current status');
        }

        $this->status = OrderStatus::DELIVERED();
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = new Money(0);
        foreach ($this->items as $item) {
            $this->subtotal = $this->subtotal->add($item->getTotalPrice());
        }

        $this->total = $this->subtotal
            ->add($this->shippingCost)
            ->add($this->tax);
    }

    /**
     * Restore items from persistence (bypasses validation)
     * This method should only be used by repositories when loading from database
     */
    public function restoreItems(array $items): void
    {
        $this->items = $items;
        $this->recalculateTotals();
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function getTotalQuantity(): int
    {
        return array_sum(array_map(fn ($item) => $item->getQuantity(), $this->items));
    }
}
