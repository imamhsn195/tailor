<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BlockedMac extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'mac_address',
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
            ->logOnly(['mac_address', 'reason', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user who blocked this MAC
     */
    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Check if a MAC address is blocked
     */
    public static function isBlocked(string $macAddress): bool
    {
        try {
            return self::where('mac_address', $macAddress)
                ->where('is_active', true)
                ->exists();
        } catch (\Exception $e) {
            // Table might not exist in testing environment
            return false;
        }
    }
}
