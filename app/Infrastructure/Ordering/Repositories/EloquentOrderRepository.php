<?php

namespace App\Infrastructure\Ordering\Repositories;

use App\Domain\Ordering\Entities\Order;
use App\Domain\Ordering\Entities\OrderItem;
use App\Domain\Ordering\Repositories\OrderRepository;
use App\Domain\Ordering\ValueObjects\OrderStatus;
use App\Infrastructure\Ordering\Models\OrderModel;
use App\Infrastructure\Ordering\Models\OrderItemModel;
use App\Shared\ValueObjects\Address;
use App\Shared\ValueObjects\Money;
use Illuminate\Support\Str;

class EloquentOrderRepository implements OrderRepository
{
    public function findById(string $id): ?Order
    {
        $model = OrderModel::with('items')->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        $model = OrderModel::with('items')->where('order_number', $orderNumber)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByCustomerId(string $customerId): array
    {
        return OrderModel::with('items')
            ->where('customer_id', $customerId)
            ->get()
            ->map(fn($model) => $this->toEntity($model))
            ->toArray();
    }

    public function save(Order $order): void
    {
        $model = OrderModel::findOrNew($order->getId());

        $model->id = $order->getId();
        $model->customer_id = $order->getCustomerId();
        $model->order_number = $order->getOrderNumber();
        $model->status = $order->getStatus()->getValue();
        $model->subtotal_amount = $order->getSubtotal()->getAmount();
        $model->subtotal_currency = $order->getSubtotal()->getCurrency();
        $model->shipping_cost_amount = $order->getShippingCost()->getAmount();
        $model->shipping_cost_currency = $order->getShippingCost()->getCurrency();
        $model->tax_amount = $order->getTax()->getAmount();
        $model->tax_currency = $order->getTax()->getCurrency();
        $model->total_amount = $order->getTotal()->getAmount();
        $model->total_currency = $order->getTotal()->getCurrency();
        $model->shipping_address = $order->getShippingAddress() ? $this->addressToArray($order->getShippingAddress()) : null;
        $model->billing_address = $order->getBillingAddress() ? $this->addressToArray($order->getBillingAddress()) : null;
        $model->notes = $order->getNotes();

        $model->save();

        // Save order items
        OrderItemModel::where('order_id', $order->getId())->delete();
        foreach ($order->getItems() as $item) {
            $itemModel = new OrderItemModel();
            $itemModel->id = $item->getId();
            $itemModel->order_id = $order->getId();
            $itemModel->product_id = $item->getProductId();
            $itemModel->product_name = $item->getProductName();
            $itemModel->product_sku = $item->getProductSku();
            $itemModel->unit_price_amount = $item->getUnitPrice()->getAmount();
            $itemModel->unit_price_currency = $item->getUnitPrice()->getCurrency();
            $itemModel->quantity = $item->getQuantity();
            $itemModel->total_price_amount = $item->getTotalPrice()->getAmount();
            $itemModel->total_price_currency = $item->getTotalPrice()->getCurrency();
            $itemModel->save();
        }
    }

    public function delete(Order $order): void
    {
        OrderModel::destroy($order->getId());
    }

    public function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(Str::random(8));
        } while (OrderModel::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    private function toEntity(OrderModel $model): Order
    {
        $order = new Order($model->customer_id, $model->order_number);
        $order->setId($model->id);

        // Set status
        $order->setStatus(OrderStatus::from($model->status));

        // Set addresses
        if ($model->shipping_address) {
            $order->setShippingAddress($this->arrayToAddress($model->shipping_address));
        }
        if ($model->billing_address) {
            $order->setBillingAddress($this->arrayToAddress($model->billing_address));
        }

        if ($model->notes) {
            $order->setNotes($model->notes);
        }

        // Set money values
        $order->setShippingCost(new Money($model->shipping_cost_amount, $model->shipping_cost_currency));
        $order->setTax(new Money($model->tax_amount, $model->tax_currency));

        // Restore items from persistence
        $items = [];
        foreach ($model->items as $itemModel) {
            $item = new OrderItem(
                $itemModel->product_id,
                $itemModel->product_name,
                $itemModel->product_sku,
                new Money($itemModel->unit_price_amount, $itemModel->unit_price_currency),
                $itemModel->quantity
            );
            $item->setId($itemModel->id);
            $items[] = $item;
        }
        $order->restoreItems($items);

        return $order;
    }

    private function addressToArray(Address $address): array
    {
        return [
            'street' => $address->getStreet(),
            'addressLine2' => $address->getAddressLine2(),
            'city' => $address->getCity(),
            'state' => $address->getState(),
            'zipCode' => $address->getZipCode(),
            'country' => $address->getCountry(),
        ];
    }

    private function arrayToAddress(array $data): Address
    {
        return new Address(
            $data['street'],
            $data['city'],
            $data['state'],
            $data['zipCode'],
            $data['country'],
            $data['addressLine2'] ?? null
        );
    }
}

