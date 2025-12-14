<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RentOrder extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'rent_number',
        'customer_id',
        'branch_id',
        'rent_date',
        'expected_return_date',
        'actual_return_date',
        'rent_amount',
        'security_deposit',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'rent_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
        'rent_amount' => 'decimal:2',
        'security_deposit' => 'decimal:2',
    ];

    /**
     * Get the customer for this rent order
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the branch for this rent order
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created this rent order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get items for this rent order
     */
    public function items(): HasMany
    {
        return $this->hasMany(RentOrderItem::class);
    }

    /**
     * Get deliveries for this rent order
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(RentDelivery::class);
    }

    /**
     * Get returns for this rent order
     */
    public function returns(): HasMany
    {
        return $this->hasMany(RentReturn::class);
    }

    /**
     * Check if rent order is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'active' && 
               $this->expected_return_date < now() && 
               !$this->actual_return_date;
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['rent_number', 'rent_amount', 'security_deposit', 'status', 'expected_return_date', 'actual_return_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

