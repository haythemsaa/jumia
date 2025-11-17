<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $subject,
        public string $template,
        public array $data = []
    ) {
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->send(
            new \App\Mail\GenericNotification(
                $this->subject,
                $this->template,
                $this->data
            )
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Email notification failed', [
            'user_id' => $this->user->id,
            'subject' => $this->subject,
            'error' => $exception->getMessage()
        ]);
    }
}
