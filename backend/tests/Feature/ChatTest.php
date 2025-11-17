<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_conversation_with_vendor(): void
    {
        $user = User::factory()->create();
        $vendor = User::factory()->create(['role' => 'vendor']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/conversations', [
                'vendor_id' => $vendor->id,
                'subject' => 'Product inquiry',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'subject', 'participants'
            ]);

        $this->assertDatabaseHas('conversations', [
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
        ]);
    }

    public function test_user_can_send_message_in_conversation(): void
    {
        $user = User::factory()->create();
        $vendor = User::factory()->create(['role' => 'vendor']);

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'subject' => 'Test subject',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'message' => 'Hello, I have a question',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => 'Hello, I have a question',
        ]);
    }

    public function test_user_can_send_message_with_attachment(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $vendor = User::factory()->create(['role' => 'vendor']);

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'subject' => 'Test subject',
        ]);

        $file = UploadedFile::fake()->image('screenshot.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'message' => 'See attached screenshot',
                'attachment' => $file,
            ]);

        $response->assertStatus(201);

        Storage::disk('public')->assertExists('chat/' . $file->hashName());
    }

    public function test_user_can_view_own_conversations(): void
    {
        $user = User::factory()->create();
        $vendor = User::factory()->create(['role' => 'vendor']);

        Conversation::factory()->count(3)->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
        ]);

        // Another user's conversation
        Conversation::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/conversations');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_vendor_can_view_conversations_with_customers(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $user = User::factory()->create();

        Conversation::factory()->count(2)->create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
        ]);

        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson('/api/conversations');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_user_can_mark_messages_as_read(): void
    {
        $user = User::factory()->create();
        $vendor = User::factory()->create(['role' => 'vendor']);

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'subject' => 'Test subject',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $vendor->id,
            'message' => 'Test message',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read");

        $response->assertStatus(200);
    }

    public function test_unread_count_is_tracked(): void
    {
        $user = User::factory()->create();
        $vendor = User::factory()->create(['role' => 'vendor']);

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'subject' => 'Test subject',
        ]);

        // Vendor sends 3 messages
        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $vendor->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/conversations');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.unread_count', 3);
    }

    public function test_user_can_delete_own_message_within_time_limit(): void
    {
        $user = User::factory()->create();
        $vendor = User::factory()->create(['role' => 'vendor']);

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'subject' => 'Test subject',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => 'Test message',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/messages/{$message->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('messages', [
            'id' => $message->id,
        ]);
    }

    public function test_user_cannot_delete_other_user_message(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $vendor = User::factory()->create(['role' => 'vendor']);

        $conversation = Conversation::create([
            'user_id' => $user2->id,
            'vendor_id' => $vendor->id,
            'subject' => 'Test subject',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user2->id,
            'message' => 'Test message',
        ]);

        $response = $this->actingAs($user1, 'sanctum')
            ->deleteJson("/api/messages/{$message->id}");

        $response->assertStatus(403);
    }

    public function test_conversation_can_be_closed(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $user = User::factory()->create();

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'subject' => 'Test subject',
            'status' => 'open',
        ]);

        $response = $this->actingAs($vendor, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/close");

        $response->assertStatus(200);

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'closed',
        ]);
    }
}
