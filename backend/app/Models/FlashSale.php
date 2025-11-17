<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
        'is_active',
        'max_quantity',
        'sold_quantity',
        'eligible_tiers',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'max_quantity' => 'integer',
            'sold_quantity' => 'integer',
            'eligible_tiers' => 'array',
        ];
    }

    // Relations
    public function products()
    {
        return $this->belongsToMany(Product::class, 'flash_sale_products')
            ->withPivot([
                'discount_percentage',
                'flash_price',
                'stock_quantity',
                'sold_quantity',
                'max_per_customer',
            ])
            ->withTimestamps();
    }

    public function flashSaleProducts()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '>', now());
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active
            && now()->between($this->starts_at, $this->ends_at);
    }

    public function isUpcoming(): bool
    {
        return $this->is_active && now()->lt($this->starts_at);
    }

    public function hasEnded(): bool
    {
        return now()->gt($this->ends_at);
    }

    public function getTimeRemaining(): array
    {
        if ($this->hasEnded()) {
            return [
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
            ];
        }

        $diff = now()->diff($this->ends_at);

        return [
            'days' => $diff->d,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s,
        ];
    }

    public function canUserAccess(User $user): bool
    {
        if (empty($this->eligible_tiers)) {
            return true;
        }

        if (!$user->loyaltyTier) {
            return false;
        }

        return in_array($user->loyaltyTier->slug, $this->eligible_tiers);
    }

    public function hasStock(): bool
    {
        if ($this->max_quantity === null) {
            return true;
        }

        return $this->sold_quantity < $this->max_quantity;
    }
}
