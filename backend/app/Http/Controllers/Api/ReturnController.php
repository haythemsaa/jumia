<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReturnController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user's return requests
     */
    public function index()
    {
        $user = auth()->user();

        $returns = ReturnRequest::where('user_id', $user->id)
            ->with(['order', 'orderItem.product'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'returns' => $returns,
        ]);
    }

    /**
     * Create a return request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'order_item_id' => 'required|exists:order_items,id',
            'type' => 'required|in:return,exchange,refund',
            'reason' => 'required|in:defective,wrong_item,not_as_described,damaged,changed_mind,other',
            'description' => 'required|string|max:1000',
            'images.*' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        // Verify order belongs to user
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande introuvable',
            ], 404);
        }

        // Check if order is delivered
        if (!in_array($order->status, ['delivered', 'completed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez retourner que les commandes livrées',
            ], 400);
        }

        // Check return window (e.g., 14 days)
        $returnWindowDays = config('returns.window_days', 14);
        if ($order->delivered_at && $order->delivered_at->diffInDays(now()) > $returnWindowDays) {
            return response()->json([
                'success' => false,
                'message' => "Le délai de retour de {$returnWindowDays} jours est dépassé",
            ], 400);
        }

        // Check if return already requested for this item
        $existingReturn = ReturnRequest::where('order_id', $request->order_id)
            ->where('order_item_id', $request->order_item_id)
            ->whereIn('status', ['pending', 'approved', 'processing'])
            ->first();

        if ($existingReturn) {
            return response()->json([
                'success' => false,
                'message' => 'Une demande de retour existe déjà pour cet article',
            ], 409);
        }

        // Upload images if provided
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('returns', 'public');
                $imagePaths[] = $path;
            }
        }

        // Generate return number
        $returnNumber = 'RET-' . strtoupper(uniqid());

        // Create return request
        $return = ReturnRequest::create([
            'return_number' => $returnNumber,
            'user_id' => $user->id,
            'order_id' => $request->order_id,
            'order_item_id' => $request->order_item_id,
            'type' => $request->type,
            'reason' => $request->reason,
            'description' => $request->description,
            'images' => $imagePaths,
            'status' => 'pending',
        ]);

        // Send notification to user
        $this->notificationService->sendToUser(
            $user,
            'Demande de retour reçue',
            "Votre demande de retour #{$returnNumber} a été reçue et sera traitée dans les 24-48 heures.",
            [
                'type' => 'return_request',
                'return_number' => $returnNumber,
                'order_number' => $order->order_number,
            ],
            ['database', 'push', 'email']
        );

        return response()->json([
            'success' => true,
            'message' => 'Demande de retour créée avec succès',
            'return' => $return,
        ], 201);
    }

    /**
     * Get details of a return request
     */
    public function show($id)
    {
        $user = auth()->user();

        $return = ReturnRequest::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['order', 'orderItem.product'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'return' => $return,
        ]);
    }

    /**
     * Cancel a return request (only if pending)
     */
    public function cancel($id)
    {
        $user = auth()->user();

        $return = ReturnRequest::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($return->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez annuler que les demandes en attente',
            ], 400);
        }

        $return->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Demande de retour annulée',
        ]);
    }

    /**
     * Admin: Get all return requests
     */
    public function adminIndex(Request $request)
    {
        $query = ReturnRequest::with(['user', 'order', 'orderItem.product']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search by return number or order number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', "%{$search}%");
                    });
            });
        }

        $returns = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'returns' => $returns,
        ]);
    }

    /**
     * Admin: Approve return request
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'nullable|string|max:1000',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $return = ReturnRequest::findOrFail($id);

        if ($return->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Seules les demandes en attente peuvent être approuvées',
            ], 400);
        }

        $return->approve(
            $request->input('admin_notes'),
            $request->input('refund_amount', $return->orderItem->price * $return->orderItem->quantity)
        );

        // Send notification to user
        $this->notificationService->sendToUser(
            $return->user,
            'Demande de retour approuvée',
            "Votre demande de retour #{$return->return_number} a été approuvée. Veuillez retourner l'article selon les instructions.",
            [
                'type' => 'return_approved',
                'return_number' => $return->return_number,
                'order_number' => $return->order->order_number,
            ],
            ['database', 'push', 'email']
        );

        return response()->json([
            'success' => true,
            'message' => 'Demande de retour approuvée',
            'return' => $return,
        ]);
    }

    /**
     * Admin: Reject return request
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $return = ReturnRequest::findOrFail($id);

        if ($return->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Seules les demandes en attente peuvent être rejetées',
            ], 400);
        }

        $return->reject($request->admin_notes);

        // Send notification to user
        $this->notificationService->sendToUser(
            $return->user,
            'Demande de retour rejetée',
            "Votre demande de retour #{$return->return_number} a été rejetée. Raison: {$request->admin_notes}",
            [
                'type' => 'return_rejected',
                'return_number' => $return->return_number,
                'order_number' => $return->order->order_number,
            ],
            ['database', 'push', 'email']
        );

        return response()->json([
            'success' => true,
            'message' => 'Demande de retour rejetée',
            'return' => $return,
        ]);
    }

    /**
     * Admin: Mark return as processing (item received)
     */
    public function markAsProcessing($id)
    {
        $return = ReturnRequest::findOrFail($id);

        if ($return->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Seules les demandes approuvées peuvent être mises en traitement',
            ], 400);
        }

        $return->update(['status' => 'processing']);

        // Send notification to user
        $this->notificationService->sendToUser(
            $return->user,
            'Retour en cours de traitement',
            "Nous avons reçu votre retour #{$return->return_number} et le traitons actuellement.",
            [
                'type' => 'return_processing',
                'return_number' => $return->return_number,
            ],
            ['database', 'push']
        );

        return response()->json([
            'success' => true,
            'message' => 'Retour marqué en cours de traitement',
        ]);
    }

    /**
     * Admin: Complete return (refund issued)
     */
    public function complete(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $return = ReturnRequest::findOrFail($id);

        if (!in_array($return->status, ['approved', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Statut invalide pour compléter le retour',
            ], 400);
        }

        $return->complete($request->input('admin_notes'));

        // Send notification to user
        $this->notificationService->sendToUser(
            $return->user,
            'Retour complété',
            "Votre retour #{$return->return_number} a été traité. Le remboursement de {$return->refund_amount} TND sera effectué sous 3-5 jours ouvrables.",
            [
                'type' => 'return_completed',
                'return_number' => $return->return_number,
                'refund_amount' => $return->refund_amount,
            ],
            ['database', 'push', 'email']
        );

        return response()->json([
            'success' => true,
            'message' => 'Retour complété avec succès',
            'return' => $return,
        ]);
    }

    /**
     * Get return statistics (admin)
     */
    public function statistics()
    {
        $stats = [
            'total' => ReturnRequest::count(),
            'pending' => ReturnRequest::where('status', 'pending')->count(),
            'approved' => ReturnRequest::where('status', 'approved')->count(),
            'processing' => ReturnRequest::where('status', 'processing')->count(),
            'completed' => ReturnRequest::where('status', 'completed')->count(),
            'rejected' => ReturnRequest::where('status', 'rejected')->count(),
            'by_type' => [
                'return' => ReturnRequest::where('type', 'return')->count(),
                'exchange' => ReturnRequest::where('type', 'exchange')->count(),
                'refund' => ReturnRequest::where('type', 'refund')->count(),
            ],
            'by_reason' => ReturnRequest::select('reason')
                ->selectRaw('count(*) as count')
                ->groupBy('reason')
                ->get()
                ->pluck('count', 'reason'),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }
}
