<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateLoyaltyPoints implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public Order $order
    ) {
        $this->onQueue('loyalty');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Calculate points: 1 point per 10 TND spent
        $points = floor($this->order->total / 10);

        // Apply tier multiplier
        $tier = $this->user->loyaltyTier;
        if ($tier) {
            $multiplier = $tier->benefits['points_multiplier'] ?? 1;
            $points *= $multiplier;
        }

        // Add points
        $this->user->addLoyaltyPoints(
            $points,
            "Order #{$this->order->id} purchase"
        );

        // Check and update missions
        $this->checkMissions();
    }

    /**
     * Check if this order completes any missions
     */
    private function checkMissions(): void
    {
        $userMissions = $this->user->userMissions()
            ->where('status', 'active')
            ->whereHas('mission', function($q) {
                $q->where('type', 'order_placed');
            })
            ->get();

        foreach ($userMissions as $userMission) {
            $userMission->incrementProgress();
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Loyalty points update failed', [
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'error' => $exception->getMessage()
        ]);
    }
}
