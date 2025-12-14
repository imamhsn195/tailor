<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MaterialIssue extends Model
{
    use LogsActivity;

    protected $fillable = [
        'issue_number',
        'factory_production_id',
        'product_id',
        'branch_id',
        'issue_date',
        'quantity',
        'unit',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    /**
     * Get the factory production
     */
    public function factoryProduction(): BelongsTo
    {
        return $this->belongsTo(FactoryProduction::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created this issue
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['issue_number', 'quantity', 'product_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

