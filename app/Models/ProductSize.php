<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSize extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the product this size belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}


