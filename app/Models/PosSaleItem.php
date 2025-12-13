<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSaleItem extends Model
{
    protected $fillable = [
        'pos_sale_id',
        'product_id',
        'barcode',
        'product_name',
        'size',
        'quantity',
        'unit_price',
        'discount_amount',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the POS sale this item belongs to
     */
    public function posSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    /**
     * Get the product for this item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}


