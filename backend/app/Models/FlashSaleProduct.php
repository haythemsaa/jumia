<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSaleProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'flash_sale_id',
        'product_id',
        'discount_percentage',
        'flash_price',
        'stock_quantity',
        'sold_quantity',
        'max_per_customer',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'flash_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'sold_quantity' => 'integer',
            'max_per_customer' => 'integer',
        ];
    }

    // Relations
    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Helper methods
    public function hasStock(): bool
    {
        return $this->sold_quantity < $this->stock_quantity;
    }

    public function getRemainingStock(): int
    {
        return max(0, $this->stock_quantity - $this->sold_quantity);
    }

    public function canPurchase(User $user, int $quantity): bool
    {
        if (!$this->hasStock()) {
            return false;
        }

        if ($quantity > $this->getRemainingStock()) {
            return false;
        }

        // Check if user has already purchased maximum allowed
        $userPurchased = Order::where('user_id', $user->id)
            ->whereHas('items', function ($query) {
                $query->where('product_id', $this->product_id);
            })
            ->where('created_at', '>=', $this->flashSale->starts_at)
            ->where('created_at', '<=', $this->flashSale->ends_at)
            ->sum('quantity');

        return ($userPurchased + $quantity) <= $this->max_per_customer;
    }

    public function incrementSold(int $quantity): void
    {
        $this->increment('sold_quantity', $quantity);
        $this->flashSale->increment('sold_quantity', $quantity);
    }
}
