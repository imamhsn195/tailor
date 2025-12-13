<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosSale extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'sale_number',
        'customer_id',
        'branch_id',
        'seller_id',
        'sale_date',
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'vat_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'payment_reference',
        'status',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    /**
     * Get the customer for this sale
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the branch for this sale
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the seller (user) for this sale
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get items for this sale
     */
    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class, 'pos_sale_id');
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['sale_number', 'total_amount', 'paid_amount', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

