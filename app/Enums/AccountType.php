<?php

namespace App\Enums;

use App\Enums\Concerns\HasLocalizedLabel;

enum AccountType: string
{
    use HasLocalizedLabel;

    case ASSET = 'asset';
    case LIABILITY = 'liability';
    case EQUITY = 'equity';
    case INCOME = 'income';
    case EXPENSE = 'expense';
}
