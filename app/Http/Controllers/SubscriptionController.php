<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\PaymentGatewayService;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        DB::beginTransaction();
        try {
            // Create tenant
            $tenant = Tenant::create([
                'name' => $validated['tenant_name'],
                'domain' => $validated['domain'],
                'database_name' => $this->provisioningService->generateDatabaseName($validated['domain']),
                'status' => 'pending',
            ]);

            $plan = Plan::findOrFail($validated['plan_id']);

            // Process payment
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

            if ($paymentResult['status'] === 'error') {
                throw new \Exception($paymentResult['message'] ?? 'Payment processing failed');
            }

            // Create subscription record
            $subscription = $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'trialing',
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
            ]);

            DB::commit();

            // Return redirect URL for payment gateway
            if (isset($paymentResult['redirect_url'])) {
                return redirect($paymentResult['redirect_url']);
            }

            // For Stripe, return client secret for frontend
            if (isset($paymentResult['client_secret'])) {
                return response()->json([
                    'client_secret' => $paymentResult['client_secret'],
                    'subscription_id' => $subscription->id,
                ]);
            }

            return redirect()->route('subscriptions.success', $subscription);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle payment success callback
     */
    public function success(Request $request, string $gateway)
    {
        $transactionId = $request->input('transaction_id') 
            ?? $request->input('tran_id')
            ?? $request->input('order_id');

        if (!$transactionId) {
            return redirect()->route('subscriptions.index')
                ->withErrors(['error' => 'Transaction ID not found']);
        }

        // Verify payment
        $verification = $this->paymentService->getGateway($gateway)
            ->verifyPayment($transactionId);

        if ($verification['verified'] ?? false) {
            // Update subscription status
            // This will be handled by webhook, but we can show success page
            return view('subscriptions.success');
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
