<?php

namespace App\Enums;

use App\Enums\Concerns\HasLocalizedLabel;

enum VatReturnStatus: string
{
    use HasLocalizedLabel;

    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function badgeColor(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::SUBMITTED => 'info',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
