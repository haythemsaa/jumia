<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $paymentMethod,
        public array $webhookData
    ) {
        $this->onQueue('payments');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $service = match($this->paymentMethod) {
            'edinar' => app(\App\Services\EDinarService::class),
            'konnect' => app(\App\Services\KonnectService::class),
            'd17' => app(\App\Services\D17Service::class),
            default => null
        };

        if ($service) {
            $service->processWebhook($this->webhookData);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Payment webhook processing failed', [
            'payment_method' => $this->paymentMethod,
            'webhook_data' => $this->webhookData,
            'error' => $exception->getMessage()
        ]);
    }
}
