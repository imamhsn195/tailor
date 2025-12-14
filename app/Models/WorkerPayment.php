<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class WorkerPayment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'payment_number',
        'worker_id',
        'payment_date',
        'period_from',
        'period_to',
        'total_amount',
        'advance_deduction',
        'net_amount',
        'payment_method',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        'total_amount' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    /**
     * Get the worker
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Get the user who created this payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get payment items
     */
    public function items(): HasMany
    {
        return $this->hasMany(WorkerPaymentItem::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['payment_number', 'total_amount', 'net_amount', 'payment_method'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

