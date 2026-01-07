<?php

namespace App\Http\Controllers\Api;

use App\Application\Ordering\DTOs\CreateOrderDTO;
use App\Application\Ordering\DTOs\OrderItemDTO;
use App\Application\Ordering\Services\OrderService;
use App\Http\Controllers\Controller;
use App\Shared\ValueObjects\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|uuid',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'sometimes|array',
            'shipping_address.street' => 'required_with:shipping_address|string',
            'shipping_address.city' => 'required_with:shipping_address|string',
            'shipping_address.state' => 'required_with:shipping_address|string',
            'shipping_address.zip_code' => 'required_with:shipping_address|string',
            'shipping_address.country' => 'required_with:shipping_address|string',
            'billing_address' => 'sometimes|array',
            'notes' => 'sometimes|string',
        ]);

        $items = array_map(
            fn ($item) => new OrderItemDTO($item['product_id'], $item['quantity']),
            $validated['items']
        );

        $shippingAddress = null;
        if (isset($validated['shipping_address'])) {
            $addr = $validated['shipping_address'];
            $shippingAddress = new Address(
                $addr['street'],
                $addr['city'],
                $addr['state'],
                $addr['zip_code'],
                $addr['country'],
                $addr['address_line2'] ?? null
            );
        }

        $billingAddress = null;
        if (isset($validated['billing_address'])) {
            $addr = $validated['billing_address'];
            $billingAddress = new Address(
                $addr['street'],
                $addr['city'],
                $addr['state'],
                $addr['zip_code'],
                $addr['country'],
                $addr['address_line2'] ?? null
            );
        }

        try {
            $dto = new CreateOrderDTO(
                $validated['customer_id'],
                $items,
                $shippingAddress,
                $billingAddress,
                $validated['notes'] ?? null
            );

            $order = $this->orderService->createOrder($dto);

            return response()->json([
                'data' => [
                    'id' => $order->getId(),
                    'order_number' => $order->getOrderNumber(),
                    'status' => $order->getStatus()->getValue(),
                    'total' => $order->getTotal()->getAmount(),
                    'currency' => $order->getTotal()->getCurrency(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->getOrder($id);

            return response()->json([
                'data' => [
                    'id' => $order->getId(),
                    'order_number' => $order->getOrderNumber(),
                    'customer_id' => $order->getCustomerId(),
                    'status' => $order->getStatus()->getValue(),
                    'subtotal' => $order->getSubtotal()->getAmount(),
                    'shipping_cost' => $order->getShippingCost()->getAmount(),
                    'tax' => $order->getTax()->getAmount(),
                    'total' => $order->getTotal()->getAmount(),
                    'currency' => $order->getTotal()->getCurrency(),
                    'items' => array_map(fn ($item) => [
                        'id' => $item->getId(),
                        'product_id' => $item->getProductId(),
                        'product_name' => $item->getProductName(),
                        'quantity' => $item->getQuantity(),
                        'unit_price' => $item->getUnitPrice()->getAmount(),
                        'total_price' => $item->getTotalPrice()->getAmount(),
                    ], $order->getItems()),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function confirm(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->confirmOrder($id);

            return response()->json([
                'data' => [
                    'id' => $order->getId(),
                    'order_number' => $order->getOrderNumber(),
                    'status' => $order->getStatus()->getValue(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
