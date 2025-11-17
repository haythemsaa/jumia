<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate PDF invoice for an order
     */
    public function generateInvoice(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->load(['user', 'items.product', 'shippingAddress', 'billingAddress', 'paymentTransactions']);

        $data = [
            'order' => $order,
            'company' => $this->getCompanyInfo(),
            'invoice_number' => $this->generateInvoiceNumber($order),
            'invoice_date' => now()->format('d/m/Y'),
            'due_date' => now()->addDays(30)->format('d/m/Y'),
        ];

        return Pdf::loadView('invoices.order', $data)
            ->setPaper('a4')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-right', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('margin-left', '10mm');
    }

    /**
     * Generate and save invoice to storage
     */
    public function generateAndSave(Order $order): string
    {
        $pdf = $this->generateInvoice($order);
        $filename = 'invoices/' . $this->generateInvoiceNumber($order) . '.pdf';

        Storage::disk('local')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Generate invoice number from order
     */
    public function generateInvoiceNumber(Order $order): string
    {
        return 'INV-' . $order->order_number;
    }

    /**
     * Get company information for invoice header
     */
    private function getCompanyInfo(): array
    {
        return [
            'name' => 'ICHRI Tunisia',
            'address' => 'Tunis, Tunisia',
            'phone' => '+216 XX XXX XXX',
            'email' => 'contact@ichri.tn',
            'website' => 'www.ichri.tn',
            'registration' => 'XXXXX',
            'vat_number' => 'XXXXXX',
        ];
    }

    /**
     * Download invoice as PDF
     */
    public function download(Order $order)
    {
        $pdf = $this->generateInvoice($order);
        $filename = $this->generateInvoiceNumber($order) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Stream invoice PDF in browser
     */
    public function stream(Order $order)
    {
        $pdf = $this->generateInvoice($order);
        $filename = $this->generateInvoiceNumber($order) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Get invoice URL for download
     */
    public function getInvoiceUrl(Order $order): ?string
    {
        $filename = 'invoices/' . $this->generateInvoiceNumber($order) . '.pdf';

        if (Storage::disk('local')->exists($filename)) {
            return route('invoice.download', ['order' => $order->id]);
        }

        return null;
    }

    /**
     * Calculate totals breakdown for invoice
     */
    public function getTotalsBreakdown(Order $order): array
    {
        return [
            'subtotal' => $order->subtotal,
            'shipping' => $order->shipping_cost,
            'tax' => $order->tax_amount,
            'discount' => $order->discount_amount,
            'total' => $order->total_amount,
        ];
    }
}
