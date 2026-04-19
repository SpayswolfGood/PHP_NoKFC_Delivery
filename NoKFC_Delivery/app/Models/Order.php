<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_id',
    'courier_id',
    'status',
    'delivery_address',
    'delivery_time',
    'note',
    'total_amount',
])]
class Order extends Model
{
    public const STATUSES = [
        'new',
        'confirmed',
        'preparing',
        'on_the_way',
        'delivered',
        'cancelled',
    ];

    protected function casts(): array
    {
        return [
            'delivery_time' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
