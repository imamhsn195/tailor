<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'product_id',
        'branch_id',
        'size',
        'quantity',
        'unit_price',
        'supplier_id',
        'purchase_date',
        'barcode',
        'expiry_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'purchase_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Get the product for this inventory
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this inventory
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the supplier for this inventory
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get transactions for this inventory
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['quantity', 'unit_price'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

