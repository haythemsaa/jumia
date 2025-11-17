<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\ProductView;
use App\Models\ProductRecommendation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RecommendationService
{
    /**
     * Get personalized product recommendations for a user
     */
    public function getPersonalizedRecommendations(int $userId, int $limit = 10): array
    {
        return Cache::remember("user_recommendations_{$userId}", 3600, function() use ($userId, $limit) {
            // Get user's order history
            $purchasedProductIds = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.user_id', $userId)
                ->pluck('order_items.product_id')
                ->toArray();

            // Get collaborative filtering recommendations
            $collaborative = $this->getCollaborativeRecommendations($userId, $purchasedProductIds, $limit);

            // Get content-based recommendations
            $contentBased = $this->getContentBasedRecommendations($purchasedProductIds, $limit);

            // Merge and deduplicate
            $recommendations = collect($collaborative)
                ->merge($contentBased)
                ->unique('id')
                ->take($limit)
                ->values()
                ->toArray();

            return $recommendations;
        });
    }

    /**
     * Collaborative filtering: Users who bought this also bought...
     */
    protected function getCollaborativeRecommendations(int $userId, array $purchasedProductIds, int $limit): array
    {
        if (empty($purchasedProductIds)) {
            return [];
        }

        // Find users who bought similar products
        $similarUsers = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('order_items.product_id', $purchasedProductIds)
            ->where('orders.user_id', '!=', $userId)
            ->select('orders.user_id')
            ->distinct()
            ->limit(100)
            ->pluck('user_id');

        if ($similarUsers->isEmpty()) {
            return [];
        }

        // Get products bought by similar users
        $recommendedProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('orders.user_id', $similarUsers)
            ->whereNotIn('order_items.product_id', $purchasedProductIds)
            ->where('products.status', 'active')
            ->where('products.stock', '>', 0)
            ->select(
                'products.*',
                DB::raw('COUNT(*) as purchase_count')
            )
            ->groupBy('products.id')
            ->orderByDesc('purchase_count')
            ->limit($limit)
            ->get()
            ->toArray();

        return $recommendedProducts;
    }

    /**
     * Content-based: Products similar to what user viewed/bought
     */
    protected function getContentBasedRecommendations(array $productIds, int $limit): array
    {
        if (empty($productIds)) {
            return [];
        }

        // Get categories of user's products
        $categories = Product::whereIn('id', $productIds)
            ->pluck('category_id')
            ->unique()
            ->toArray();

        // Find similar products in same categories
        $recommendations = Product::whereIn('category_id', $categories)
            ->whereNotIn('id', $productIds)
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->limit($limit)
            ->get()
            ->toArray();

        return $recommendations;
    }

    /**
     * Get frequently bought together products
     */
    public function getFrequentlyBoughtTogether(int $productId, int $limit = 4): array
    {
        return Cache::remember("fbt_product_{$productId}", 7200, function() use ($productId, $limit) {
            // Find orders containing this product
            $orderIds = DB::table('order_items')
                ->where('product_id', $productId)
                ->pluck('order_id');

            if ($orderIds->isEmpty()) {
                return [];
            }

            // Find other products in those orders
            $frequentProducts = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereIn('order_items.order_id', $orderIds)
                ->where('order_items.product_id', '!=', $productId)
                ->where('products.status', 'active')
                ->where('products.stock', '>', 0)
                ->select(
                    'products.*',
                    DB::raw('COUNT(*) as frequency')
                )
                ->groupBy('products.id')
                ->orderByDesc('frequency')
                ->limit($limit)
                ->get()
                ->toArray();

            return $frequentProducts;
        });
    }

    /**
     * Get similar products based on category and attributes
     */
    public function getSimilarProducts(int $productId, int $limit = 6): array
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return [];
        }

        return Product::where('category_id', $product->category_id)
            ->where('id', '!=', $productId)
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->whereBetween('price', [
                $product->price * 0.5,
                $product->price * 1.5
            ])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Track product view for recommendations
     */
    public function trackProductView(int $productId, ?int $userId, string $sessionId): void
    {
        ProductView::create([
            'product_id' => $productId,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'viewed_at' => now(),
        ]);

        // Increment product view count in Redis
        app(CacheService::class)->incrementProductView($productId);
    }

    /**
     * Get trending products
     */
    public function getTrendingProducts(int $limit = 10): array
    {
        return Cache::remember('trending_products', 600, function() use ($limit) {
            $popularProductIds = app(CacheService::class)->getPopularProducts($limit * 2);

            if (empty($popularProductIds)) {
                return [];
            }

            $ids = array_column($popularProductIds, 'product_id');

            return Product::whereIn('id', $ids)
                ->where('status', 'active')
                ->where('stock', '>', 0)
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->get()
                ->sortByDesc(function($product) use ($popularProductIds) {
                    $key = array_search($product->id, array_column($popularProductIds, 'product_id'));
                    return $popularProductIds[$key]['views'] ?? 0;
                })
                ->take($limit)
                ->values()
                ->toArray();
        });
    }
}
