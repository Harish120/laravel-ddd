<?php

namespace App\Infrastructure\Ordering\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderModel extends Model
{
    use HasUuids;

    protected $table = 'orders';

    protected $fillable = [
        'id',
        'customer_id',
        'order_number',
        'status',
        'subtotal_amount',
        'subtotal_currency',
        'shipping_cost_amount',
        'shipping_cost_currency',
        'tax_amount',
        'tax_currency',
        'total_amount',
        'total_currency',
        'shipping_address',
        'billing_address',
        'notes',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'shipping_cost_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }
}
