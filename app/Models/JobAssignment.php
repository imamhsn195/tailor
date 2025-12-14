<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class JobAssignment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'worker_id',
        'order_id',
        'order_item_id',
        'alteration_id',
        'assign_date',
        'expected_receive_date',
        'quantity',
        'production_charge',
        'status',
    ];

    protected $casts = [
        'assign_date' => 'date',
        'expected_receive_date' => 'date',
        'quantity' => 'integer',
        'production_charge' => 'decimal:2',
    ];

    /**
     * Get the worker
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Get the order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the alteration
     */
    public function alteration(): BelongsTo
    {
        return $this->belongsTo(Alteration::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['worker_id', 'status', 'quantity', 'production_charge'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

