<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginHistory extends Model
{
    protected $table = 'user_login_history';

    protected $fillable = [
        'user_id',
        'branch_id',
        'ip_address',
        'mac_address',
        'user_agent',
        'login_at',
        'logout_at',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    /**
     * Get the user that owns the login history
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch where user logged in
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo('App\Models\Branch');
    }
}

