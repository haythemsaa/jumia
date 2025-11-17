<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateInvoicePDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
        public bool $emailToUser = false
    ) {
        $this->onQueue('invoices');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $invoiceService = app(\App\Services\InvoiceService::class);

        // Generate and save the PDF
        $filePath = $invoiceService->generateAndSave($this->order);

        // Email to user if requested
        if ($this->emailToUser) {
            SendEmailNotification::dispatch(
                $this->order->user,
                __('messages.invoice_ready'),
                'invoice',
                [
                    'order_id' => $this->order->id,
                    'invoice_path' => $filePath
                ]
            );
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Invoice PDF generation failed', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage()
        ]);
    }
}
