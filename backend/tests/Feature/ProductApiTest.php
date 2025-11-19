<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'price', 'stock']
                     ]
                 ]);
    }

    /** @test */
    public function it_can_show_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $product->id,
                     'name' => $product->name
                 ]);
    }

    /** @test */
    public function it_can_create_product_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock' => 10,
            'category_id' => $category->id
        ]);

        $response->assertStatus(201)
                 ->assertJson(['name' => 'Test Product']);
    }

    /** @test */
    public function it_cannot_create_product_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 99.99
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_filter_products_by_category()
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);
        Product::factory()->count(2)->create();

        $response = $this->getJson("/api/products?category={$category->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }
}
