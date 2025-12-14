<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Employee extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'employee_id',
        'department_id',
        'designation_id',
        'name',
        'father_name',
        'mother_name',
        'present_address',
        'permanent_address',
        'date_of_birth',
        'nid_no',
        'nid_photo',
        'photo',
        'signature',
        'gender',
        'type',
        'marital_status',
        'study_history',
        'shift',
        'joining_date',
        'blood_group',
        'religion',
        'salary',
        'leave_days',
        'allowance',
        'loan',
        'commission_rate',
        'sewing_limit_yearly',
        'sewing_price',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'loan' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'sewing_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the designation
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Get attendances for this employee
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get leaves for this employee
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get advances for this employee
     */
    public function advances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    /**
     * Get deductions for this employee
     */
    public function deductions(): HasMany
    {
        return $this->hasMany(EmployeeDeduction::class);
    }

    /**
     * Get salary payments for this employee
     */
    public function salaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'employee_id', 'salary', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

