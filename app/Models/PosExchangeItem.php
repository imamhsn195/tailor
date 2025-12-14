<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosExchangeItem extends Model
{
    protected $fillable = [
        'pos_exchange_id',
        'original_item_id',
        'product_id',
        'barcode',
        'product_name',
        'size',
        'quantity',
        'refund_amount',
        'charge_amount',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'refund_amount' => 'decimal:2',
        'charge_amount' => 'decimal:2',
    ];

    /**
     * Get the exchange this item belongs to
     */
    public function exchange(): BelongsTo
    {
        return $this->belongsTo(PosExchange::class, 'pos_exchange_id');
    }

    /**
     * Get the original sale item
     */
    public function originalItem(): BelongsTo
    {
        return $this->belongsTo(PosSaleItem::class, 'original_item_id');
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

