<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PosExchange extends Model
{
    use LogsActivity;

    protected $fillable = [
        'exchange_number',
        'original_sale_id',
        'branch_id',
        'reason',
        'exchange_amount',
        'user_id',
        'exchange_date',
    ];

    protected $casts = [
        'exchange_date' => 'date',
        'exchange_amount' => 'decimal:2',
    ];

    /**
     * Get the original sale
     */
    public function originalSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class, 'original_sale_id');
    }

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who processed this exchange
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
            ->logOnly(['exchange_number', 'exchange_amount', 'reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

