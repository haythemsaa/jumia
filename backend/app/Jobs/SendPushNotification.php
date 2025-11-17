<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $title,
        public string $body,
        public array $data = []
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->user->fcm_token) {
            return;
        }

        $notification = [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ];

        app(\App\Services\NotificationService::class)
            ->sendPushNotification($this->user, $notification);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Push notification failed', [
            'user_id' => $this->user->id,
            'title' => $this->title,
            'error' => $exception->getMessage()
        ]);
    }
}
