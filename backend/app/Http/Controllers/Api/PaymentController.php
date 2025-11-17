<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\NotificationService;
use App\Services\Payment\EDinarGateway;
use App\Services\Payment\KonnectGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Initiate payment for an order
     */
    public function initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'gateway' => 'required|in:edinar,konnect,d17,cash',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if order is pending
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut pas être payée',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $gateway = $this->getGateway($request->gateway);

            if ($request->gateway === 'cash') {
                // Cash on delivery - no payment gateway needed
                $order->update([
                    'payment_method' => 'cash',
                    'payment_status' => 'pending',
                ]);

                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'gateway' => 'cash',
                    'amount' => $order->total,
                    'currency' => 'TND',
                    'status' => 'pending',
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Commande confirmée - Paiement à la livraison',
                    'order' => $order,
                ]);
            }

            // Initialize online payment
            $result = $gateway->initiatePayment($order);

            if (!$result['success']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Échec de l\'initialisation du paiement',
                ], 400);
            }

            $order->update([
                'payment_method' => $request->gateway,
                'payment_status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'payment_url' => $result['payment_url'],
                'payment_ref' => $result['payment_ref'],
                'transaction_id' => $result['transaction_id'],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment initiation failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation du paiement',
            ], 500);
        }
    }

    /**
     * E-Dinar webhook callback
     */
    public function edinarWebhook(Request $request)
    {
        Log::info('E-Dinar webhook received', $request->all());

        try {
            $gateway = new EDinarGateway();

            // Verify signature
            $expectedSignature = $gateway->generateSignature($request->all());
            if ($request->input('Signature') !== $expectedSignature) {
                Log::error('Invalid E-Dinar signature');
                return response('Invalid signature', 403);
            }

            // Verify payment
            $result = $gateway->verifyPayment($request->all());

            if (!$result['success']) {
                Log::error('E-Dinar payment verification failed', $result);
                return response('Verification failed', 400);
            }

            // Find transaction by order ID
            $orderId = $request->input('OrderId');
            $order = Order::where('order_number', $orderId)->firstOrFail();

            $transaction = PaymentTransaction::where('order_id', $order->id)
                ->where('gateway', 'edinar')
                ->firstOrFail();

            DB::beginTransaction();

            // Update transaction
            $transaction->update([
                'transaction_id' => $result['transaction_id'],
                'status' => $result['status'],
                'gateway_response' => $result['gateway_response'],
                'completed_at' => $result['status'] === 'completed' ? now() : null,
            ]);

            // Update order
            if ($result['status'] === 'completed') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                ]);

                // Send confirmation notification
                $this->notificationService->sendPaymentConfirmation(
                    $order->user,
                    $order->order_number,
                    $order->total
                );

                $this->notificationService->sendOrderStatusNotification(
                    $order->user,
                    $order->order_number,
                    'confirmed'
                );
            } elseif ($result['status'] === 'failed') {
                $order->update([
                    'payment_status' => 'failed',
                ]);
            }

            DB::commit();

            return response('OK', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('E-Dinar webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response('Processing failed', 500);
        }
    }

    /**
     * Konnect webhook callback
     */
    public function konnectWebhook(Request $request)
    {
        Log::info('Konnect webhook received', $request->all());

        try {
            $gateway = new KonnectGateway();

            // Verify payment
            $result = $gateway->verifyPayment($request->all());

            if (!$result['success']) {
                Log::error('Konnect payment verification failed', $result);
                return response('Verification failed', 400);
            }

            // Find transaction
            $paymentRef = $request->input('paymentRef');
            $transaction = PaymentTransaction::where('transaction_id', $paymentRef)
                ->where('gateway', 'konnect')
                ->firstOrFail();

            $order = $transaction->order;

            DB::beginTransaction();

            // Update transaction
            $transaction->update([
                'status' => $result['status'],
                'gateway_response' => $result['gateway_response'],
                'completed_at' => $result['status'] === 'completed' ? now() : null,
            ]);

            // Update order
            if ($result['status'] === 'completed') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                ]);

                // Send notifications
                $this->notificationService->sendPaymentConfirmation(
                    $order->user,
                    $order->order_number,
                    $order->total
                );

                $this->notificationService->sendOrderStatusNotification(
                    $order->user,
                    $order->order_number,
                    'confirmed'
                );
            } elseif ($result['status'] === 'failed') {
                $order->update([
                    'payment_status' => 'failed',
                ]);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Konnect webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Payment success return URL
     */
    public function paymentSuccess(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Verify payment status
        $transaction = $order->paymentTransactions()
            ->whereIn('status', ['completed', 'pending'])
            ->latest()
            ->first();

        if ($transaction && $transaction->status === 'pending') {
            // Payment might still be processing, show pending status
            return response()->json([
                'success' => true,
                'status' => 'pending',
                'message' => 'Votre paiement est en cours de traitement',
                'order' => $order,
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => 'completed',
            'message' => 'Paiement effectué avec succès',
            'order' => $order,
        ]);
    }

    /**
     * Payment failure return URL
     */
    public function paymentFailed(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        return response()->json([
            'success' => false,
            'message' => 'Le paiement a échoué',
            'order' => $order,
        ]);
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $transaction = $order->paymentTransactions()->latest()->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune transaction trouvée',
            ], 404);
        }

        // If transaction is still pending, try to verify with gateway
        if ($transaction->status === 'pending') {
            $gateway = $this->getGateway($transaction->gateway);
            $status = $gateway->getPaymentStatus($transaction->transaction_id);

            if ($status !== 'pending') {
                $transaction->update(['status' => $status]);

                if ($status === 'completed') {
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'payment_status' => $transaction->status,
            'order_status' => $order->status,
            'transaction' => $transaction,
        ]);
    }

    /**
     * Get payment history for user
     */
    public function paymentHistory()
    {
        $user = auth()->user();

        $transactions = PaymentTransaction::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with('order')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Get payment gateway instance
     */
    private function getGateway(string $gateway)
    {
        return match ($gateway) {
            'edinar' => new EDinarGateway(),
            'konnect' => new KonnectGateway(),
            default => throw new \Exception("Unsupported gateway: {$gateway}"),
        };
    }
}
