<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\PaymentTransaction;
use App\Services\PaymentGatewayService;
use App\Services\TenantProvisioningService;
use App\Jobs\ProvisionTenantDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $paymentService,
        protected TenantProvisioningService $provisioningService
    ) {}

    /**
     * Handle Stripe webhook
     */
    public function stripe(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        Log::channel('subscription')->info('=== STRIPE WEBHOOK RECEIVED ===', [
            'has_signature' => !empty($signature),
            'has_webhook_secret' => !empty($webhookSecret),
            'payload_length' => strlen($payload),
        ]);

        // Verify webhook signature if secret is configured
        if ($webhookSecret) {
            $gateway = $this->paymentService->getGateway('stripe');
            if ($gateway && method_exists($gateway, 'verifyWebhookSignature')) {
                if (!$gateway->verifyWebhookSignature($payload, $signature, $webhookSecret)) {
                    Log::channel('subscription')->warning('Stripe webhook signature verification failed');
                    return response()->json(['error' => 'Invalid signature'], 400);
                }
                Log::channel('subscription')->info('Stripe webhook signature verified');
            }
        } else {
            Log::channel('subscription')->warning('Stripe webhook secret not configured, skipping signature verification');
        }

        $payloadArray = json_decode($payload, true);
        
        Log::channel('subscription')->info('Processing Stripe webhook', [
            'event_type' => $payloadArray['type'] ?? 'unknown',
            'event_id' => $payloadArray['id'] ?? null,
        ]);
        
        return $this->handleWebhook('stripe', $payloadArray);
    }

    /**
     * Handle Paddle webhook
     */
    public function paddle(Request $request)
    {
        $payload = $request->all();
        return $this->handleWebhook('paddle', $payload);
    }

    /**
     * Handle SSLCOMMERZ IPN
     */
    public function sslcommerz(Request $request)
    {
        $payload = $request->all();
        return $this->handleWebhook('sslcommerz', $payload);
    }

    /**
     * Handle AamarPay callback
     */
    public function aamarpay(Request $request)
    {
        $payload = $request->all();
        return $this->handleWebhook('aamarpay', $payload);
    }

    /**
     * Handle ShurjoPay webhook
     */
    public function shurjopay(Request $request)
    {
        $payload = $request->all();
        return $this->handleWebhook('shurjopay', $payload);
    }

    /**
     * Generic webhook handler
     */
    protected function handleWebhook(string $gateway, array $payload): \Illuminate\Http\JsonResponse
    {
        try {
            Log::channel('subscription')->info('Handling webhook', [
                'gateway' => $gateway,
                'event_type' => $payload['type'] ?? 'unknown',
            ]);

            $result = $this->paymentService->handleWebhook($gateway, $payload);

            Log::channel('subscription')->info('Webhook processed by gateway', [
                'gateway' => $gateway,
                'result_status' => $result['status'] ?? 'unknown',
            ]);

            if ($result['status'] === 'success') {
                Log::channel('subscription')->info('Processing successful payment from webhook');
                $this->processSuccessfulPayment($gateway, $result);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::channel('subscription')->error("Webhook processing error ({$gateway})", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Process successful payment
     */
    protected function processSuccessfulPayment(string $gateway, array $result): void
    {
        DB::beginTransaction();
        try {
            $transactionId = $result['transaction_id'] ?? null;
            $amount = $result['amount'] ?? 0;
            $currency = $result['currency'] ?? 'BDT';

            // Find or create payment transaction
            $transaction = PaymentTransaction::firstOrCreate(
                [
                    'gateway' => $gateway,
                    'gateway_transaction_id' => $transactionId,
                ],
                [
                    'transaction_id' => 'TXN' . time() . rand(1000, 9999),
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => 'completed',
                    'paid_at' => now(),
                    'gateway_response' => $result['data'] ?? [],
                ]
            );

            // Update transaction if it was pending
            if ($transaction->status === 'pending') {
                $transaction->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'gateway_response' => $result['data'] ?? [],
                ]);
            }

            // Find subscription by transaction or tenant
            $subscription = $transaction->subscription;
            
            if (!$subscription && isset($result['data']['tenant_id'])) {
                $tenant = Tenant::find($result['data']['tenant_id']);
                $subscription = $tenant?->subscriptions()->latest()->first();
            }

            if ($subscription) {
                // Calculate period end based on billing cycle
                $plan = $subscription->plan;
                $periodEnd = now();
                
                if ($plan) {
                    if ($plan->billing_cycle === 'yearly') {
                        $periodEnd = now()->addYear();
                    } else {
                        $periodEnd = now()->addMonth();
                    }
                } else {
                    $periodEnd = now()->addMonth(); // Default to monthly
                }

                // Update subscription status
                $subscription->update([
                    'status' => 'active',
                    'current_period_start' => now(),
                    'current_period_end' => $periodEnd,
                ]);

                // Link transaction to subscription
                $transaction->update(['subscription_id' => $subscription->id]);

                // Provision tenant if not already provisioned
                $tenant = $subscription->tenant;
                if ($tenant && $tenant->status === 'pending') {
                    // Dispatch provisioning job
                    ProvisionTenantDatabase::dispatch($tenant, [
                        'email' => $result['data']['customer_email'] ?? 'admin@' . $tenant->domain,
                        'name' => $result['data']['customer_name'] ?? 'Admin',
                        'send_email' => true,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment processing error: " . $e->getMessage());
            throw $e;
        }
    }
}
