<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Company extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'branch_name',
        'address',
        'invoice_name',
        'phone',
        'mobile',
        'website',
        'email',
        'company_registration_no',
        'company_tin_no',
        'e_bin',
        'bin',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get all branches for this company
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'branch_name', 'email', 'phone'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

