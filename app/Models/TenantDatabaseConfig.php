<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class TenantDatabaseConfig extends Model
{
    protected $fillable = [
        'tenant_id',
        'host',
        'port',
        'database',
        'username',
        'password',
        'charset',
        'collation',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get decrypted password
     */
    public function getDecryptedPassword(): string
    {
        return Crypt::decryptString($this->password);
    }

    /**
     * Set encrypted password
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }
}
