<?php

namespace App\Services\Payment;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment and return the payment URL or form data
     */
    public function initiatePayment(Order $order): array;

    /**
     * Verify payment callback from gateway
     */
    public function verifyPayment(array $data): array;

    /**
     * Process refund
     */
    public function refund(string $transactionId, float $amount): bool;

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $transactionId): string;
}
