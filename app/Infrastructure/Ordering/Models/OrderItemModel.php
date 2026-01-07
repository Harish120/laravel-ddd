<?php

namespace App\Infrastructure\Ordering\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemModel extends Model
{
    use HasUuids;

    protected $table = 'order_items';

    protected $fillable = [
        'id',
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'unit_price_amount',
        'unit_price_currency',
        'quantity',
        'total_price_amount',
        'total_price_currency',
    ];

    protected $casts = [
        'unit_price_amount' => 'decimal:2',
        'total_price_amount' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
