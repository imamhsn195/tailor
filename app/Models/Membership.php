<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Membership extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'type', // general, company
        'discount_percentage',
        'description',
        'is_active',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get customers with this membership
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_memberships')
            ->withPivot('joined_at', 'expires_at')
            ->withTimestamps();
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'type', 'discount_percentage', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

