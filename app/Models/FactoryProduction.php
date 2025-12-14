<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FactoryProduction extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'batch_number',
        'product_id',
        'order_id',
        'production_date',
        'quantity',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'production_date' => 'date',
        'quantity' => 'integer',
    ];

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who created this production
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get production steps
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ProductionStep::class);
    }

    /**
     * Get material issues
     */
    public function materialIssues(): HasMany
    {
        return $this->hasMany(MaterialIssue::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['batch_number', 'status', 'quantity'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

