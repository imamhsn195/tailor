<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SalaryPayment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'payment_number',
        'employee_id',
        'payment_date',
        'period_from',
        'period_to',
        'basic_salary',
        'allowance',
        'commission',
        'overtime',
        'gross_salary',
        'advance_deduction',
        'loan_deduction',
        'leave_deduction',
        'other_deduction',
        'total_deduction',
        'net_salary',
        'payment_method',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        'basic_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'commission' => 'decimal:2',
        'overtime' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'leave_deduction' => 'decimal:2',
        'other_deduction' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    /**
     * Get the employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who created this payment
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
            ->logOnly(['payment_number', 'gross_salary', 'net_salary', 'payment_method'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

