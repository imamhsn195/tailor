<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentOrderItem extends Model
{
    protected $fillable = [
        'rent_order_id',
        'product_id',
        'inventory_id',
        'barcode',
        'product_name',
        'size',
        'rent_price',
        'notes',
    ];

    protected $casts = [
        'rent_price' => 'decimal:2',
    ];

    /**
     * Get the rent order this item belongs to
     */
    public function rentOrder(): BelongsTo
    {
        return $this->belongsTo(RentOrder::class);
    }

    /**
     * Get the product for this item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the inventory for this item
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}

