<?php

namespace App\Enums;

use App\Enums\Concerns\HasLocalizedLabel;

enum GiftVoucherStatus: string
{
    use HasLocalizedLabel;

    case ACTIVE = 'active';
    case USED = 'used';
    case EXPIRED = 'expired';

    public function badgeColor(): string
    {
        return match($this) {
            self::ACTIVE => 'success',
            self::USED => 'info',
            self::EXPIRED => 'danger',
        };
    }
}
