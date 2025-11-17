<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    // Relations
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // Helper methods
    public function getSubtotal()
    {
        return $this->price * $this->quantity;
    }

    public function isAvailable()
    {
        if ($this->product_variant_id) {
            return $this->variant && $this->variant->isInStock() && $this->variant->stock_quantity >= $this->quantity;
        }

        return $this->product && $this->product->isInStock() && $this->product->stock_quantity >= $this->quantity;
    }

    public function getCurrentPrice()
    {
        if ($this->product_variant_id && $this->variant) {
            return $this->variant->price;
        }

        return $this->product ? $this->product->price : $this->price;
    }

    public function syncPrice()
    {
        $currentPrice = $this->getCurrentPrice();
        if ($this->price != $currentPrice) {
            $this->update(['price' => $currentPrice]);
        }
    }
}
