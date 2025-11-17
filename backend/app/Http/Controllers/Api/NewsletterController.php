<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
        ]);

        $subscription = NewsletterSubscription::updateOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->name,
                'user_id' => auth()->id(),
                'status' => 'subscribed',
                'token' => Str::random(32),
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]
        );

        // Send welcome email (optional)
        // \App\Jobs\SendEmailNotification::dispatch(...)

        return response()->json([
            'success' => true,
            'message' => __('messages.newsletter_subscribed'),
            'data' => $subscription
        ], 201);
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe(Request $request, string $token): JsonResponse
    {
        $subscription = NewsletterSubscription::where('token', $token)->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => __('messages.invalid_token')
            ], 404);
        }

        $subscription->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.newsletter_unsubscribed')
        ]);
    }

    /**
     * Get subscription status
     */
    public function status(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $subscription = NewsletterSubscription::where('email', $request->email)->first();

        if (!$subscription) {
            return response()->json([
                'subscribed' => false
            ]);
        }

        return response()->json([
            'subscribed' => $subscription->status === 'subscribed',
            'subscribed_at' => $subscription->subscribed_at,
        ]);
    }
}
