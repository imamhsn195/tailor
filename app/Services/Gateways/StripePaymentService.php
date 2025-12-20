<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
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
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                ],
            ]);

            // Store subscription ID in local subscription record if it exists
            $localSubscription = Subscription::where('tenant_id', $tenant->id)
                ->where('plan_id', $plan->id)
                ->latest()
                ->first();
            
            if ($localSubscription) {
                $localSubscription->update(['stripe_subscription_id' => $subscription->id]);
            }

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
        try {
            $eventType = $payload['type'] ?? null;
            $eventData = $payload['data']['object'] ?? [];

            if (!$eventType) {
                return ['status' => 'error', 'message' => 'Invalid webhook payload'];
            }

            Log::info("Stripe webhook received: {$eventType}", ['event_id' => $payload['id'] ?? null]);

            switch ($eventType) {
                case 'invoice.payment_succeeded':
                    return $this->handleInvoicePaymentSucceeded($eventData);
                
                case 'invoice.payment_failed':
                    return $this->handleInvoicePaymentFailed($eventData);
                
                case 'customer.subscription.created':
                    return $this->handleSubscriptionCreated($eventData);
                
                case 'customer.subscription.updated':
                    return $this->handleSubscriptionUpdated($eventData);
                
                case 'customer.subscription.deleted':
                    return $this->handleSubscriptionDeleted($eventData);
                
                case 'checkout.session.completed':
                    return $this->handleCheckoutSessionCompleted($eventData);
                
                default:
                    Log::info("Unhandled Stripe webhook event: {$eventType}");
                    return ['status' => 'processed', 'message' => 'Event not handled'];
            }
        } catch (\Exception $e) {
            Log::error("Stripe webhook processing error: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        try {
            Webhook::constructEvent($payload, $signature, $secret);
            return true;
        } catch (SignatureVerificationException $e) {
            Log::error("Stripe webhook signature verification failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle invoice.payment_succeeded event
     */
    protected function handleInvoicePaymentSucceeded(array $invoice): array
    {
        $subscriptionId = $invoice['subscription'] ?? null;
        
        if (!$subscriptionId) {
            return ['status' => 'error', 'message' => 'No subscription ID in invoice'];
        }

        try {
            $subscription = $this->stripe->subscriptions->retrieve($subscriptionId);
            $localSubscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();

            if ($localSubscription) {
                $localSubscription->update([
                    'status' => $this->mapStripeStatus($subscription->status),
                    'current_period_start' => \Carbon\Carbon::createFromTimestamp($subscription->current_period_start),
                    'current_period_end' => \Carbon\Carbon::createFromTimestamp($subscription->current_period_end),
                ]);

                // Provision tenant if pending
                $tenant = $localSubscription->tenant;
                if ($tenant && $tenant->status === 'pending') {
                    $tenant->update(['status' => 'active']);
                }
            }

            return [
                'status' => 'success',
                'transaction_id' => $invoice['id'],
                'subscription_id' => $subscriptionId,
                'amount' => $invoice['amount_paid'] / 100, // Convert from cents
                'currency' => strtoupper($invoice['currency'] ?? 'usd'),
                'data' => [
                    'tenant_id' => $localSubscription->tenant_id ?? null,
                    'invoice_id' => $invoice['id'],
                ],
            ];
        } catch (ApiErrorException $e) {
            Log::error("Error processing invoice.payment_succeeded: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle invoice.payment_failed event
     */
    protected function handleInvoicePaymentFailed(array $invoice): array
    {
        $subscriptionId = $invoice['subscription'] ?? null;
        
        if ($subscriptionId) {
            $localSubscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();
            if ($localSubscription) {
                $localSubscription->update(['status' => 'past_due']);
            }
        }

        return ['status' => 'success', 'message' => 'Payment failed handled'];
    }

    /**
     * Handle customer.subscription.created event
     */
    protected function handleSubscriptionCreated(array $subscription): array
    {
        $subscriptionId = $subscription['id'] ?? null;
        $tenantId = $subscription['metadata']['tenant_id'] ?? null;

        if ($subscriptionId && $tenantId) {
            $localSubscription = Subscription::where('tenant_id', $tenantId)
                ->whereNull('stripe_subscription_id')
                ->latest()
                ->first();

            if ($localSubscription) {
                $localSubscription->update([
                    'stripe_subscription_id' => $subscriptionId,
                    'status' => $this->mapStripeStatus($subscription['status'] ?? 'incomplete'),
                ]);
            }
        }

        return ['status' => 'success', 'message' => 'Subscription created handled'];
    }

    /**
     * Handle customer.subscription.updated event
     */
    protected function handleSubscriptionUpdated(array $subscription): array
    {
        $subscriptionId = $subscription['id'] ?? null;
        
        if ($subscriptionId) {
            $localSubscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();
            
            if ($localSubscription) {
                $localSubscription->update([
                    'status' => $this->mapStripeStatus($subscription['status'] ?? 'active'),
                    'current_period_start' => isset($subscription['current_period_start']) 
                        ? \Carbon\Carbon::createFromTimestamp($subscription['current_period_start']) 
                        : null,
                    'current_period_end' => isset($subscription['current_period_end']) 
                        ? \Carbon\Carbon::createFromTimestamp($subscription['current_period_end']) 
                        : null,
                    'cancelled_at' => isset($subscription['canceled_at']) 
                        ? \Carbon\Carbon::createFromTimestamp($subscription['canceled_at']) 
                        : null,
                ]);
            }
        }

        return ['status' => 'success', 'message' => 'Subscription updated handled'];
    }

    /**
     * Handle customer.subscription.deleted event
     */
    protected function handleSubscriptionDeleted(array $subscription): array
    {
        $subscriptionId = $subscription['id'] ?? null;
        
        if ($subscriptionId) {
            $localSubscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();
            
            if ($localSubscription) {
                $localSubscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
            }
        }

        return ['status' => 'success', 'message' => 'Subscription deleted handled'];
    }

    /**
     * Handle checkout.session.completed event
     */
    protected function handleCheckoutSessionCompleted(array $session): array
    {
        // Handle if using Stripe Checkout instead of Elements
        $subscriptionId = $session['subscription'] ?? null;
        
        if ($subscriptionId) {
            try {
                $subscription = $this->stripe->subscriptions->retrieve($subscriptionId);
                return $this->handleSubscriptionCreated($subscription->toArray());
            } catch (ApiErrorException $e) {
                Log::error("Error retrieving subscription from checkout session: " . $e->getMessage());
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return ['status' => 'success', 'message' => 'Checkout session completed handled'];
    }

    /**
     * Map Stripe subscription status to local status
     */
    protected function mapStripeStatus(string $stripeStatus): string
    {
        return match($stripeStatus) {
            'active' => 'active',
            'trialing' => 'trialing',
            'past_due' => 'past_due',
            'canceled', 'cancelled' => 'cancelled',
            'unpaid' => 'unpaid',
            'incomplete', 'incomplete_expired' => 'incomplete',
            default => 'pending',
        };
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
    protected function getOrCreateCustomer(Tenant $tenant, array $data): \Stripe\Customer
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

