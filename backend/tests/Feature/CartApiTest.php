<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_add_product_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);
    }

    /** @test */
    public function it_can_get_cart_items()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $user->cart()->create([
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertStatus(200)
                 ->assertJsonStructure(['items', 'subtotal', 'total']);
    }

    /** @test */
    public function it_can_update_cart_item_quantity()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $cartItem = $user->cart()->create([
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($user)->putJson("/api/cart/update/{$cartItem->id}", [
            'quantity' => 3
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 3
        ]);
    }

    /** @test */
    public function it_can_remove_item_from_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $cartItem = $user->cart()->create([
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/cart/remove/{$cartItem->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }
}
