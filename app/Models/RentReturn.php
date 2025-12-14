<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RentReturn extends Model
{
    use LogsActivity;

    protected $fillable = [
        'rent_order_id',
        'return_date',
        'return_status', // complete, partial, damaged
        'damage_charges',
        'late_fees',
        'refund_amount',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'return_date' => 'date',
        'damage_charges' => 'decimal:2',
        'late_fees' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    /**
     * Get the rent order for this return
     */
    public function rentOrder(): BelongsTo
    {
        return $this->belongsTo(RentOrder::class);
    }

    /**
     * Get the user who created this return
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get items for this return
     */
    public function items(): HasMany
    {
        return $this->hasMany(RentReturnItem::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['return_date', 'return_status', 'damage_charges', 'late_fees', 'refund_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

