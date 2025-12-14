<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'to',
        'subject',
        'view',
        'status',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}


