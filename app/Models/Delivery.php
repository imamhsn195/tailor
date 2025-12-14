<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Delivery extends Model
{
    use LogsActivity;

    protected $fillable = [
        'order_id',
        'delivery_date',
        'delivered_amount',
        'payment_method',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'delivered_amount' => 'decimal:2',
    ];

    /**
     * Get the order this delivery belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who created this delivery
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['delivery_date', 'delivered_amount', 'payment_method'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

