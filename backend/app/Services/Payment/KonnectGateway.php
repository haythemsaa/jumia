<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KonnectGateway implements PaymentGatewayInterface
{
    private string $apiKey;
    private string $walletId;
    private string $apiUrl = 'https://api.konnect.network/api/v2';

    public function __construct()
    {
        $this->apiKey = config('payment.konnect.api_key');
        $this->walletId = config('payment.konnect.wallet_id');
    }

    public function initiatePayment(Order $order): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->post($this->apiUrl . '/payments/init-payment', [
                'receiverWalletId' => $this->walletId,
                'token' => 'TND',
                'amount' => $order->total * 1000, // Millimes
                'type' => 'immediate',
                'description' => 'Commande ' . $order->order_number,
                'orderId' => $order->order_number,
                'webhook' => route('payment.webhook.konnect'),
                'silentWebhook' => true,
                'successUrl' => route('payment.success', ['order' => $order->id]),
                'failUrl' => route('payment.fail', ['order' => $order->id]),
                'theme' => 'light',
            ]);

            if (!$response->successful()) {
                throw new \Exception('Konnect API error: ' . $response->body());
            }

            $data = $response->json();

            // Create transaction record
            $transaction = PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => 'konnect',
                'transaction_id' => $data['paymentRef'] ?? null,
                'amount' => $order->total,
                'currency' => 'TND',
                'status' => 'pending',
                'gateway_response' => $data,
            ]);

            return [
                'success' => true,
                'payment_url' => $data['payUrl'] ?? null,
                'payment_ref' => $data['paymentRef'] ?? null,
                'transaction_id' => $transaction->id,
            ];
        } catch (\Exception $e) {
            Log::error('Konnect payment initialization failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(array $data): array
    {
        try {
            $paymentRef = $data['payment_ref'] ?? $data['paymentRef'] ?? null;

            if (!$paymentRef) {
                throw new \Exception('Missing payment reference');
            }

            // Get payment details from Konnect
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get($this->apiUrl . '/payments/' . $paymentRef);

            if (!$response->successful()) {
                throw new \Exception('Failed to verify payment');
            }

            $paymentData = $response->json();

            $status = match ($paymentData['payment']['status'] ?? '') {
                'completed' => 'completed',
                'pending' => 'pending',
                default => 'failed',
            };

            return [
                'success' => $status === 'completed',
                'transaction_id' => $paymentRef,
                'amount' => ($paymentData['payment']['amount'] ?? 0) / 1000,
                'status' => $status,
                'gateway_response' => $paymentData,
            ];
        } catch (\Exception $e) {
            Log::error('Konnect payment verification failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function refund(string $transactionId, float $amount): bool
    {
        // Konnect refunds are handled through dashboard
        Log::info('Konnect refund requested (manual process)', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        return false; // Must be done manually
    }

    public function getPaymentStatus(string $transactionId): string
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get($this->apiUrl . '/payments/' . $transactionId);

            if ($response->successful()) {
                $status = $response->json('payment.status');
                return match ($status) {
                    'completed' => 'completed',
                    'pending' => 'pending',
                    default => 'failed',
                };
            }

            return 'failed';
        } catch (\Exception $e) {
            return 'failed';
        }
    }
}
