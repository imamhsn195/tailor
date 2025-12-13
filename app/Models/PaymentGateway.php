<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentGateway extends Model
{
    protected $connection = 'landlord';

    protected $fillable = [
        'name',
        'display_name',
        'type',
        'credentials',
        'supported_methods',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'credentials' => 'array',
        'supported_methods' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get decrypted credentials
     */
    public function getDecryptedCredentials(): array
    {
        if (empty($this->credentials)) {
            return [];
        }

        $decrypted = [];
        foreach ($this->credentials as $key => $value) {
            try {
                $decrypted[$key] = Crypt::decryptString($value);
            } catch (\Exception $e) {
                $decrypted[$key] = $value; // If not encrypted, use as is
            }
        }

        return $decrypted;
    }

    /**
     * Set encrypted credentials
     */
    public function setCredentialsAttribute($value): void
    {
        if (is_array($value)) {
            $encrypted = [];
            foreach ($value as $key => $val) {
                // Don't encrypt already encrypted values or empty values
                if (!empty($val) && !str_starts_with($val, 'eyJ')) {
                    $encrypted[$key] = Crypt::encryptString($val);
                } else {
                    $encrypted[$key] = $val;
                }
            }
            $this->attributes['credentials'] = json_encode($encrypted);
        } else {
            $this->attributes['credentials'] = $value;
        }
    }
}
