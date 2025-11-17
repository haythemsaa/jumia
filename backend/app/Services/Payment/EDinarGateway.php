<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EDinarGateway implements PaymentGatewayInterface
{
    private string $merchantId;
    private string $terminalId;
    private string $merchantPassword;
    private string $apiUrl;

    public function __construct()
    {
        $this->merchantId = config('payment.edinar.merchant_id');
        $this->terminalId = config('payment.edinar.terminal_id');
        $this->merchantPassword = config('payment.edinar.password');
        $this->apiUrl = config('payment.edinar.api_url');
    }

    public function initiatePayment(Order $order): array
    {
        $params = [
            'MerchantId' => $this->merchantId,
            'TerminalId' => $this->terminalId,
            'Amount' => $order->total * 1000, // Convert to millimes
            'OrderId' => $order->order_number,
            'Currency' => '788', // TND ISO code
            'Language' => 'fr',
            'ReturnUrl' => route('payment.return', ['gateway' => 'edinar']),
            'CancelUrl' => route('payment.cancel', ['gateway' => 'edinar']),
            'NotifyUrl' => route('payment.webhook.edinar'),
        ];

        // Generate signature
        $params['Signature'] = $this->generateSignature($params);

        // Create payment transaction record
        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'edinar',
            'amount' => $order->total,
            'currency' => 'TND',
            'status' => 'pending',
        ]);

        return [
            'success' => true,
            'payment_url' => $this->buildPaymentUrl($params),
            'transaction_id' => $transaction->id,
        ];
    }

    public function verifyPayment(array $data): array
    {
        try {
            // Verify signature
            $receivedSignature = $data['Signature'] ?? '';
            $calculatedSignature = $this->generateSignature($data);

            if ($receivedSignature !== $calculatedSignature) {
                throw new \Exception('Invalid signature');
            }

            $status = $data['ResponseCode'] === '00' ? 'completed' : 'failed';

            return [
                'success' => $status === 'completed',
                'transaction_id' => $data['TransactionId'] ?? null,
                'order_number' => $data['OrderId'] ?? null,
                'amount' => ($data['Amount'] ?? 0) / 1000,
                'status' => $status,
                'message' => $data['ResponseMessage'] ?? 'Unknown error',
                'gateway_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('E-Dinar payment verification failed', [
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
        try {
            $response = Http::post($this->apiUrl . '/refund', [
                'MerchantId' => $this->merchantId,
                'TerminalId' => $this->terminalId,
                'TransactionId' => $transactionId,
                'Amount' => $amount * 1000,
                'Signature' => $this->generateRefundSignature($transactionId, $amount),
            ]);

            return $response->successful() && $response->json('ResponseCode') === '00';
        } catch (\Exception $e) {
            Log::error('E-Dinar refund failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            return false;
        }
    }

    public function getPaymentStatus(string $transactionId): string
    {
        try {
            $response = Http::get($this->apiUrl . '/status', [
                'MerchantId' => $this->merchantId,
                'TransactionId' => $transactionId,
            ]);

            if ($response->successful()) {
                $code = $response->json('ResponseCode');
                return match ($code) {
                    '00' => 'completed',
                    '01' => 'pending',
                    default => 'failed',
                };
            }

            return 'failed';
        } catch (\Exception $e) {
            return 'failed';
        }
    }

    private function generateSignature(array $params): string
    {
        // Remove signature if exists
        unset($params['Signature']);

        // Sort parameters alphabetically
        ksort($params);

        // Concatenate values
        $string = implode('', array_values($params));

        // Add merchant password
        $string .= $this->merchantPassword;

        // Generate SHA256 hash
        return hash('sha256', $string);
    }

    private function generateRefundSignature(string $transactionId, float $amount): string
    {
        $string = $this->merchantId . $this->terminalId . $transactionId .
                  ($amount * 1000) . $this->merchantPassword;

        return hash('sha256', $string);
    }

    private function buildPaymentUrl(array $params): string
    {
        return $this->apiUrl . '/payment?' . http_build_query($params);
    }
}
