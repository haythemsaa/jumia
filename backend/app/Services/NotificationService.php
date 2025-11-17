<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    private ?string $firebaseServerKey;
    private string $firebaseUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->firebaseServerKey = config('services.firebase.server_key');
    }

    /**
     * Send notification to a user via all channels
     */
    public function sendToUser(User $user, string $title, string $body, array $data = [], array $channels = ['database', 'push']): bool
    {
        try {
            // Save to database
            if (in_array('database', $channels)) {
                $this->saveToDatabase($user, $title, $body, $data);
            }

            // Send push notification
            if (in_array('push', $channels)) {
                $this->sendPushNotification($user, $title, $body, $data);
            }

            // Send email if specified
            if (in_array('email', $channels)) {
                $this->sendEmail($user, $title, $body, $data);
            }

            // Send SMS if specified
            if (in_array('sms', $channels)) {
                $this->sendSMS($user, $body);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Save notification to database
     */
    public function saveToDatabase(User $user, string $title, string $body, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $data['type'] ?? 'system',
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }

    /**
     * Send push notification via Firebase FCM
     */
    public function sendPushNotification(User $user, string $title, string $body, array $data = []): bool
    {
        if (!$this->firebaseServerKey) {
            Log::warning('Firebase server key not configured');
            return false;
        }

        // Get user's device tokens
        $tokens = DeviceToken::where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            Log::info('No device tokens found for user', ['user_id' => $user->id]);
            return false;
        }

        // Send to multiple devices
        $payload = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'badge' => $this->getUnreadCount($user),
            ],
            'data' => $data,
            'priority' => 'high',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->firebaseServerKey,
                'Content-Type' => 'application/json',
            ])->post($this->firebaseUrl, $payload);

            if ($response->successful()) {
                $result = $response->json();

                // Handle failed tokens
                if (isset($result['results'])) {
                    $this->handleFailedTokens($tokens, $result['results']);
                }

                return true;
            }

            Log::error('FCM request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('FCM push notification failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return false;
        }
    }

    /**
     * Send email notification
     */
    public function sendEmail(User $user, string $title, string $body, array $data = []): bool
    {
        try {
            Mail::send('emails.notification', [
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'user' => $user,
            ], function ($message) use ($user, $title) {
                $message->to($user->email)
                    ->subject($title);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Email notification failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return false;
        }
    }

    /**
     * Send SMS notification (for Tunisian providers)
     */
    public function sendSMS(User $user, string $message): bool
    {
        if (!$user->phone) {
            return false;
        }

        // Using Tunisian SMS provider (example with generic API)
        $smsProvider = config('services.sms.provider'); // 'tunisiesms', 'smsapi', etc.

        try {
            switch ($smsProvider) {
                case 'tunisiesms':
                    return $this->sendViaTunisieSMS($user->phone, $message);
                case 'smsapi':
                    return $this->sendViaSMSAPI($user->phone, $message);
                default:
                    Log::warning('SMS provider not configured');
                    return false;
            }
        } catch (\Exception $e) {
            Log::error('SMS notification failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return false;
        }
    }

    /**
     * Send SMS via Tunisie SMS provider
     */
    private function sendViaTunisieSMS(string $phone, string $message): bool
    {
        $apiKey = config('services.sms.tunisiesms.api_key');
        $senderId = config('services.sms.tunisiesms.sender_id');

        if (!$apiKey) {
            return false;
        }

        $response = Http::post('https://api.tunisiesms.tn/api/send', [
            'api_key' => $apiKey,
            'sender_id' => $senderId,
            'to' => $phone,
            'message' => $message,
        ]);

        return $response->successful();
    }

    /**
     * Send SMS via generic SMS API provider
     */
    private function sendViaSMSAPI(string $phone, string $message): bool
    {
        $apiKey = config('services.sms.smsapi.api_key');

        if (!$apiKey) {
            return false;
        }

        $response = Http::post('https://api.smsapi.com/send', [
            'api_key' => $apiKey,
            'to' => $phone,
            'message' => $message,
        ]);

        return $response->successful();
    }

    /**
     * Send order status notification
     */
    public function sendOrderStatusNotification(User $user, string $orderNumber, string $status): bool
    {
        $statusMessages = [
            'pending' => 'Votre commande #:order est en attente de confirmation',
            'confirmed' => 'Votre commande #:order a été confirmée',
            'processing' => 'Votre commande #:order est en cours de préparation',
            'shipped' => 'Votre commande #:order a été expédiée',
            'delivered' => 'Votre commande #:order a été livrée',
            'cancelled' => 'Votre commande #:order a été annulée',
        ];

        $message = str_replace(':order', $orderNumber, $statusMessages[$status] ?? 'Mise à jour de votre commande');

        return $this->sendToUser(
            $user,
            'Mise à jour de commande',
            $message,
            [
                'type' => 'order_status',
                'order_number' => $orderNumber,
                'status' => $status,
            ],
            ['database', 'push', 'email']
        );
    }

    /**
     * Send payment confirmation notification
     */
    public function sendPaymentConfirmation(User $user, string $orderNumber, float $amount): bool
    {
        return $this->sendToUser(
            $user,
            'Paiement confirmé',
            "Votre paiement de {$amount} TND pour la commande #{$orderNumber} a été confirmé avec succès.",
            [
                'type' => 'payment',
                'order_number' => $orderNumber,
                'amount' => $amount,
            ],
            ['database', 'push', 'email']
        );
    }

    /**
     * Send promotion notification
     */
    public function sendPromotion(User $user, string $title, string $message, array $data = []): bool
    {
        return $this->sendToUser(
            $user,
            $title,
            $message,
            array_merge($data, ['type' => 'promotion']),
            ['database', 'push']
        );
    }

    /**
     * Get unread notification count for badge
     */
    private function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Handle failed FCM tokens
     */
    private function handleFailedTokens(array $tokens, array $results): void
    {
        foreach ($results as $index => $result) {
            if (isset($result['error'])) {
                $token = $tokens[$index] ?? null;

                if ($token && in_array($result['error'], ['NotRegistered', 'InvalidRegistration'])) {
                    // Deactivate invalid tokens
                    DeviceToken::where('token', $token)->update(['is_active' => false]);
                    Log::info('Deactivated invalid FCM token', ['token' => $token]);
                }
            }
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
