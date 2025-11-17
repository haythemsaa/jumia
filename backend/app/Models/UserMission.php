<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'loyalty_mission_id',
        'current_count',
        'is_completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'current_count' => 'integer',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mission()
    {
        return $this->belongsTo(LoyaltyMission::class, 'loyalty_mission_id');
    }

    // Helper methods
    public function incrementProgress(int $amount = 1): bool
    {
        if ($this->is_completed) {
            return false;
        }

        $this->current_count += $amount;

        if ($this->current_count >= $this->mission->target_count) {
            $this->complete();
        } else {
            $this->save();
        }

        return true;
    }

    public function complete(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        // Award points to user
        $this->user->addLoyaltyPoints($this->mission->points_reward, 'Mission complétée: ' . $this->mission->title);
    }

    public function getProgress(): int
    {
        return min(100, ($this->current_count / $this->mission->target_count) * 100);
    }
}
