<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function loyaltyTier()
    {
        return $this->belongsTo(LoyaltyTier::class);
    }

    public function userMissions()
    {
        return $this->hasMany(UserMission::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referredBy()
    {
        return $this->hasOne(Referral::class, 'referred_id');
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isVendor()
    {
        return $this->role === 'vendor';
    }

    public function isClient()
    {
        return $this->role === 'client';
    }

    public function getTotalLoyaltyPoints()
    {
        return $this->loyaltyPoints()
            ->where('type', 'earned')
            ->sum('points') -
            $this->loyaltyPoints()
            ->where('type', 'redeemed')
            ->sum('points');
    }

    public function addLoyaltyPoints(int $points, string $description = 'Points earned'): void
    {
        LoyaltyPoint::create([
            'user_id' => $this->id,
            'points' => $points,
            'type' => 'earned',
            'description' => $description,
        ]);

        $this->increment('loyalty_points', $points);
        $this->updateLoyaltyTier();
    }

    public function deductLoyaltyPoints(int $points, string $description = 'Points redeemed'): bool
    {
        if ($this->loyalty_points < $points) {
            return false;
        }

        LoyaltyPoint::create([
            'user_id' => $this->id,
            'points' => $points,
            'type' => 'redeemed',
            'description' => $description,
        ]);

        $this->decrement('loyalty_points', $points);
        $this->updateLoyaltyTier();

        return true;
    }

    public function updateLoyaltyTier(): void
    {
        $tier = LoyaltyTier::getTierByPoints($this->loyalty_points);

        if ($tier && $this->loyalty_tier_id !== $tier->id) {
            $this->update(['loyalty_tier_id' => $tier->id]);
        }
    }

    public function generateReferralCode(): string
    {
        if ($this->referral_code) {
            return $this->referral_code;
        }

        $code = strtoupper(substr($this->name, 0, 3) . rand(1000, 9999));

        while (self::where('referral_code', $code)->exists()) {
            $code = strtoupper(substr($this->name, 0, 3) . rand(1000, 9999));
        }

        $this->update(['referral_code' => $code]);

        return $code;
    }
}
