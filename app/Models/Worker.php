<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Worker extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'worker_id',
        'name',
        'photo',
        'address',
        'nid_no',
        'nid_photo',
        'mobile_1',
        'mobile_2',
        'mobile_3',
        'home_mobile_1',
        'home_mobile_2',
        'home_mobile_3',
        'reference_1',
        'reference_2',
        'category_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the category for this worker
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(WorkerCategory::class);
    }

    /**
     * Get job assignments for this worker
     */
    public function jobAssignments(): HasMany
    {
        return $this->hasMany(JobAssignment::class);
    }

    /**
     * Get payments for this worker
     */
    public function payments(): HasMany
    {
        return $this->hasMany(WorkerPayment::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'worker_id', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

