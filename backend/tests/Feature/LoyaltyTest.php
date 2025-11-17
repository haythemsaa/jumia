<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyMission;
use App\Models\Referral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed loyalty tiers
        $this->artisan('db:seed', ['--class' => 'LoyaltySeeder']);
    }

    public function test_user_can_view_loyalty_dashboard(): void
    {
        $user = User::factory()->create(['loyalty_points' => 500]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/loyalty/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'points',
                'tier',
                'next_tier',
                'points_to_next_tier',
                'recent_transactions',
            ]);
    }

    public function test_user_tier_upgrades_automatically(): void
    {
        $user = User::factory()->create(['loyalty_points' => 500]);
        $bronzeTier = LoyaltyTier::where('slug', 'bronze')->first();

        $this->assertEquals($bronzeTier->id, $user->loyalty_tier_id);

        // Add points to reach Silver tier (1000 points)
        $user->addLoyaltyPoints(600, 'Test points');
        $user->refresh();

        $silverTier = LoyaltyTier::where('slug', 'silver')->first();
        $this->assertEquals($silverTier->id, $user->loyalty_tier_id);
    }

    public function test_user_can_apply_referral_code(): void
    {
        $referrer = User::factory()->create([
            'referral_code' => 'TEST1234'
        ]);
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/loyalty/referral/apply', [
                'code' => 'TEST1234'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $user->id,
            'code' => 'TEST1234',
        ]);
    }

    public function test_user_cannot_use_own_referral_code(): void
    {
        $user = User::factory()->create([
            'referral_code' => 'TEST1234'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/loyalty/referral/apply', [
                'code' => 'TEST1234'
            ]);

        $response->assertStatus(400);
    }

    public function test_referral_code_can_only_be_used_once(): void
    {
        $referrer = User::factory()->create([
            'referral_code' => 'TEST1234'
        ]);
        $user = User::factory()->create();

        // Use code first time
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/loyalty/referral/apply', [
                'code' => 'TEST1234'
            ]);

        // Try to use again
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/loyalty/referral/apply', [
                'code' => 'TEST1234'
            ]);

        $response->assertStatus(400);
    }

    public function test_user_can_view_available_missions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/loyalty/missions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'title', 'description', 'reward_points', 'progress']
            ]);
    }

    public function test_user_can_redeem_points(): void
    {
        $user = User::factory()->create(['loyalty_points' => 1000]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/loyalty/redeem', [
                'points' => 500,
                'type' => 'discount_voucher',
            ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals(500, $user->loyalty_points);
    }

    public function test_user_cannot_redeem_more_points_than_available(): void
    {
        $user = User::factory()->create(['loyalty_points' => 100]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/loyalty/redeem', [
                'points' => 500,
                'type' => 'discount_voucher',
            ]);

        $response->assertStatus(400);
    }

    public function test_tier_benefits_are_included_in_dashboard(): void
    {
        $user = User::factory()->create(['loyalty_points' => 5500]); // Gold tier
        $user->updateLoyaltyTier();
        $user->refresh();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/loyalty/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('tier.slug', 'gold')
            ->assertJsonStructure([
                'tier' => ['benefits']
            ]);
    }
}
