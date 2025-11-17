<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist
     */
    public function index(Request $request): JsonResponse
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->with(['product' => function($query) {
                $query->with(['category', 'vendor'])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews');
            }])
            ->latest()
            ->paginate(20);

        return response()->json($wishlist);
    }

    /**
     * Add product to wishlist
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $product = Product::findOrFail($request->product_id);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        if ($wishlist->wasRecentlyCreated) {
            return response()->json([
                'success' => true,
                'message' => __('messages.wishlist_added'),
                'data' => $wishlist->load('product')
            ], 201);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.already_in_wishlist'),
            'data' => $wishlist->load('product')
        ]);
    }

    /**
     * Remove product from wishlist
     */
    public function remove(Request $request, int $productId): JsonResponse
    {
        $deleted = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => __('messages.wishlist_removed')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.not_in_wishlist')
        ], 404);
    }

    /**
     * Check if product is in wishlist
     */
    public function check(Request $request, int $productId): JsonResponse
    {
        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'in_wishlist' => $exists
        ]);
    }

    /**
     * Clear entire wishlist
     */
    public function clear(Request $request): JsonResponse
    {
        $count = Wishlist::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.wishlist_cleared'),
            'count' => $count
        ]);
    }

    /**
     * Move wishlist items to cart
     */
    public function moveToCart(Request $request): JsonResponse
    {
        $user = $request->user();
        $cart = $user->cart ?? \App\Models\Cart::create(['user_id' => $user->id]);

        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->with('product')
            ->get();

        $addedCount = 0;
        $errors = [];

        foreach ($wishlistItems as $item) {
            if ($item->product->stock > 0 && $item->product->status === 'active') {
                \App\Models\CartItem::firstOrCreate(
                    [
                        'cart_id' => $cart->id,
                        'product_id' => $item->product_id,
                    ],
                    [
                        'quantity' => 1,
                        'price' => $item->product->price,
                    ]
                );
                $addedCount++;
            } else {
                $errors[] = $item->product->name . ' is out of stock';
            }
        }

        // Clear wishlist after moving to cart
        if ($addedCount > 0) {
            Wishlist::where('user_id', $user->id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => "{$addedCount} items moved to cart",
            'added_count' => $addedCount,
            'errors' => $errors
        ]);
    }
}
