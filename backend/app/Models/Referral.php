<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referral_code',
        'points_earned',
        'is_claimed',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'points_earned' => 'integer',
            'is_claimed' => 'boolean',
            'claimed_at' => 'datetime',
        ];
    }

    // Relations
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    // Helper methods
    public function claim(int $points = 100): void
    {
        if ($this->is_claimed) {
            return;
        }

        $this->update([
            'points_earned' => $points,
            'is_claimed' => true,
            'claimed_at' => now(),
        ]);

        // Award points to referrer
        $this->referrer->addLoyaltyPoints($points, 'Parrainage de ' . $this->referred->name);
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }
}
