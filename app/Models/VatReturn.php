<?php

namespace App\Models;

use App\Casts\SafeEnumCast;
use App\Enums\VatReturnStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class VatReturn extends Model
{
    use LogsActivity;

    protected $fillable = [
        'return_number',
        'return_date',
        'period_from',
        'period_to',
        'tailoring_vat',
        'pos_sale_vat',
        'sherwani_rent_vat',
        'total_output_vat',
        'total_input_vat',
        'vat_payable',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'status' => SafeEnumCast::class . ':' . VatReturnStatus::class,
        'return_date' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        'tailoring_vat' => 'decimal:2',
        'pos_sale_vat' => 'decimal:2',
        'sherwani_rent_vat' => 'decimal:2',
        'total_output_vat' => 'decimal:2',
        'total_input_vat' => 'decimal:2',
        'vat_payable' => 'decimal:2',
    ];

    /**
     * Get the user who created this return
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
            ->logOnly(['return_number', 'vat_payable', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

