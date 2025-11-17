<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Helper methods
    public static function toggle($userId, $productId)
    {
        $wishlist = self::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return ['added' => false, 'message' => 'Removed from wishlist'];
        }

        self::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return ['added' => true, 'message' => 'Added to wishlist'];
    }

    public static function exists($userId, $productId)
    {
        return self::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    public static function getUserWishlist($userId)
    {
        return self::where('user_id', $userId)
            ->with('product.primaryImage', 'product.vendor')
            ->get();
    }
}
