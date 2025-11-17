<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'points',
        'description',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopeEarned($query)
    {
        return $query->where('type', 'earned');
    }

    public function scopeRedeemed($query)
    {
        return $query->where('type', 'redeemed');
    }

    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    // Helper methods
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public static function awardPoints($userId, $orderId, $points, $description = null)
    {
        return self::create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'type' => 'earned',
            'points' => $points,
            'description' => $description ?? 'Points earned from order',
            'expires_at' => now()->addYear(), // Points expire after 1 year
        ]);
    }

    public static function redeemPoints($userId, $points, $description = null)
    {
        $available = self::where('user_id', $userId)
            ->where('type', 'earned')
            ->valid()
            ->sum('points') -
            self::where('user_id', $userId)
            ->where('type', 'redeemed')
            ->sum('points');

        if ($available < $points) {
            return false;
        }

        return self::create([
            'user_id' => $userId,
            'type' => 'redeemed',
            'points' => $points,
            'description' => $description ?? 'Points redeemed',
        ]);
    }

    public static function getUserBalance($userId)
    {
        $earned = self::where('user_id', $userId)
            ->where('type', 'earned')
            ->valid()
            ->sum('points');

        $redeemed = self::where('user_id', $userId)
            ->where('type', 'redeemed')
            ->sum('points');

        return $earned - $redeemed;
    }
}
