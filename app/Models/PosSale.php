<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PosSale extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'branch_id',
        'customer_name',
        'customer_mobile',
        'subtotal',
        'discount_amount',
        'vat_amount',
        'total_amount',
        'payment_method',
        'sender_mobile',
        'account_number',
        'card_last_4',
        'seller_id',
        'sale_date',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
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
     * Get sale number (alias for invoice_number)
     */
    public function getSaleNumberAttribute(): string
    {
        return $this->invoice_number;
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['invoice_number', 'total_amount', 'payment_method'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

