<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'to',
        'message',
        'gateway',
        'status',
        'response',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'response' => 'array',
    ];
}


