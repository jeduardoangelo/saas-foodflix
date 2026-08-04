<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name_snapshot',
        'unit_price_snapshot',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'unit_price_snapshot' => 'decimal:2',
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::updating(function (OrderItem $item) {
            if ($item->isDirty(['product_name_snapshot', 'unit_price_snapshot'])) {
                throw new LogicException('Os snapshots do produto no item do pedido são imutáveis.');
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
