<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductionStep extends Model
{
    use LogsActivity;

    protected $fillable = [
        'factory_production_id',
        'step_name',
        'step_date',
        'quantity',
        'worker_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'step_date' => 'date',
        'quantity' => 'integer',
    ];

    /**
     * Get the factory production
     */
    public function factoryProduction(): BelongsTo
    {
        return $this->belongsTo(FactoryProduction::class);
    }

    /**
     * Get the worker
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Get the user who created this step
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
            ->logOnly(['step_name', 'step_date', 'quantity', 'worker_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

