<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Create a payment/subscription
     *
     * @param array $data
     * @return array
     */
    public function createPayment(array $data): array;

    /**
     * Verify a payment
     *
     * @param string $transactionId
     * @return array
     */
    public function verifyPayment(string $transactionId): array;

    /**
     * Cancel a subscription
     *
     * @param string $subscriptionId
     * @return bool
     */
    public function cancelSubscription(string $subscriptionId): bool;

    /**
     * Handle webhook/IPN
     *
     * @param array $payload
     * @return array
     */
    public function handleWebhook(array $payload): array;

    /**
     * Get supported payment methods
     *
     * @return array
     */
    public function getSupportedMethods(): array;
}

