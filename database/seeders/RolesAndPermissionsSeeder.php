<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder is for tenant databases
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'dashboard.view',
            
            // Company & Branch
            'company.view', 'company.edit',
            'branch.view', 'branch.create', 'branch.edit', 'branch.delete',
            
            // Users & Roles
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',
            'permission.view',
            
            // Customers
            'customer.view', 'customer.create', 'customer.edit', 'customer.delete',
            
            // Products
            'product.view', 'product.create', 'product.edit', 'product.delete',
            'product-category.view', 'product-category.create', 'product-category.edit', 'product-category.delete',
            'product-unit.view', 'product-unit.create', 'product-unit.edit', 'product-unit.delete',
            
            // Inventory
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
            
            // Orders
            'order.view', 'order.create', 'order.edit', 'order.delete',
            
            // Deliveries
            'delivery.view', 'delivery.create', 'delivery.edit', 'delivery.delete',
            
            // POS
            'pos.view', 'pos.create', 'pos.edit', 'pos.delete',
            'pos-sale.view', 'pos-sale.create', 'pos-sale.edit', 'pos-sale.delete',
            'pos-exchange.view', 'pos-exchange.create', 'pos-exchange.edit', 'pos-exchange.delete',
            'pos-cancellation.view', 'pos-cancellation.create', 'pos-cancellation.edit', 'pos-cancellation.delete',
            
            // Factory
            'factory.view', 'factory.create', 'factory.edit', 'factory.delete',
            'worker.view', 'worker.create', 'worker.edit', 'worker.delete',
            'worker-category.view', 'worker-category.create', 'worker-category.edit', 'worker-category.delete',
            
            // HR & Payroll
            'hr.view', 'hr.create', 'hr.edit', 'hr.delete',
            'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
            'department.view', 'department.create', 'department.edit', 'department.delete',
            'designation.view', 'designation.create', 'designation.edit', 'designation.delete',
            'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete',
            'leave.view', 'leave.create', 'leave.edit', 'leave.delete',
            'salary-payment.view', 'salary-payment.create', 'salary-payment.edit', 'salary-payment.delete',
            
            // Accounting
            'accounting.view', 'accounting.create', 'accounting.edit', 'accounting.delete',
            'chart-of-account.view', 'chart-of-account.create', 'chart-of-account.edit', 'chart-of-account.delete',
            'ledger.view', 'ledger.create', 'ledger.edit', 'ledger.delete',
            'expense.view', 'expense.create', 'expense.edit', 'expense.delete',
            'payment-voucher.view', 'payment-voucher.create', 'payment-voucher.edit', 'payment-voucher.delete',
            'vat-return.view', 'vat-return.create', 'vat-return.edit', 'vat-return.delete',
            
            // Purchase & Supplier
            'supplier.view', 'supplier.create', 'supplier.edit', 'supplier.delete',
            'purchase.view', 'purchase.create', 'purchase.edit', 'purchase.delete',
            'supplier-payment.view', 'supplier-payment.create', 'supplier-payment.edit', 'supplier-payment.delete',
            
            // Rent Management
            'rent-order.view', 'rent-order.create', 'rent-order.edit', 'rent-order.delete',
            'rent-delivery.view', 'rent-delivery.create', 'rent-delivery.edit', 'rent-delivery.delete',
            'rent-return.view', 'rent-return.create', 'rent-return.edit', 'rent-return.delete',
            
            // CRM
            'membership.view', 'membership.create', 'membership.edit', 'membership.delete',
            'discount.view', 'discount.create', 'discount.edit', 'discount.delete',
            'coupon.view', 'coupon.create', 'coupon.edit', 'coupon.delete',
            'gift-voucher.view', 'gift-voucher.create', 'gift-voucher.edit', 'gift-voucher.delete',
            
            // Reports
            'report.view', 'report.export',
            
            // Settings
            'settings.view', 'settings.edit',
            
            // System Logs
            'system-log.view', 'system-log.delete',
            'activity-log.view', 'activity-log.delete',
            
            // Blocked IPs/MACs
            'blocked-ip.view', 'blocked-ip.create', 'blocked-ip.edit', 'blocked-ip.delete',
            'blocked-mac.view', 'blocked-mac.create', 'blocked-mac.edit', 'blocked-mac.delete',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // Create Super Admin role with all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
        
        // Create Admin role with most permissions (except user/role management)
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminPermissions = Permission::whereNotIn('name', [
            'user.create', 'user.edit', 'user.delete',
            'role.view', 'role.create', 'role.edit', 'role.delete',
        ])->get();
        $admin->syncPermissions($adminPermissions);
        
        // Create Manager role
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $managerPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'customer.view', 'customer.create', 'customer.edit',
            'product.view', 'product.create', 'product.edit',
            'inventory.view', 'inventory.create', 'inventory.edit',
            'order.view', 'order.create', 'order.edit',
            'pos.view', 'pos.create', 'pos.edit',
            'report.view', 'report.export',
            'supplier.view', 'supplier.create', 'supplier.edit',
            'purchase.view', 'purchase.create', 'purchase.edit',
        ])->get();
        $manager->syncPermissions($managerPermissions);
        
        // Create Cashier role
        $cashier = Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web']);
        $cashierPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'customer.view', 'customer.create',
            'pos.view', 'pos.create',
            'pos-sale.view', 'pos-sale.create',
            'report.view',
        ])->get();
        $cashier->syncPermissions($cashierPermissions);
        
        // Create Tailor/Worker role
        $tailor = Role::firstOrCreate(['name' => 'Tailor', 'guard_name' => 'web']);
        $tailorPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'order.view', 'order.edit',
            'factory.view',
            'worker.view',
        ])->get();
        $tailor->syncPermissions($tailorPermissions);
        
        // Create Accountant role
        $accountant = Role::firstOrCreate(['name' => 'Accountant', 'guard_name' => 'web']);
        $accountantPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'accounting.view', 'accounting.create', 'accounting.edit',
            'chart-of-account.view', 'chart-of-account.create', 'chart-of-account.edit',
            'ledger.view', 'ledger.create', 'ledger.edit',
            'expense.view', 'expense.create', 'expense.edit',
            'payment-voucher.view', 'payment-voucher.create', 'payment-voucher.edit',
            'vat-return.view', 'vat-return.create', 'vat-return.edit',
            'report.view', 'report.export',
        ])->get();
        $accountant->syncPermissions($accountantPermissions);
    }
}
