<?php

namespace App\Application\Catalog\Services;

use App\Application\Catalog\DTOs\CreateProductDTO;
use App\Application\Catalog\DTOs\ProductDTO;
use App\Domain\Catalog\Entities\Product;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Domain\Catalog\ValueObjects\ProductStatus;
use App\Shared\Exceptions\EntityNotFoundException;
use App\Shared\ValueObjects\Money;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    public function createProduct(CreateProductDTO $dto): ProductDTO
    {
        $product = new Product(
            $dto->name,
            $dto->description,
            $dto->price,
            $dto->stockQuantity
        );

        if ($dto->sku) {
            $product->setSku($dto->sku);
        } else {
            $product->setSku($this->generateSku($dto->name));
        }

        if ($dto->categoryId) {
            $product->setCategoryId($dto->categoryId);
        }

        $product->setId(Str::uuid()->toString());
        $this->productRepository->save($product);

        return $this->toDTO($product);
    }

    public function getProduct(string $id): ProductDTO
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw EntityNotFoundException::forEntity('Product', $id);
        }

        return $this->toDTO($product);
    }

    public function updateProduct(string $id, CreateProductDTO $dto): ProductDTO
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw EntityNotFoundException::forEntity('Product', $id);
        }

        $product->setName($dto->name);
        $product->setDescription($dto->description);
        $product->setPrice($dto->price);
        $product->setStockQuantity($dto->stockQuantity);

        if ($dto->sku) {
            $product->setSku($dto->sku);
        }

        if ($dto->categoryId) {
            $product->setCategoryId($dto->categoryId);
        }

        $this->productRepository->save($product);

        return $this->toDTO($product);
    }

    public function publishProduct(string $id): ProductDTO
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw EntityNotFoundException::forEntity('Product', $id);
        }

        $product->publish();
        $this->productRepository->save($product);

        return $this->toDTO($product);
    }

    public function getAllProducts(): array
    {
        $products = $this->productRepository->findAll();

        return array_map(fn($product) => $this->toDTO($product), $products);
    }

    public function getActiveProducts(): array
    {
        $products = $this->productRepository->findActive();

        return array_map(fn($product) => $this->toDTO($product), $products);
    }

    private function toDTO(Product $product): ProductDTO
    {
        return new ProductDTO(
            $product->getId(),
            $product->getSku(),
            $product->getName(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getStockQuantity(),
            $product->getStatus()->getValue(),
            $product->getCategoryId(),
            $product->getImages()
        );
    }

    private function generateSku(string $name): string
    {
        $base = strtoupper(Str::slug($name, ''));
        $base = substr($base, 0, 10);
        $random = Str::random(4);

        return $base . '-' . $random;
    }
}

