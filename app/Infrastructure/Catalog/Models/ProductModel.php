<?php

namespace App\Infrastructure\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductModel extends Model
{
    use HasUuids;

    protected $table = 'products';

    protected $fillable = [
        'id',
        'sku',
        'name',
        'description',
        'price_amount',
        'price_currency',
        'stock_quantity',
        'status',
        'category_id',
        'images',
    ];

    protected $casts = [
        'price_amount' => 'decimal:2',
        'stock_quantity' => 'integer',
        'images' => 'array',
    ];
}

