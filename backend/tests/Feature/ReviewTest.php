<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_review_for_purchased_product(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Create a completed order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reviews', [
                'product_id' => $product->id,
                'rating' => 5,
                'title' => 'Great product!',
                'comment' => 'I love this product',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_review_can_include_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $product = Product::factory()->create();

        $photo = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reviews', [
                'product_id' => $product->id,
                'rating' => 5,
                'title' => 'Great product!',
                'comment' => 'I love this product',
                'photos' => [$photo],
            ]);

        $response->assertStatus(201);
        Storage::disk('public')->assertExists('reviews/' . $photo->hashName());
    }

    public function test_review_requires_moderation_by_default(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reviews', [
                'product_id' => $product->id,
                'rating' => 5,
                'title' => 'Great product!',
                'comment' => 'I love this product',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_approve_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $review = Review::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/reviews/{$review->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $review = Review::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/reviews/{$review->id}/reject", [
                'reason' => 'Inappropriate content'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 'rejected',
        ]);
    }

    public function test_only_approved_reviews_are_shown_to_public(): void
    {
        $product = Product::factory()->create();

        Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'approved'
        ]);

        Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'pending'
        ]);

        $response = $this->getJson("/api/products/{$product->id}/reviews");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_vendor_can_respond_to_review(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'approved'
        ]);

        $response = $this->actingAs($vendor, 'sanctum')
            ->postJson("/api/reviews/{$review->id}/respond", [
                'response' => 'Thank you for your feedback!'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'vendor_response' => 'Thank you for your feedback!',
        ]);
    }

    public function test_product_average_rating_is_calculated(): void
    {
        $product = Product::factory()->create();

        Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
            'status' => 'approved'
        ]);

        Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 3,
            'status' => 'approved'
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        // Average should be 4.0
        $this->assertEquals(4.0, $response->json('average_rating'));
    }
}
