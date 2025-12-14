<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Supplier extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'contact_person',
        'mobile',
        'phone',
        'email',
        'address',
        'vat_no',
        'discount_percentage',
        'total_purchase_amount',
        'total_paid_amount',
        'total_due_amount',
        'is_active',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'total_purchase_amount' => 'decimal:2',
        'total_paid_amount' => 'decimal:2',
        'total_due_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get purchases from this supplier
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get payments to this supplier
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    /**
     * Get supplier account ledger (purchases and payments)
     */
    public function getAccountBalance(): float
    {
        return $this->total_due_amount;
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'mobile', 'email', 'is_active', 'total_due_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

