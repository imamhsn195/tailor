<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use Laravel\Cashier\Subscription as CashierSubscription;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

class StripePaymentService implements PaymentGatewayInterface
{
    protected StripeClient $stripe;

    public function __construct(array $credentials = [])
    {
        $apiKey = $credentials['secret_key'] ?? config('services.stripe.secret');
        $this->stripe = new StripeClient($apiKey);
    }

    /**
     * Create a payment/subscription
     */
    public function createPayment(array $data): array
    {
        try {
            $tenant = Tenant::find($data['tenant_id']);
            $plan = Plan::find($data['plan_id']);

            if (!$tenant || !$plan || !$plan->stripe_plan_id) {
                throw new \Exception('Invalid tenant or plan');
            }

            // Create Stripe customer if not exists
            $customer = $this->getOrCreateCustomer($tenant, $data);

            // Create subscription
            $subscription = $this->stripe->subscriptions->create([
                'customer' => $customer->id,
                'items' => [['price' => $plan->stripe_plan_id]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            return [
                'status' => 'success',
                'subscription_id' => $subscription->id,
                'client_secret' => $subscription->latest_invoice->payment_intent->client_secret ?? null,
                'redirect_url' => null,
            ];
        } catch (ApiErrorException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify a payment
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            $subscription = $this->stripe->subscriptions->retrieve($transactionId);
            
            return [
                'status' => 'success',
                'verified' => $subscription->status === 'active',
                'data' => $subscription->toArray(),
            ];
        } catch (ApiErrorException $e) {
            return [
                'status' => 'error',
                'verified' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId): bool
    {
        try {
            $this->stripe->subscriptions->cancel($subscriptionId);
            return true;
        } catch (ApiErrorException $e) {
            Log::error("Stripe subscription cancellation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle webhook
     */
    public function handleWebhook(array $payload): array
    {
        // Laravel Cashier handles Stripe webhooks
        // This is a placeholder for custom handling if needed
        return ['status' => 'processed'];
    }

    /**
     * Get supported payment methods
     */
    public function getSupportedMethods(): array
    {
        return ['card'];
    }

    /**
     * Get or create Stripe customer
     */
    protected function getOrCreateCustomer(Tenant $tenant, array $data)
    {
        // Check if tenant has Stripe customer ID
        $customerId = $tenant->data['stripe_customer_id'] ?? null;

        if ($customerId) {
            try {
                return $this->stripe->customers->retrieve($customerId);
            } catch (ApiErrorException $e) {
                // Customer not found, create new one
            }
        }

        // Create new customer
        $customer = $this->stripe->customers->create([
            'name' => $tenant->name,
            'email' => $data['email'] ?? null,
            'metadata' => [
                'tenant_id' => $tenant->id,
                'tenant_domain' => $tenant->domain,
            ],
        ]);

        // Store customer ID in tenant data
        $tenantData = $tenant->data ?? [];
        $tenantData['stripe_customer_id'] = $customer->id;
        $tenant->update(['data' => $tenantData]);

        return $customer;
    }
}

