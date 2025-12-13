<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alteration extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'alteration_number',
        'order_id',
        'customer_id',
        'branch_id',
        'alteration_date',
        'delivery_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'alteration_date' => 'date',
        'delivery_date' => 'date',
    ];

    /**
     * Get the order this alteration belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the customer for this alteration
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the branch for this alteration
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get measurements for this alteration
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['alteration_number', 'status', 'delivery_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

