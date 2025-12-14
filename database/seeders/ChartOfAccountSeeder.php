<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // Assets
            [
                'name' => 'Assets',
                'code' => '1000',
                'type' => 'asset',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Current Assets',
                'code' => '1100',
                'type' => 'asset',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Cash',
                'code' => '1110',
                'type' => 'asset',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Bank Account',
                'code' => '1120',
                'type' => 'asset',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Accounts Receivable',
                'code' => '1130',
                'type' => 'asset',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Inventory',
                'code' => '1140',
                'type' => 'asset',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Fixed Assets',
                'code' => '1200',
                'type' => 'asset',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Equipment',
                'code' => '1210',
                'type' => 'asset',
                'parent_id' => null,
                'is_active' => true,
            ],
            
            // Liabilities
            [
                'name' => 'Liabilities',
                'code' => '2000',
                'type' => 'liability',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Accounts Payable',
                'code' => '2100',
                'type' => 'liability',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Loans',
                'code' => '2200',
                'type' => 'liability',
                'parent_id' => null,
                'is_active' => true,
            ],
            
            // Equity
            [
                'name' => 'Equity',
                'code' => '3000',
                'type' => 'equity',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Capital',
                'code' => '3100',
                'type' => 'equity',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Retained Earnings',
                'code' => '3200',
                'type' => 'equity',
                'parent_id' => null,
                'is_active' => true,
            ],
            
            // Revenue
            [
                'name' => 'Revenue',
                'code' => '4000',
                'type' => 'revenue',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Sales Revenue',
                'code' => '4100',
                'type' => 'revenue',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Service Revenue',
                'code' => '4200',
                'type' => 'revenue',
                'parent_id' => null,
                'is_active' => true,
            ],
            
            // Expenses
            [
                'name' => 'Expenses',
                'code' => '5000',
                'type' => 'expense',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Cost of Goods Sold',
                'code' => '5100',
                'type' => 'expense',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Operating Expenses',
                'code' => '5200',
                'type' => 'expense',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Salary & Wages',
                'code' => '5210',
                'type' => 'expense',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Rent',
                'code' => '5220',
                'type' => 'expense',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Utilities',
                'code' => '5230',
                'type' => 'expense',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'code' => '5240',
                'type' => 'expense',
                'parent_id' => null,
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::updateOrCreate(
                ['code' => $account['code']],
                $account
            );
        }
    }
}
