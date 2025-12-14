<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PosCancellation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'pos_sale_id',
        'reason',
        'user_id',
        'cancellation_date',
    ];

    protected $casts = [
        'cancellation_date' => 'date',
    ];

    /**
     * Get the original sale
     */
    public function originalSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    /**
     * Get the user who processed this cancellation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get cancellation number (derived from pos_sale_id)
     */
    public function getCancellationNumberAttribute(): string
    {
        return 'CAN-' . $this->pos_sale_id . '-' . $this->id;
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['pos_sale_id', 'reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

