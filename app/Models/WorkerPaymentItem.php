<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerPaymentItem extends Model
{
    protected $fillable = [
        'worker_payment_id',
        'job_assignment_id',
        'product_name',
        'quantity',
        'rate',
        'total_amount',
        'assign_date',
        'receive_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'assign_date' => 'date',
        'receive_date' => 'date',
    ];

    /**
     * Get the payment this item belongs to
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(WorkerPayment::class, 'worker_payment_id');
    }

    /**
     * Get the job assignment
     */
    public function jobAssignment(): BelongsTo
    {
        return $this->belongsTo(JobAssignment::class);
    }
}

