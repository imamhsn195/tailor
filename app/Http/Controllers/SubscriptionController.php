<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\PaymentGatewayService;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class SubscriptionController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $paymentService,
        protected TenantProvisioningService $provisioningService
    ) {}

    /**
     * Show subscription plans
     */
    public function index()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('subscriptions.index', compact('plans'));
    }

    /**
     * Show plan details
     */
    public function show(Plan $plan)
    {
        return view('subscriptions.show', compact('plan'));
    }

    /**
     * Create subscription
     */
    public function store(Request $request)
    {
        Log::channel('subscription')->info('=== SUBSCRIPTION REQUEST STARTED ===', [
            'request_data' => $request->except(['_token', 'password']),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'gateway' => 'required|in:stripe,paddle,sslcommerz,aamarpay,shurjopay',
            'tenant_name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain',
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string',
            'customer_address' => 'nullable|string',
        ]);

        Log::channel('subscription')->info('Validation passed', ['validated' => $validated]);

        DB::beginTransaction();
        try {
            // Create tenant
            Log::channel('subscription')->info('Creating tenant...', [
                'tenant_name' => $validated['tenant_name'],
                'domain' => $validated['domain'],
            ]);

            $tenant = Tenant::create([
                'name' => $validated['tenant_name'],
                'domain' => $validated['domain'],
                'database_name' => $this->provisioningService->generateDatabaseName($validated['domain']),
                'status' => 'pending',
            ]);

            Log::channel('subscription')->info('Tenant created', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'domain' => $tenant->domain,
                'status' => $tenant->status,
            ]);

            $plan = Plan::findOrFail($validated['plan_id']);

            Log::channel('subscription')->info('Plan found', [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'stripe_plan_id' => $plan->stripe_plan_id,
                'price_usd' => $plan->price_usd,
                'price_bdt' => $plan->price_bdt,
            ]);

            // Create subscription record first (before payment processing)
            Log::channel('subscription')->info('Creating subscription record...');
            
            $subscription = $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'pending',
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
            ]);

            Log::channel('subscription')->info('Subscription created', [
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
            ]);

            // Process payment
            Log::channel('subscription')->info('Processing payment...', [
                'gateway' => $validated['gateway'],
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
            ]);

            $paymentResult = $this->paymentService->processSubscription(
                $tenant,
                $plan,
                $validated['gateway'],
                [
                    'customer_name' => $validated['name'],
                    'customer_email' => $validated['email'],
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'customer_address' => $validated['customer_address'] ?? null,
                ]
            );

            Log::channel('subscription')->info('Payment processing result', [
                'payment_result' => $paymentResult,
                'has_redirect_url' => isset($paymentResult['redirect_url']),
                'redirect_url' => $paymentResult['redirect_url'] ?? null,
                'has_client_secret' => isset($paymentResult['client_secret']),
                'has_checkout_session_id' => isset($paymentResult['checkout_session_id']),
                'has_subscription_id' => isset($paymentResult['subscription_id']),
            ]);

            if ($paymentResult['status'] === 'error') {
                Log::channel('subscription')->error('Payment processing failed', [
                    'error_message' => $paymentResult['message'] ?? 'Payment processing failed',
                    'payment_result' => $paymentResult,
                ]);
                throw new \Exception($paymentResult['message'] ?? 'Payment processing failed');
            }

            // Store checkout session ID in subscription metadata if available
            if (isset($paymentResult['checkout_session_id'])) {
                Log::channel('subscription')->info('Storing checkout session ID', [
                    'checkout_session_id' => $paymentResult['checkout_session_id'],
                ]);
                $metadata = $subscription->metadata ?? [];
                $metadata['stripe_checkout_session_id'] = $paymentResult['checkout_session_id'];
                $subscription->update(['metadata' => $metadata]);
            }

            // Store Stripe subscription ID if available
            if (isset($paymentResult['subscription_id'])) {
                Log::channel('subscription')->info('Storing Stripe subscription ID', [
                    'stripe_subscription_id' => $paymentResult['subscription_id'],
                ]);
                $subscription->update(['stripe_subscription_id' => $paymentResult['subscription_id']]);
            }

            DB::commit();
            Log::channel('subscription')->info('Database transaction committed');

            // Return redirect URL for payment gateway
            if (isset($paymentResult['redirect_url']) && !empty($paymentResult['redirect_url'])) {
                Log::channel('subscription')->info('Redirecting to payment gateway', [
                    'redirect_url' => $paymentResult['redirect_url'],
                ]);
                return redirect($paymentResult['redirect_url']);
            }

            // For Stripe, return client secret for frontend
            if (isset($paymentResult['client_secret'])) {
                Log::channel('subscription')->info('Returning client secret for frontend', [
                    'has_client_secret' => true,
                ]);
                return response()->json([
                    'client_secret' => $paymentResult['client_secret'],
                    'subscription_id' => $subscription->id,
                ]);
            }

            Log::channel('subscription')->warning('No redirect URL or client secret found, redirecting to success page', [
                'payment_result' => $paymentResult,
            ]);

            return redirect()->route('subscriptions.success', $subscription);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('subscription')->error('Subscription creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle payment success callback
     */
    public function success(Request $request, string $gateway)
    {
        // For Stripe, check for session_id from checkout
        if ($gateway === 'stripe') {
            $sessionId = $request->input('session_id');
            
            Log::channel('subscription')->info('=== PAYMENT SUCCESS CALLBACK (Stripe) ===', [
                'session_id' => $sessionId,
                'gateway' => $gateway,
            ]);
            
            if ($sessionId) {
                // Find subscription by checkout session ID in metadata
                $subscription = null;
                
                // Try to find by checkout session ID in metadata
                $subscriptions = \App\Models\Subscription::whereNotNull('metadata')->get();
                foreach ($subscriptions as $sub) {
                    $metadata = $sub->metadata ?? [];
                    if (isset($metadata['stripe_checkout_session_id']) && $metadata['stripe_checkout_session_id'] === $sessionId) {
                        $subscription = $sub;
                        break;
                    }
                }
                
                // If not found, try by subscription ID
                if (!$subscription) {
                    $subscription = \App\Models\Subscription::where('stripe_subscription_id', $sessionId)
                        ->with(['plan', 'tenant'])
                        ->first();
                }
                
                if ($subscription) {
                    Log::channel('subscription')->info('Subscription found in success callback', [
                        'subscription_id' => $subscription->id,
                        'tenant_id' => $subscription->tenant_id,
                        'current_status' => $subscription->status,
                    ]);
                    
                    // Load tenant relationship
                    $subscription->load('tenant');
                    $tenant = $subscription->tenant;
                    
                    // Activate tenant if still pending (fallback if webhook hasn't processed)
                    if ($tenant && $tenant->status === 'pending') {
                        Log::channel('subscription')->info('Activating tenant from success callback (webhook fallback)', [
                            'tenant_id' => $tenant->id,
                        ]);
                        $tenant->update(['status' => 'active']);
                        
                        // Also update subscription status if still pending
                        if ($subscription->status === 'pending') {
                            $subscription->update(['status' => 'active']);
                        }
                    }
                    
                    return view('subscriptions.success', compact('subscription'));
                } else {
                    Log::channel('subscription')->warning('Subscription not found in success callback', [
                        'session_id' => $sessionId,
                    ]);
                }
            }
        }
        
        $transactionId = $request->input('transaction_id') 
            ?? $request->input('tran_id')
            ?? $request->input('order_id')
            ?? $request->input('session_id');

        if (!$transactionId) {
            return redirect()->route('subscriptions.index')
                ->withErrors(['error' => 'Transaction ID not found']);
        }

        // Verify payment
        $gatewayInstance = $this->paymentService->getGateway($gateway);
        if (!$gatewayInstance) {
            return redirect()->route('subscriptions.index')
                ->withErrors(['error' => 'Payment gateway not found']);
        }

        $verification = $gatewayInstance->verifyPayment($transactionId);

        if ($verification['verified'] ?? false) {
            // Update subscription status
            // This will be handled by webhook, but we can show success page
            $subscription = \App\Models\Subscription::where('stripe_subscription_id', $transactionId)
                ->orWhere('paddle_subscription_id', $transactionId)
                ->with(['plan', 'tenant'])
                ->first();
            
            return view('subscriptions.success', compact('subscription'));
        }

        return redirect()->route('subscriptions.index')
            ->withErrors(['error' => 'Payment verification failed']);
    }

    /**
     * Handle payment failure callback
     */
    public function fail(Request $request, string $gateway)
    {
        return view('subscriptions.fail', [
            'message' => $request->input('error_message', 'Payment failed'),
        ]);
    }
}
