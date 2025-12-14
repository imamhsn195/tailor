<?php

namespace App\Enums;

use App\Enums\Concerns\HasLocalizedLabel;

enum MembershipType: string
{
    use HasLocalizedLabel;

    case GENERAL = 'general';
    case COMPANY = 'company';
}
