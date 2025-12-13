<?php

namespace App\Enums;

use App\Enums\Concerns\HasLocalizedLabel;

enum UserStatus: string
{
    use HasLocalizedLabel;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case TERMINATED = 'terminated';
}


