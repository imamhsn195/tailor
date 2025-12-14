<?php

use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes (public, no tenant required)
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
});

// Logout (requires authentication)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
});

// Admin routes (require authentication and tenant context)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'identifytenant', 'ensuretenantactive', 'ensuresubscriptionactive'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Roles & Permissions
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class)->only(['index', 'show']);
    
    // Activity Logs
    Route::get('activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/{activityLog}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::delete('activity-logs/clean', [\App\Http\Controllers\Admin\ActivityLogController::class, 'clean'])->name('activity-logs.clean');
    
    // User Management
    Route::post('users/{user}/force-logout', [\App\Http\Controllers\Admin\UserController::class, 'forceLogout'])->name('users.force-logout');
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    
    // Company & Branch Management
    Route::resource('companies', \App\Http\Controllers\Admin\CompanyController::class);
    Route::resource('branches', \App\Http\Controllers\Admin\BranchController::class);
    
    // Product Management
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    Route::resource('product-categories', \App\Http\Controllers\Admin\ProductCategoryController::class);
    Route::resource('product-units', \App\Http\Controllers\Admin\ProductUnitController::class);
    
    // Inventory Management
    Route::get('inventory/stock-in', [\App\Http\Controllers\Admin\InventoryController::class, 'stockIn'])->name('inventory.stock-in');
    Route::post('inventory/stock-in', [\App\Http\Controllers\Admin\InventoryController::class, 'processStockIn'])->name('inventory.stock-in.process');
    Route::get('inventory/stock-out', [\App\Http\Controllers\Admin\InventoryController::class, 'stockOut'])->name('inventory.stock-out');
    Route::post('inventory/stock-out', [\App\Http\Controllers\Admin\InventoryController::class, 'processStockOut'])->name('inventory.stock-out.process');
    Route::resource('inventory', \App\Http\Controllers\Admin\InventoryController::class)->only(['index', 'show']);
    
    // Order Management
    Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class);
    
    // POS System
    Route::get('pos', [\App\Http\Controllers\Admin\PosSaleController::class, 'create'])->name('pos.create');
    Route::post('pos', [\App\Http\Controllers\Admin\PosSaleController::class, 'store'])->name('pos.store');
    Route::resource('pos-sales', \App\Http\Controllers\Admin\PosSaleController::class)->only(['index', 'show']);
    Route::resource('pos-exchanges', \App\Http\Controllers\Admin\PosExchangeController::class);
    Route::resource('pos-cancellations', \App\Http\Controllers\Admin\PosCancellationController::class);
    
    // Factory Management
    Route::resource('worker-categories', \App\Http\Controllers\Admin\WorkerCategoryController::class);
    Route::resource('workers', \App\Http\Controllers\Admin\WorkerController::class);
    
    // HR & Payroll
    Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
    Route::resource('designations', \App\Http\Controllers\Admin\DesignationController::class);
    Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
    Route::resource('attendances', \App\Http\Controllers\Admin\AttendanceController::class);
    Route::resource('leaves', \App\Http\Controllers\Admin\LeaveController::class);
    Route::resource('employee-advances', \App\Http\Controllers\Admin\EmployeeAdvanceController::class);
    Route::resource('employee-deductions', \App\Http\Controllers\Admin\EmployeeDeductionController::class);
    Route::resource('salary-payments', \App\Http\Controllers\Admin\SalaryPaymentController::class);
    
    // CRM
    Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);
    Route::post('customers/{customer}/comments', [\App\Http\Controllers\Admin\CustomerController::class, 'addComment'])->name('customers.comments.store');
    Route::resource('memberships', \App\Http\Controllers\Admin\MembershipController::class);
    Route::resource('discounts', \App\Http\Controllers\Admin\DiscountController::class);
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class);
    Route::resource('gift-vouchers', \App\Http\Controllers\Admin\GiftVoucherController::class);
    
    // Accounting
    Route::resource('chart-of-accounts', \App\Http\Controllers\Admin\ChartOfAccountController::class);
    Route::resource('ledgers', \App\Http\Controllers\Admin\LedgerController::class);
    Route::resource('payment-vouchers', \App\Http\Controllers\Admin\PaymentVoucherController::class);
    Route::resource('vat-returns', \App\Http\Controllers\Admin\VatReturnController::class);
    Route::resource('expenses', \App\Http\Controllers\Admin\ExpenseController::class);
});

// Subscription routes (public)
Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');
    Route::get('/{plan}', [SubscriptionController::class, 'show'])->name('show');
    Route::post('/', [SubscriptionController::class, 'store'])->name('store');
    Route::get('/success/{subscription}', [SubscriptionController::class, 'success'])->name('success');
});

// Payment success/fail callbacks
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/{gateway}/success', [SubscriptionController::class, 'success'])->name('success');
    Route::get('/{gateway}/fail', [SubscriptionController::class, 'fail'])->name('fail');
    Route::get('/{gateway}/cancel', [SubscriptionController::class, 'fail'])->name('cancel');
});

// Webhook routes (no CSRF protection)
Route::prefix('webhook')->name('webhook.')->group(function () {
    Route::post('/stripe', [PaymentWebhookController::class, 'stripe'])->name('stripe');
    Route::post('/paddle', [PaymentWebhookController::class, 'paddle'])->name('paddle');
    Route::post('/sslcommerz', [PaymentWebhookController::class, 'sslcommerz'])->name('sslcommerz');
    Route::post('/aamarpay', [PaymentWebhookController::class, 'aamarpay'])->name('aamarpay');
    Route::post('/shurjopay', [PaymentWebhookController::class, 'shurjopay'])->name('shurjopay');
});
