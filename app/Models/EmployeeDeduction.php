<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EmployeeDeduction extends Model
{
    use LogsActivity;

    protected $fillable = [
        'deduction_number',
        'employee_id',
        'deduction_date',
        'amount',
        'type',
        'reason',
        'user_id',
    ];

    protected $casts = [
        'deduction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who created this deduction
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
            ->logOnly(['deduction_number', 'amount', 'type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

