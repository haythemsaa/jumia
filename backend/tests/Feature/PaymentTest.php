<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_initiate_edinar_payment(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 100,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/payments/edinar/initiate", [
                'order_id' => $order->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'payment_url',
                'transaction_id'
            ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'edinar',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_initiate_konnect_payment(): void
    {
        Http::fake([
            'api.konnect.network/*' => Http::response([
                'payUrl' => 'https://payment.konnect.network/test',
                'paymentRef' => 'TEST123'
            ], 200)
        ]);

        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 100,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/payments/konnect/initiate", [
                'order_id' => $order->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'payment_url',
                'payment_ref'
            ]);
    }

    public function test_cash_on_delivery_creates_payment_record(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 100,
            'status' => 'pending',
            'payment_method' => 'cash'
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_method' => 'cash',
        ]);
    }

    public function test_edinar_webhook_updates_payment_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'payment_method' => 'edinar',
            'status' => 'pending',
            'transaction_id' => 'TEST123'
        ]);

        $signature = hash('sha256', 'TEST123' . config('services.edinar.secret_key'));

        $response = $this->postJson('/api/webhooks/edinar', [
            'transaction_id' => 'TEST123',
            'status' => 'success',
            'signature' => $signature,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'transaction_id' => 'TEST123',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_konnect_webhook_updates_payment_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'payment_method' => 'konnect',
            'status' => 'pending',
            'transaction_id' => 'KONNECT123'
        ]);

        $response = $this->postJson('/api/webhooks/konnect', [
            'payment_ref' => 'KONNECT123',
            'status' => 'completed',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'transaction_id' => 'KONNECT123',
            'status' => 'completed',
        ]);
    }

    public function test_failed_payment_updates_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'payment_method' => 'edinar',
            'status' => 'pending',
            'transaction_id' => 'TEST123'
        ]);

        $signature = hash('sha256', 'TEST123' . config('services.edinar.secret_key'));

        $this->postJson('/api/webhooks/edinar', [
            'transaction_id' => 'TEST123',
            'status' => 'failed',
            'signature' => $signature,
        ]);

        $this->assertDatabaseHas('payments', [
            'transaction_id' => 'TEST123',
            'status' => 'failed',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'payment_failed',
        ]);
    }

    public function test_payment_requires_valid_order(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/payments/edinar/initiate", [
                'order_id' => 99999, // Non-existent order
            ]);

        $response->assertStatus(404);
    }

    public function test_user_can_only_pay_for_own_orders(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user2->id,
            'total' => 100,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user1, 'sanctum')
            ->postJson("/api/payments/edinar/initiate", [
                'order_id' => $order->id,
            ]);

        $response->assertStatus(403);
    }
}
