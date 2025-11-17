<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'action',
        'target_count',
        'points_reward',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'target_count' => 'integer',
            'points_reward' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function userMissions()
    {
        return $this->hasMany(UserMission::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            });
    }

    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    public function scopeWeekly($query)
    {
        return $query->where('type', 'weekly');
    }

    // Helper methods
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }
}
