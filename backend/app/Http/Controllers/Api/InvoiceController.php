<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    private InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Download invoice PDF
     */
    public function download(Request $request, $orderId)
    {
        $user = auth()->user();

        $query = Order::where('id', $orderId);

        // Users can only download their own invoices, unless admin
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $order = $query->firstOrFail();

        // Only allow invoice download for paid orders
        if (!$order->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'La facture n\'est disponible que pour les commandes payées',
            ], 403);
        }

        return $this->invoiceService->download($order);
    }

    /**
     * View invoice in browser
     */
    public function view(Request $request, $orderId)
    {
        $user = auth()->user();

        $query = Order::where('id', $orderId);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $order = $query->firstOrFail();

        if (!$order->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'La facture n\'est disponible que pour les commandes payées',
            ], 403);
        }

        return $this->invoiceService->stream($order);
    }

    /**
     * Generate and email invoice
     */
    public function email(Request $request, $orderId)
    {
        $user = auth()->user();

        $query = Order::where('id', $orderId);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $order = $query->firstOrFail();

        if (!$order->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'La facture n\'est disponible que pour les commandes payées',
            ], 403);
        }

        // Generate PDF
        $pdf = $this->invoiceService->generateInvoice($order);

        // Send email with attachment
        \Mail::send('emails.invoice', [
            'order' => $order,
            'user' => $user,
        ], function ($message) use ($user, $pdf, $order) {
            $message->to($user->email)
                ->subject('Facture - Commande #' . $order->order_number)
                ->attachData($pdf->output(), 'facture-' . $order->order_number . '.pdf');
        });

        return response()->json([
            'success' => true,
            'message' => 'Facture envoyée par email à ' . $user->email,
        ]);
    }

    /**
     * Get invoice URL
     */
    public function getUrl($orderId)
    {
        $user = auth()->user();

        $query = Order::where('id', $orderId);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $order = $query->firstOrFail();

        if (!$order->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'La facture n\'est disponible que pour les commandes payées',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'invoice_url' => route('invoice.download', ['order' => $order->id]),
        ]);
    }
}
