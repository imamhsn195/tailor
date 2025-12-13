<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeasurementTemplate extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'product_type', // shirt, pant, panjabi, etc.
        'fields',
        'is_active',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get measurements using this template
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class, 'measurement_template_id');
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'product_type', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

