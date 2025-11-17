<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSMSNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $message
    ) {
        $this->onQueue('sms');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->user->phone) {
            return;
        }

        app(\App\Services\NotificationService::class)
            ->sendSMS($this->user->phone, $this->message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('SMS notification failed', [
            'user_id' => $this->user->id,
            'phone' => $this->user->phone,
            'error' => $exception->getMessage()
        ]);
    }
}
