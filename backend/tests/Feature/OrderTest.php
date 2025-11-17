<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 100,
            'stock' => 10,
            'status' => 'active'
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->price,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'shipping_address' => '123 Test Street, Tunis',
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 200,
        ]);
    }

    public function test_order_reduces_product_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 100,
            'stock' => 10,
            'status' => 'active'
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => $product->price,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'shipping_address' => '123 Test Street, Tunis',
                'payment_method' => 'cash',
            ]);

        $product->refresh();
        $this->assertEquals(7, $product->stock);
    }

    public function test_user_can_view_own_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        Order::factory()->create(); // Another user's order

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/orders');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_user_can_view_single_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $order->id,
            ]);
    }

    public function test_user_cannot_view_other_user_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_cancel_pending_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_user_cannot_cancel_shipped_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'shipped'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(400);
    }

    public function test_loyalty_points_are_earned_on_order(): void
    {
        $user = User::factory()->create(['loyalty_points' => 0]);
        $product = Product::factory()->create([
            'price' => 1000, // 1000 TND order
            'stock' => 10,
            'status' => 'active'
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'shipping_address' => '123 Test Street, Tunis',
                'payment_method' => 'cash',
            ]);

        $user->refresh();
        $this->assertGreaterThan(0, $user->loyalty_points);
    }
}
