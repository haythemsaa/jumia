<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    const PRODUCT_TTL = 3600; // 1 hour
    const CATEGORY_TTL = 7200; // 2 hours
    const USER_TTL = 1800; // 30 minutes
    const FLASH_SALE_TTL = 300; // 5 minutes
    const LOYALTY_TIER_TTL = 86400; // 24 hours
    const CART_TTL = 3600; // 1 hour

    /**
     * Get product with caching
     */
    public function getProduct(int $productId): mixed
    {
        return Cache::remember(
            "product:{$productId}",
            self::PRODUCT_TTL,
            fn() => \App\Models\Product::with(['category', 'vendor', 'reviews'])
                ->find($productId)
        );
    }

    /**
     * Get products list with caching
     */
    public function getProducts(array $filters = []): mixed
    {
        $cacheKey = 'products:' . md5(serialize($filters));

        return Cache::remember(
            $cacheKey,
            self::PRODUCT_TTL,
            fn() => \App\Models\Product::query()
                ->when($filters['category_id'] ?? null, fn($q, $v) => $q->where('category_id', $v))
                ->when($filters['search'] ?? null, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
                ->where('status', 'active')
                ->paginate($filters['per_page'] ?? 15)
        );
    }

    /**
     * Get category with caching
     */
    public function getCategory(int $categoryId): mixed
    {
        return Cache::remember(
            "category:{$categoryId}",
            self::CATEGORY_TTL,
            fn() => \App\Models\Category::with('children')->find($categoryId)
        );
    }

    /**
     * Get all categories with caching
     */
    public function getCategories(): mixed
    {
        return Cache::remember(
            'categories:all',
            self::CATEGORY_TTL,
            fn() => \App\Models\Category::whereNull('parent_id')
                ->with('children')
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * Get active flash sales with caching
     */
    public function getActiveFlashSales(): mixed
    {
        return Cache::remember(
            'flash_sales:active',
            self::FLASH_SALE_TTL,
            fn() => \App\Models\FlashSale::where('status', 'active')
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->with(['products.product'])
                ->get()
        );
    }

    /**
     * Get loyalty tiers with caching
     */
    public function getLoyaltyTiers(): mixed
    {
        return Cache::remember(
            'loyalty_tiers:all',
            self::LOYALTY_TIER_TTL,
            fn() => \App\Models\LoyaltyTier::orderBy('min_points')->get()
        );
    }

    /**
     * Get user's cart with caching
     */
    public function getUserCart(int $userId): mixed
    {
        return Cache::remember(
            "cart:user:{$userId}",
            self::CART_TTL,
            fn() => \App\Models\Cart::with(['items.product'])
                ->where('user_id', $userId)
                ->first()
        );
    }

    /**
     * Invalidate product cache
     */
    public function invalidateProduct(int $productId): void
    {
        Cache::forget("product:{$productId}");
        Cache::tags(['products'])->flush();
    }

    /**
     * Invalidate category cache
     */
    public function invalidateCategory(int $categoryId): void
    {
        Cache::forget("category:{$categoryId}");
        Cache::forget('categories:all');
    }

    /**
     * Invalidate user cart cache
     */
    public function invalidateUserCart(int $userId): void
    {
        Cache::forget("cart:user:{$userId}");
    }

    /**
     * Invalidate flash sales cache
     */
    public function invalidateFlashSales(): void
    {
        Cache::forget('flash_sales:active');
    }

    /**
     * Get popular products (Redis sorted set)
     */
    public function getPopularProducts(int $limit = 10): array
    {
        $products = Redis::zrevrange('popular_products', 0, $limit - 1, 'WITHSCORES');
        $result = [];

        for ($i = 0; $i < count($products); $i += 2) {
            $result[] = [
                'product_id' => $products[$i],
                'views' => $products[$i + 1]
            ];
        }

        return $result;
    }

    /**
     * Increment product view count
     */
    public function incrementProductView(int $productId): void
    {
        Redis::zincrby('popular_products', 1, $productId);
    }

    /**
     * Cache API response
     */
    public function cacheApiResponse(string $key, mixed $data, int $ttl = 300): mixed
    {
        return Cache::remember($key, $ttl, fn() => $data);
    }

    /**
     * Clear all application cache
     */
    public function clearAll(): void
    {
        Cache::flush();
        Redis::flushdb();
    }

    /**
     * Warm up critical caches
     */
    public function warmUp(): void
    {
        // Cache categories
        $this->getCategories();

        // Cache loyalty tiers
        $this->getLoyaltyTiers();

        // Cache active flash sales
        $this->getActiveFlashSales();

        // Cache popular products
        $popularProducts = \App\Models\Product::where('status', 'active')
            ->orderBy('views', 'desc')
            ->limit(50)
            ->get();

        foreach ($popularProducts as $product) {
            $this->getProduct($product->id);
        }
    }
}
