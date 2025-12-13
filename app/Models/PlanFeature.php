<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlanFeature extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'category',
        'sort_order',
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_feature_plan')
            ->withPivot('value')
            ->withTimestamps();
    }
}
