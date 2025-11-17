<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // Helper methods
    public function addItem($product, $quantity = 1, $variantId = null)
    {
        $price = $variantId
            ? ProductVariant::find($variantId)->price
            : $product->price;

        return $this->items()->updateOrCreate(
            [
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
            ],
            [
                'quantity' => \DB::raw("quantity + {$quantity}"),
                'price' => $price,
            ]
        );
    }

    public function removeItem($itemId)
    {
        return $this->items()->where('id', $itemId)->delete();
    }

    public function updateItemQuantity($itemId, $quantity)
    {
        if ($quantity <= 0) {
            return $this->removeItem($itemId);
        }

        return $this->items()->where('id', $itemId)->update(['quantity' => $quantity]);
    }

    public function clear()
    {
        return $this->items()->delete();
    }

    public function getSubtotal()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getTotalItems()
    {
        return $this->items->sum('quantity');
    }

    public function isEmpty()
    {
        return $this->items->count() === 0;
    }
}
