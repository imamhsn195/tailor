<?php

namespace App\Enums;

use App\Enums\Concerns\HasLocalizedLabel;

enum CouponType: string
{
    use HasLocalizedLabel;

    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
}
