<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SupplierPayment extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'supplier_id',
        'payment_number',
        'payment_date',
        'amount',
        'payment_method', // cash, cheque, bank_transfer
        'cheque_number',
        'bank_name',
        'account_number',
        'reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the supplier for this payment
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'payment_method', 'payment_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

