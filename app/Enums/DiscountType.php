<?php

namespace App\Enums;

use App\Enums\Concerns\HasLocalizedLabel;

enum DiscountType: string
{
    use HasLocalizedLabel;

    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
}
