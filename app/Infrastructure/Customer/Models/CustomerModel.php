<?php

namespace App\Infrastructure\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CustomerModel extends Model
{
    use HasUuids;

    protected $table = 'customers';

    protected $fillable = [
        'id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'billing_address',
        'shipping_address',
        'is_active',
    ];

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'is_active' => 'boolean',
    ];
}

