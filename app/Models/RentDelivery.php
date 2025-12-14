<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RentDelivery extends Model
{
    use LogsActivity;

    protected $fillable = [
        'rent_order_id',
        'delivery_date',
        'delivery_status', // delivered, partial
        'notes',
        'user_id',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    /**
     * Get the rent order for this delivery
     */
    public function rentOrder(): BelongsTo
    {
        return $this->belongsTo(RentOrder::class);
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
            ->logOnly(['delivery_date', 'delivery_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

