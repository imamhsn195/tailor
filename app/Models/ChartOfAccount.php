<?php

namespace App\Models;

use App\Casts\SafeEnumCast;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ChartOfAccount extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type', // asset, liability, equity, income, expense
        'parent_id',
        'opening_balance',
        'is_active',
    ];

    protected $casts = [
        'account_type' => SafeEnumCast::class . ':' . AccountType::class,
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the parent account
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Get child accounts
     */
    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Get ledger entries for this account
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(Ledger::class, 'account_id');
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['account_code', 'account_name', 'account_type', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

