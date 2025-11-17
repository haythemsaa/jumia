<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\LoyaltyTier;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashSaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed loyalty tiers
        $this->artisan('db:seed', ['--class' => 'LoyaltySeeder']);
    }

    public function test_guest_can_view_active_flash_sales(): void
    {
        FlashSale::factory()->create([
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/flash-sales');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'start_time', 'end_time', 'products']
            ]);
    }

    public function test_flash_sale_products_show_discounted_price(): void
    {
        $product = Product::factory()->create(['price' => 100]);

        $flashSale = FlashSale::factory()->create([
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
            'status' => 'active',
        ]);

        FlashSaleProduct::create([
            'flash_sale_id' => $flashSale->id,
            'product_id' => $product->id,
            'discount_percentage' => 30,
            'flash_price' => 70,
            'stock_limit' => 100,
        ]);

        $response = $this->getJson("/api/flash-sales/{$flashSale->id}");

        $response->assertStatus(200)
            ->assertJsonPath('products.0.flash_price', 70);
    }

    public function test_user_can_check_flash_sale_eligibility(): void
    {
        $user = User::factory()->create(['loyalty_points' => 6000]); // Gold tier
        $user->updateLoyaltyTier();

        $product = Product::factory()->create(['price' => 100]);

        $flashSale = FlashSale::factory()->create([
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
            'status' => 'active',
            'eligible_tiers' => ['gold', 'platinum'],
        ]);

        $flashSaleProduct = FlashSaleProduct::create([
            'flash_sale_id' => $flashSale->id,
            'product_id' => $product->id,
            'discount_percentage' => 30,
            'flash_price' => 70,
            'stock_limit' => 100,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/flash-sales/check-eligibility/{$flashSaleProduct->id}");

        $response->assertStatus(200)
            ->assertJson(['eligible' => true]);
    }

    public function test_bronze_user_cannot_access_gold_flash_sale(): void
    {
        $user = User::factory()->create(['loyalty_points' => 100]); // Bronze tier

        $product = Product::factory()->create(['price' => 100]);

        $flashSale = FlashSale::factory()->create([
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
            'status' => 'active',
            'eligible_tiers' => ['gold', 'platinum'],
        ]);

        $flashSaleProduct = FlashSaleProduct::create([
            'flash_sale_id' => $flashSale->id,
            'product_id' => $product->id,
            'discount_percentage' => 30,
            'flash_price' => 70,
            'stock_limit' => 100,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/flash-sales/check-eligibility/{$flashSaleProduct->id}");

        $response->assertStatus(200)
            ->assertJson(['eligible' => false]);
    }

    public function test_flash_sale_stock_limit_is_respected(): void
    {
        $product = Product::factory()->create([
            'price' => 100,
            'stock' => 1000
        ]);

        $flashSale = FlashSale::factory()->create([
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
            'status' => 'active',
        ]);

        $flashSaleProduct = FlashSaleProduct::create([
            'flash_sale_id' => $flashSale->id,
            'product_id' => $product->id,
            'discount_percentage' => 30,
            'flash_price' => 70,
            'stock_limit' => 10,
            'sold' => 10, // All sold out
        ]);

        $response = $this->getJson("/api/flash-sales/{$flashSale->id}");

        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('products.0.sold'));
    }

    public function test_upcoming_flash_sales_are_listed_separately(): void
    {
        // Active flash sale
        FlashSale::factory()->create([
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
            'status' => 'active',
        ]);

        // Upcoming flash sale
        FlashSale::factory()->create([
            'start_time' => Carbon::now()->addDay(),
            'end_time' => Carbon::now()->addDays(2),
            'status' => 'scheduled',
        ]);

        $response = $this->getJson('/api/flash-sales/upcoming');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
    }

    public function test_expired_flash_sales_are_not_shown(): void
    {
        FlashSale::factory()->create([
            'start_time' => Carbon::now()->subDays(2),
            'end_time' => Carbon::now()->subDay(),
            'status' => 'ended',
        ]);

        $response = $this->getJson('/api/flash-sales');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json());
    }
}
