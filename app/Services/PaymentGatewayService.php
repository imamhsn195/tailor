<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentGateway;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Get payment gateway instance
     *
     * @param string $gatewayName
     * @return PaymentGatewayInterface|null
     */
    public function getGateway(string $gatewayName): ?PaymentGatewayInterface
    {
        $gateway = PaymentGateway::where('name', $gatewayName)
            ->where('is_active', true)
            ->first();

        if (!$gateway) {
            return null;
        }

        $credentials = $gateway->getDecryptedCredentials();
        return $this->createGatewayInstance($gatewayName, $credentials);
    }

    /**
     * Create gateway instance
     *
     * @param string $gatewayName
     * @param array $credentials
     * @return PaymentGatewayInterface
     */
    protected function createGatewayInstance(string $gatewayName, array $credentials = []): PaymentGatewayInterface
    {
        $gatewayClass = match($gatewayName) {
            'stripe' => \App\Services\Gateways\StripePaymentService::class,
            'paddle' => \App\Services\Gateways\PaddlePaymentService::class,
            'sslcommerz' => \App\Services\Gateways\SslcommerzPaymentService::class,
            'aamarpay' => \App\Services\Gateways\AamarpayPaymentService::class,
            'shurjopay' => \App\Services\Gateways\ShurjopayPaymentService::class,
            default => throw new \InvalidArgumentException("Unsupported gateway: {$gatewayName}"),
        };

        return app($gatewayClass, ['credentials' => $credentials]);
    }

    /**
     * Process subscription payment
     *
     * @param Tenant $tenant
     * @param Plan $plan
     * @param string $gatewayName
     * @param array $paymentData
     * @return array
     */
    public function processSubscription(Tenant $tenant, Plan $plan, string $gatewayName, array $paymentData = []): array
    {
        $gateway = $this->getGateway($gatewayName);

        if (!$gateway) {
            throw new \Exception("Payment gateway not found or inactive: {$gatewayName}");
        }

        // Determine currency based on gateway
        $currency = in_array($gatewayName, ['sslcommerz', 'aamarpay', 'shurjopay']) ? 'BDT' : 'USD';
        $amount = $currency === 'BDT' ? $plan->price_bdt : $plan->price_usd;

        $paymentData = array_merge($paymentData, [
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'amount' => $amount,
            'currency' => $currency,
            'billing_cycle' => $plan->billing_cycle,
        ]);

        return $gateway->createPayment($paymentData);
    }

    /**
     * Handle webhook from any gateway
     *
     * @param string $gatewayName
     * @param array $payload
     * @return array
     */
    public function handleWebhook(string $gatewayName, array $payload): array
    {
        $gateway = $this->getGateway($gatewayName);

        if (!$gateway) {
            Log::warning("Webhook received for inactive gateway: {$gatewayName}");
            return ['status' => 'error', 'message' => 'Gateway not found'];
        }

        return $gateway->handleWebhook($payload);
    }
}

