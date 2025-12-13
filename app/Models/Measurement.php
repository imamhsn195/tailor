<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'alteration_id',
        'product_id',
        'product_name',
        'measurement_template_id',
        'master_id',
        'measurement_data',
        'design_data',
        'master_notes',
    ];

    protected $casts = [
        'measurement_data' => 'array',
        'design_data' => 'array',
    ];

    /**
     * Get the order this measurement belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item this measurement belongs to
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the alteration this measurement belongs to
     */
    public function alteration(): BelongsTo
    {
        return $this->belongsTo(Alteration::class);
    }

    /**
     * Get the product for this measurement
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the measurement template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(MeasurementTemplate::class, 'measurement_template_id');
    }
}


