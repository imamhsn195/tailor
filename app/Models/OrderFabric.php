<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFabric extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'fabric_name',
        'quantity',
        'unit_price',
        'total_price',
        'is_in_house',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_in_house' => 'boolean',
    ];

    /**
     * Get the order this fabric belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product for this fabric
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}


