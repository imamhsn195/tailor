<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'order_number',
        'customer_id',
        'branch_id',
        'order_date',
        'trial_date',
        'delivery_date',
        'design_charge',
        'embroidery_charge',
        'fabrics_amount',
        'tailor_amount',
        'total_amount',
        'discount_amount',
        'net_payable',
        'paid_amount',
        'due_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'trial_date' => 'date',
        'delivery_date' => 'date',
        'design_charge' => 'decimal:2',
        'embroidery_charge' => 'decimal:2',
        'fabrics_amount' => 'decimal:2',
        'tailor_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    /**
     * Get the customer for this order
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the branch for this order
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get items for this order
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get fabrics for this order
     */
    public function fabrics(): HasMany
    {
        return $this->hasMany(OrderFabric::class);
    }

    /**
     * Get measurements for this order
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
            ->logOnly(['order_number', 'status', 'net_payable', 'paid_amount', 'due_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

