<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use LogsActivity;

    protected $fillable = [
        'inventory_id',
        'branch_id',
        'product_id',
        'transaction_type', // in, out, transfer, adjustment
        'quantity',
        'reference_type', // order, sale, purchase, etc.
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    /**
     * Get the inventory for this transaction
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the branch for this transaction
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the product for this transaction
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created this transaction
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['transaction_type', 'quantity', 'reference_type', 'reference_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

