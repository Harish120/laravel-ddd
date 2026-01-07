<?php

namespace App\Infrastructure\Catalog\Repositories;

use App\Domain\Catalog\Entities\Product;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Domain\Catalog\ValueObjects\ProductStatus;
use App\Infrastructure\Catalog\Models\ProductModel;
use App\Shared\ValueObjects\Money;

class EloquentProductRepository implements ProductRepository
{
    public function findById(string $id): ?Product
    {
        $model = ProductModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySku(string $sku): ?Product
    {
        $model = ProductModel::where('sku', $sku)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function save(Product $product): void
    {
        $model = ProductModel::findOrNew($product->getId());

        $model->id = $product->getId();
        $model->sku = $product->getSku();
        $model->name = $product->getName();
        $model->description = $product->getDescription();
        $model->price_amount = $product->getPrice()->getAmount();
        $model->price_currency = $product->getPrice()->getCurrency();
        $model->stock_quantity = $product->getStockQuantity();
        $model->status = $product->getStatus()->getValue();
        $model->category_id = $product->getCategoryId();
        $model->images = $product->getImages();

        $model->save();
    }

    public function delete(Product $product): void
    {
        ProductModel::destroy($product->getId());
    }

    public function findAll(): array
    {
        return ProductModel::all()
            ->map(fn($model) => $this->toEntity($model))
            ->toArray();
    }

    public function findActive(): array
    {
        return ProductModel::where('status', 'active')
            ->get()
            ->map(fn($model) => $this->toEntity($model))
            ->toArray();
    }

    private function toEntity(ProductModel $model): Product
    {
        $product = new Product(
            $model->name,
            $model->description,
            new Money($model->price_amount, $model->price_currency),
            $model->stock_quantity
        );

        $product->setId($model->id);

        if ($model->sku) {
            $product->setSku($model->sku);
        }

        if ($model->category_id) {
            $product->setCategoryId($model->category_id);
        }

        $product->setStatus(ProductStatus::from($model->status));

        if ($model->images) {
            foreach ($model->images as $image) {
                $product->addImage($image);
            }
        }

        return $product;
    }
}

