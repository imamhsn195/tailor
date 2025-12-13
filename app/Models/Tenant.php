<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as BaseTenant;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Tenant extends BaseTenant
{
    protected $connection = 'landlord';

    protected $fillable = [
        'name',
        'domain',
        'database_name',
        'status',
        'trial_ends_at',
        'data',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * Get the database configuration for this tenant
     */
    public function databaseConfig(): HasOne
    {
        return $this->hasOne(TenantDatabaseConfig::class);
    }

    /**
     * Get all domains for this tenant
     */
    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    /**
     * Get the primary domain
     */
    public function primaryDomain()
    {
        return $this->domains()->where('is_primary', true)->first();
    }

    /**
     * Get active subscription
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latest();
    }

    /**
     * Check if tenant is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the database name for this tenant
     * Spatie Multitenancy uses this method for database switching
     */
    public function getDatabaseName(): string
    {
        return $this->database_name;
    }
}
