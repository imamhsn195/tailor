<?php

namespace App\Models;

use App\Casts\SafeEnumCast;
use App\Enums\GiftVoucherStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GiftVoucher extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'voucher_code',
        'name',
        'amount',
        'customer_id',
        'issued_date',
        'expiry_date',
        'used_date',
        'status', // active, used, expired
        'notes',
    ];

    protected $casts = [
        'status' => SafeEnumCast::class . ':' . GiftVoucherStatus::class,
        'amount' => 'decimal:2',
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'used_date' => 'date',
    ];

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Check if voucher is valid
     */
    public function isValid(): bool
    {
        if ($this->status !== GiftVoucherStatus::ACTIVE) {
            return false;
        }

        if ($this->expiry_date && now()->gt($this->expiry_date)) {
            return false;
        }

        return true;
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['voucher_code', 'amount', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

