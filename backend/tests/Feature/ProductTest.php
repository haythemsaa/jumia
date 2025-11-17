<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_products(): void
    {
        Product::factory()->count(3)->create(['status' => 'active']);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'description']
                ]
            ]);
    }

    public function test_guest_can_view_single_product(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => $product->name,
            ]);
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(2)->create([
            'category_id' => $category->id,
            'status' => 'active'
        ]);
        Product::factory()->create(['status' => 'active']);

        $response = $this->getJson("/api/products?category_id={$category->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_products_can_be_searched(): void
    {
        Product::factory()->create([
            'name' => 'iPhone 15',
            'status' => 'active'
        ]);
        Product::factory()->create([
            'name' => 'Samsung Galaxy',
            'status' => 'active'
        ]);

        $response = $this->getJson('/api/products?search=iPhone');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_vendor_can_create_product(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $category = Category::factory()->create();

        $response = $this->actingAs($vendor, 'sanctum')
            ->postJson('/api/products', [
                'name' => 'New Product',
                'description' => 'Product description',
                'price' => 99.99,
                'stock' => 100,
                'category_id' => $category->id,
                'status' => 'active',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'vendor_id' => $vendor->id,
        ]);
    }

    public function test_vendor_can_update_own_product(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);

        $response = $this->actingAs($vendor, 'sanctum')
            ->putJson("/api/products/{$product->id}", [
                'name' => 'Updated Product',
                'price' => 149.99,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
        ]);
    }

    public function test_vendor_cannot_update_other_vendor_product(): void
    {
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $vendor2 = User::factory()->create(['role' => 'vendor']);
        $product = Product::factory()->create(['vendor_id' => $vendor2->id]);

        $response = $this->actingAs($vendor1, 'sanctum')
            ->putJson("/api/products/{$product->id}", [
                'name' => 'Hacked Product',
            ]);

        $response->assertStatus(403);
    }

    public function test_out_of_stock_products_are_marked(): void
    {
        $product = Product::factory()->create([
            'stock' => 0,
            'status' => 'active'
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'stock' => 0,
            ]);
    }
}
