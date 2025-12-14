<?php

namespace App\Enums;

use App\Enums\Concerns\HasLocalizedLabel;

enum ExpenseType: string
{
    use HasLocalizedLabel;

    case OPERATIONAL = 'operational';
    case ADMINISTRATIVE = 'administrative';
    case MARKETING = 'marketing';
    case UTILITY = 'utility';
    case RENT = 'rent';
    case SALARY = 'salary';
    case OTHER = 'other';
}
