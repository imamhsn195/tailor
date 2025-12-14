<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BlockedIp extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'ip_address',
        'reason',
        'blocked_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['ip_address', 'reason', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user who blocked this IP
     */
    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Check if an IP address is blocked
     */
    public static function isBlocked(string $ipAddress): bool
    {
        try {
            return self::where('ip_address', $ipAddress)
                ->where('is_active', true)
                ->exists();
        } catch (\Exception $e) {
            // Table might not exist in testing environment
            return false;
        }
    }
}
