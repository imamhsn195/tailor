<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'customer_id',
        'name',
        'mobile',
        'phone',
        'email',
        'address',
        'discount_percentage',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get memberships for this customer
     */
    public function memberships(): BelongsToMany
    {
        return $this->belongsToMany(Membership::class, 'customer_memberships')
            ->withPivot('joined_at', 'expires_at')
            ->withTimestamps();
    }

    /**
     * Get orders for this customer
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get POS sales for this customer
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
            ->logOnly(['name', 'mobile', 'email', 'discount_percentage', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

