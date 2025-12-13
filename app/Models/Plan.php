<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_usd',
        'price_bdt',
        'billing_cycle',
        'trial_days',
        'stripe_plan_id',
        'paddle_plan_id',
        'sslcommerz_plan_id',
        'aamarpay_plan_id',
        'shurjopay_plan_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_usd' => 'decimal:2',
        'price_bdt' => 'decimal:2',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function features()
    {
        return $this->belongsToMany(PlanFeature::class, 'plan_feature_plan')
            ->withPivot('value')
            ->withTimestamps();
    }
}
