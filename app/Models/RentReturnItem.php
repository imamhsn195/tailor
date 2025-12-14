<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentReturnItem extends Model
{
    protected $fillable = [
        'rent_return_id',
        'rent_order_item_id',
        'product_id',
        'inventory_id',
        'condition', // good, damaged, lost
        'damage_charges',
        'notes',
    ];

    protected $casts = [
        'damage_charges' => 'decimal:2',
    ];

    /**
     * Get the rent return this item belongs to
     */
    public function rentReturn(): BelongsTo
    {
        return $this->belongsTo(RentReturn::class);
    }

    /**
     * Get the rent order item for this return item
     */
    public function rentOrderItem(): BelongsTo
    {
        return $this->belongsTo(RentOrderItem::class);
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

