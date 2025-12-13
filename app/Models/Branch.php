<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Branch extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'branch_id',
        'name',
        'e_bin',
        'bin',
        'address',
        'email',
        'phone',
        'trade_license_no',
        'modules',
        'is_active',
    ];

    protected $casts = [
        'modules' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns this branch
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get users assigned to this branch
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_branches')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    /**
     * Get orders for this branch
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get POS sales for this branch
     */
    public function posSales(): HasMany
    {
        return $this->hasMany(PosSale::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'branch_id', 'email', 'phone', 'is_active', 'modules'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

