<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'min_points',
        'max_points',
        'discount_percentage',
        'points_multiplier',
        'benefits',
        'badge_color',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_points' => 'integer',
            'max_points' => 'integer',
            'discount_percentage' => 'decimal:2',
            'points_multiplier' => 'integer',
            'benefits' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Helper methods
    public function isInRange(int $points): bool
    {
        if ($this->max_points === null) {
            return $points >= $this->min_points;
        }

        return $points >= $this->min_points && $points <= $this->max_points;
    }

    public static function getTierByPoints(int $points): ?self
    {
        return self::active()
            ->where('min_points', '<=', $points)
            ->where(function ($query) use ($points) {
                $query->where('max_points', '>=', $points)
                    ->orWhereNull('max_points');
            })
            ->orderBy('min_points', 'desc')
            ->first();
    }
}
