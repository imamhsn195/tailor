<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductUnit extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'abbreviation',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get products using this unit
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'unit_id');
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'abbreviation', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

