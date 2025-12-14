<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Cutting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'cutting_master_id',
        'cutting_date',
        'quantity',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'cutting_date' => 'date',
        'quantity' => 'integer',
    ];

    /**
     * Get the order this cutting belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item this cutting belongs to
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the cutting master
     */
    public function cuttingMaster(): BelongsTo
    {
        return $this->belongsTo(CuttingMaster::class);
    }

    /**
     * Get the user who created this cutting
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
            ->logOnly(['cutting_master_id', 'quantity', 'cutting_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

