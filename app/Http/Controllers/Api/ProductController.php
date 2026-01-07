<?php

namespace App\Http\Controllers\Api;

use App\Application\Catalog\DTOs\CreateProductDTO;
use App\Application\Catalog\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Shared\ValueObjects\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(): JsonResponse
    {
        $products = $this->productService->getAllProducts();

        return response()->json([
            'data' => $products,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'stock_quantity' => 'sometimes|integer|min:0',
            'sku' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|uuid',
        ]);

        $dto = new CreateProductDTO(
            $validated['name'],
            $validated['description'],
            new Money($validated['price'], $validated['currency'] ?? 'USD'),
            $validated['stock_quantity'] ?? 0,
            $validated['sku'] ?? null,
            $validated['category_id'] ?? null
        );

        $product = $this->productService->createProduct($dto);

        return response()->json([
            'data' => $product,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $product = $this->productService->getProduct($id);

            return response()->json([
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'stock_quantity' => 'sometimes|integer|min:0',
            'sku' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|uuid',
        ]);

        try {
            // Get existing product to fill missing fields
            $existing = $this->productService->getProduct($id);

            $dto = new CreateProductDTO(
                $validated['name'] ?? $existing->name,
                $validated['description'] ?? $existing->description,
                new Money(
                    $validated['price'] ?? $existing->price->getAmount(),
                    $validated['currency'] ?? $existing->price->getCurrency()
                ),
                $validated['stock_quantity'] ?? $existing->stockQuantity,
                $validated['sku'] ?? $existing->sku,
                $validated['category_id'] ?? $existing->categoryId
            );

            $product = $this->productService->updateProduct($id, $dto);

            return response()->json([
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function publish(string $id): JsonResponse
    {
        try {
            $product = $this->productService->publishProduct($id);

            return response()->json([
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
