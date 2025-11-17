<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Get all conversations for authenticated user
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isVendor()) {
            $conversations = Conversation::where('vendor_id', $user->vendor->id)
                ->with(['user', 'lastMessage', 'order'])
                ->orderByDesc('last_message_at')
                ->paginate(20);
        } else {
            $conversations = Conversation::where('user_id', $user->id)
                ->with(['vendor.user', 'lastMessage', 'order'])
                ->orderByDesc('last_message_at')
                ->paginate(20);
        }

        // Add unread count for each conversation
        $conversations->getCollection()->transform(function ($conversation) use ($user) {
            $conversation->unread_count = $conversation->getUnreadCount($user->id);
            return $conversation;
        });

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Get or create conversation with vendor
     */
    public function getOrCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
            'order_id' => 'nullable|exists:orders,id',
            'subject' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        // Find existing conversation or create new one
        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'vendor_id' => $request->vendor_id,
            ],
            [
                'order_id' => $request->order_id,
                'subject' => $request->subject ?? 'Nouvelle conversation',
                'status' => 'active',
            ]
        );

        $conversation->load(['vendor.user', 'order', 'messages' => function ($query) {
            $query->latest()->limit(50);
        }]);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Get conversation details with messages
     */
    public function show($id)
    {
        $user = auth()->user();

        $conversation = Conversation::with(['user', 'vendor.user', 'order'])->findOrFail($id);

        // Check authorization
        if (!$this->canAccessConversation($conversation, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        // Get messages with pagination
        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Mark messages as read
        $conversation->markAsRead($user->id);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    /**
     * Send message in conversation
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:5000',
            'attachments.*' => 'nullable|file|max:5120', // Max 5MB per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        $conversation = Conversation::findOrFail($conversationId);

        // Check authorization
        if (!$this->canAccessConversation($conversation, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        // Upload attachments if any
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('chat-attachments', 'public');
                $attachmentPaths[] = $path;
            }
        }

        // Determine sender type
        $senderType = $user->isVendor() ? 'vendor' : 'user';

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_type' => $senderType,
            'message' => $request->message,
            'attachments' => $attachmentPaths,
        ]);

        // Reopen conversation if it was closed
        if (!$conversation->isActive()) {
            $conversation->reopen();
        }

        $message->load('sender');

        // TODO: Broadcast message to other party via WebSocket/Pusher
        // broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message,
        ], 201);
    }

    /**
     * Mark conversation messages as read
     */
    public function markAsRead($id)
    {
        $user = auth()->user();
        $conversation = Conversation::findOrFail($id);

        if (!$this->canAccessConversation($conversation, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        $conversation->markAsRead($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Messages marqués comme lus',
        ]);
    }

    /**
     * Close conversation
     */
    public function close($id)
    {
        $user = auth()->user();
        $conversation = Conversation::findOrFail($id);

        if (!$this->canAccessConversation($conversation, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        $conversation->close();

        return response()->json([
            'success' => true,
            'message' => 'Conversation fermée',
        ]);
    }

    /**
     * Get unread messages count
     */
    public function unreadCount()
    {
        $user = auth()->user();

        if ($user->isVendor()) {
            $count = Message::whereHas('conversation', function ($query) use ($user) {
                $query->where('vendor_id', $user->vendor->id);
            })
                ->where('sender_id', '!=', $user->id)
                ->unread()
                ->count();
        } else {
            $count = Message::whereHas('conversation', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->where('sender_id', '!=', $user->id)
                ->unread()
                ->count();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    /**
     * Check if user can access conversation
     */
    private function canAccessConversation(Conversation $conversation, $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor()) {
            return $conversation->vendor_id === $user->vendor->id;
        }

        return $conversation->user_id === $user->id;
    }

    /**
     * Delete message (only sender within 5 minutes)
     */
    public function deleteMessage($messageId)
    {
        $user = auth()->user();
        $message = Message::findOrFail($messageId);

        if ($message->sender_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        // Allow deletion only within 5 minutes
        if ($message->created_at->diffInMinutes(now()) > 5) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez supprimer un message que dans les 5 minutes suivant son envoi',
            ], 403);
        }

        // Delete attachments
        if ($message->hasAttachments()) {
            foreach ($message->attachments as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message supprimé',
        ]);
    }
}
