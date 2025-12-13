<?php

use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin routes (require authentication and tenant context)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'identifytenant', 'ensuretenantactive', 'ensuresubscriptionactive'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Roles & Permissions
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class)->only(['index', 'show']);
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
