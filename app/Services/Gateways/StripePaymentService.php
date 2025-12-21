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
        Log::channel('subscription')->info('=== STRIPE PAYMENT CREATION STARTED ===', [
            'data_keys' => array_keys($data),
            'tenant_id' => $data['tenant_id'] ?? null,
            'plan_id' => $data['plan_id'] ?? null,
        ]);

        try {
            $tenant = Tenant::find($data['tenant_id']);
            $plan = Plan::find($data['plan_id']);

            Log::channel('subscription')->info('Tenant and Plan lookup', [
                'tenant_found' => $tenant !== null,
                'plan_found' => $plan !== null,
                'tenant_id' => $tenant->id ?? null,
                'plan_id' => $plan->id ?? null,
            ]);

            if (!$tenant || !$plan) {
                Log::channel('subscription')->error('Invalid tenant or plan', [
                    'tenant' => $tenant,
                    'plan' => $plan,
                ]);
                throw new \Exception('Invalid tenant or plan');
            }

            // Create Stripe customer if not exists
            Log::channel('subscription')->info('Getting or creating Stripe customer...');
            $customer = $this->getOrCreateCustomer($tenant, $data);
            Log::channel('subscription')->info('Stripe customer ready', [
                'customer_id' => $customer->id,
            ]);

            // Get or create Stripe price
            $stripePriceId = $plan->stripe_plan_id;
            Log::channel('subscription')->info('Checking Stripe price ID', [
                'stripe_plan_id' => $stripePriceId,
                'plan_id' => $plan->id,
            ]);

            if (!$stripePriceId) {
                Log::channel('subscription')->info('Stripe price ID not found, creating dynamically...', [
                    'plan_name' => $plan->name,
                    'currency' => $data['currency'] ?? 'usd',
                ]);
                // Create Stripe price dynamically
                $stripePrice = $this->getOrCreateStripePrice($plan, $data);
                $stripePriceId = $stripePrice->id;
                
                Log::channel('subscription')->info('Stripe price created', [
                    'stripe_price_id' => $stripePriceId,
                    'price_amount' => $stripePrice->unit_amount,
                    'currency' => $stripePrice->currency,
                ]);
                
                // Save the price ID to the plan for future use
                $plan->update(['stripe_plan_id' => $stripePriceId]);
                Log::channel('subscription')->info('Stripe price ID saved to plan');
            } else {
                Log::channel('subscription')->info('Using existing Stripe price ID', [
                    'stripe_price_id' => $stripePriceId,
                ]);
            }

            // Build success and cancel URLs
            $successUrl = url('/payment/stripe/success?session_id={CHECKOUT_SESSION_ID}');
            $cancelUrl = url('/payment/stripe/cancel');

            Log::channel('subscription')->info('Building checkout session URLs', [
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            // Create Stripe Checkout Session for subscription
            Log::channel('subscription')->info('Creating Stripe Checkout Session...', [
                'customer_id' => $customer->id,
                'stripe_price_id' => $stripePriceId,
                'mode' => 'subscription',
            ]);

            $checkoutSession = $this->stripe->checkout->sessions->create([
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $stripePriceId,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'tenant_name' => $tenant->name,
                    'tenant_domain' => $tenant->domain,
                ],
                'subscription_data' => [
                    'metadata' => [
                        'tenant_id' => $tenant->id,
                        'plan_id' => $plan->id,
                    ],
                ],
            ]);

            Log::channel('subscription')->info('Stripe Checkout Session created successfully', [
                'checkout_session_id' => $checkoutSession->id,
                'checkout_session_url' => $checkoutSession->url,
                'subscription_id' => $checkoutSession->subscription ?? null,
                'payment_status' => $checkoutSession->payment_status ?? null,
            ]);

            $result = [
                'status' => 'success',
                'subscription_id' => $checkoutSession->subscription ?? null,
                'checkout_session_id' => $checkoutSession->id,
                'redirect_url' => $checkoutSession->url,
                'client_secret' => null,
            ];

            Log::channel('subscription')->info('=== STRIPE PAYMENT CREATION SUCCESS ===', [
                'result' => $result,
            ]);

            return $result;
        } catch (ApiErrorException $e) {
            Log::channel('subscription')->error('=== STRIPE PAYMENT CREATION FAILED ===', [
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'stripe_error_code' => method_exists($e, 'getStripeCode') ? $e->getStripeCode() : null,
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::channel('subscription')->error('=== STRIPE PAYMENT CREATION FAILED (General Exception) ===', [
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
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
        Log::channel('subscription')->info('=== CHECKOUT SESSION COMPLETED WEBHOOK ===', [
            'session_id' => $session['id'] ?? null,
            'subscription_id' => $session['subscription'] ?? null,
            'payment_status' => $session['payment_status'] ?? null,
            'customer_email' => $session['customer_details']['email'] ?? null,
            'metadata' => $session['metadata'] ?? [],
        ]);

        // Handle Stripe Checkout Session completion
        $subscriptionId = $session['subscription'] ?? null;
        $checkoutSessionId = $session['id'] ?? null;
        
        if ($subscriptionId) {
            Log::channel('subscription')->info('Processing checkout session with subscription ID', [
                'subscription_id' => $subscriptionId,
                'checkout_session_id' => $checkoutSessionId,
            ]);
            try {
                $subscription = $this->stripe->subscriptions->retrieve($subscriptionId);
                
                // Find local subscription by checkout session ID or subscription ID
                $localSubscription = null;
                
                // First, try to find by checkout session ID in metadata
                if ($checkoutSessionId) {
                    Log::channel('subscription')->info('Searching subscription by checkout session ID', [
                        'checkout_session_id' => $checkoutSessionId,
                    ]);
                    
                    // Try to find by JSON metadata
                    $localSubscription = Subscription::whereRaw("JSON_EXTRACT(metadata, '$.stripe_checkout_session_id') = ?", [json_encode($checkoutSessionId)])
                        ->orWhereRaw("JSON_EXTRACT(metadata, '$.stripe_checkout_session_id') = ?", [$checkoutSessionId])
                        ->first();
                    
                    if (!$localSubscription) {
                        // Try finding all subscriptions and check metadata manually
                        $subscriptions = Subscription::whereNotNull('metadata')->get();
                        foreach ($subscriptions as $sub) {
                            $metadata = $sub->metadata ?? [];
                            if (isset($metadata['stripe_checkout_session_id']) && $metadata['stripe_checkout_session_id'] === $checkoutSessionId) {
                                $localSubscription = $sub;
                                break;
                            }
                        }
                    }
                }
                
                // If not found, try by subscription ID
                if (!$localSubscription) {
                    Log::channel('subscription')->info('Searching subscription by Stripe subscription ID', [
                        'stripe_subscription_id' => $subscriptionId,
                    ]);
                    $localSubscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();
                }
                
                // If still not found, try by tenant ID from metadata
                if (!$localSubscription) {
                    $tenantId = $session['metadata']['tenant_id'] ?? null;
                    if ($tenantId) {
                        Log::channel('subscription')->info('Searching subscription by tenant ID from metadata', [
                            'tenant_id' => $tenantId,
                        ]);
                        $localSubscription = Subscription::where('tenant_id', $tenantId)
                            ->whereNull('stripe_subscription_id')
                            ->latest()
                            ->first();
                    }
                }
                
                if ($localSubscription) {
                    Log::channel('subscription')->info('Subscription found, updating...', [
                        'subscription_id' => $localSubscription->id,
                        'tenant_id' => $localSubscription->tenant_id,
                    ]);
                    
                    // Update with subscription ID and status
                    $localSubscription->update([
                        'stripe_subscription_id' => $subscriptionId,
                        'status' => $this->mapStripeStatus($subscription->status),
                        'current_period_start' => isset($subscription->current_period_start) 
                            ? \Carbon\Carbon::createFromTimestamp($subscription->current_period_start) 
                            : null,
                        'current_period_end' => isset($subscription->current_period_end) 
                            ? \Carbon\Carbon::createFromTimestamp($subscription->current_period_end) 
                            : null,
                    ]);
                    
                    Log::channel('subscription')->info('Subscription updated', [
                        'subscription_id' => $localSubscription->id,
                        'new_status' => $localSubscription->status,
                    ]);
                    
                    // Activate tenant if pending
                    $tenant = $localSubscription->tenant;
                    if ($tenant) {
                        Log::channel('subscription')->info('Checking tenant status', [
                            'tenant_id' => $tenant->id,
                            'current_status' => $tenant->status,
                        ]);
                        
                        if ($tenant->status === 'pending') {
                            $tenant->update(['status' => 'active']);
                            Log::channel('subscription')->info('Tenant activated', [
                                'tenant_id' => $tenant->id,
                                'tenant_name' => $tenant->name,
                            ]);
                        } else {
                            Log::channel('subscription')->info('Tenant already active or different status', [
                                'tenant_id' => $tenant->id,
                                'status' => $tenant->status,
                            ]);
                        }
                    } else {
                        Log::channel('subscription')->warning('Tenant not found for subscription', [
                            'subscription_id' => $localSubscription->id,
                            'tenant_id' => $localSubscription->tenant_id,
                        ]);
                    }
                } else {
                    Log::channel('subscription')->error('Subscription not found for checkout session', [
                        'checkout_session_id' => $checkoutSessionId,
                        'stripe_subscription_id' => $subscriptionId,
                        'session_metadata' => $session['metadata'] ?? [],
                    ]);
                }
                
                $result = [
                    'status' => 'success',
                    'subscription_id' => $subscriptionId,
                    'checkout_session_id' => $checkoutSessionId,
                    'message' => 'Checkout session completed and subscription activated',
                ];
                
                Log::channel('subscription')->info('=== CHECKOUT SESSION COMPLETED SUCCESS ===', $result);
                
                return $result;
            } catch (ApiErrorException $e) {
                Log::channel('subscription')->error('Error processing checkout session', [
                    'error' => $e->getMessage(),
                    'stripe_error_code' => method_exists($e, 'getStripeCode') ? $e->getStripeCode() : null,
                    'trace' => $e->getTraceAsString(),
                ]);
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        Log::channel('subscription')->warning('Checkout session completed but no subscription ID found', [
            'session' => $session,
        ]);

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

    /**
     * Get or create Stripe price for a plan
     */
    protected function getOrCreateStripePrice(Plan $plan, array $data): \Stripe\Price
    {
        Log::channel('subscription')->info('Creating Stripe Price for plan', [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'billing_cycle' => $plan->billing_cycle,
        ]);

        // Determine currency and amount (convert to lowercase for Stripe)
        $currency = strtolower($data['currency'] ?? 'usd');
        $amount = ($currency === 'bdt') ? $plan->price_bdt : $plan->price_usd;
        
        Log::channel('subscription')->info('Price calculation', [
            'currency' => $currency,
            'amount' => $amount,
            'price_usd' => $plan->price_usd,
            'price_bdt' => $plan->price_bdt,
        ]);
        
        // Convert to cents
        $amountInCents = (int)round($amount * 100);
        
        Log::channel('subscription')->info('Amount converted to cents', [
            'amount_in_cents' => $amountInCents,
        ]);
        
        // Determine billing interval
        $interval = match(strtolower($plan->billing_cycle)) {
            'monthly' => 'month',
            'yearly', 'annual' => 'year',
            default => 'month',
        };

        Log::channel('subscription')->info('Billing interval determined', [
            'billing_cycle' => $plan->billing_cycle,
            'stripe_interval' => $interval,
        ]);

        // Create Stripe product if needed (using plan name as product name)
        Log::channel('subscription')->info('Creating Stripe Product...');
        $product = $this->stripe->products->create([
            'name' => $plan->name,
            'description' => $plan->description ?? "Subscription plan: {$plan->name}",
            'metadata' => [
                'plan_id' => $plan->id,
            ],
        ]);

        Log::channel('subscription')->info('Stripe Product created', [
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);

        // Create Stripe price (Stripe uses lowercase currency codes)
        Log::channel('subscription')->info('Creating Stripe Price...', [
            'product_id' => $product->id,
            'unit_amount' => $amountInCents,
            'currency' => $currency,
            'interval' => $interval,
        ]);

        $price = $this->stripe->prices->create([
            'product' => $product->id,
            'unit_amount' => $amountInCents,
            'currency' => $currency,
            'recurring' => [
                'interval' => $interval,
            ],
            'metadata' => [
                'plan_id' => $plan->id,
                'billing_cycle' => $plan->billing_cycle,
            ],
        ]);

        Log::channel('subscription')->info('Stripe Price created successfully', [
            'price_id' => $price->id,
            'unit_amount' => $price->unit_amount,
            'currency' => $price->currency,
            'recurring_interval' => $price->recurring->interval ?? null,
        ]);

        return $price;
    }
}

