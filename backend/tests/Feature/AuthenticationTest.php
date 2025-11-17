<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+21612345678',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'token'
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/logout');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/user', [
                'name' => 'Updated Name',
                'phone' => '+21698765432',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_referral_code_is_generated_on_registration(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+21612345678',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->referral_code);
        $this->assertEquals(8, strlen($user->referral_code));
    }
}
