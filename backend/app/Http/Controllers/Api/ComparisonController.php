<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductComparison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComparisonController extends Controller
{
    /**
     * Get comparison list for user/session
     */
    public function index(Request $request)
    {
        $comparison = $this->getOrCreateComparison($request);

        if (!$comparison) {
            return response()->json([
                'success' => true,
                'products' => [],
                'count' => 0,
            ]);
        }

        $products = Product::whereIn('id', $comparison->product_ids ?? [])
            ->with(['category', 'vendor', 'primaryImage', 'reviews'])
            ->get();

        return response()->json([
            'success' => true,
            'products' => $products,
            'count' => $products->count(),
        ]);
    }

    /**
     * Add product to comparison
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $comparison = $this->getOrCreateComparison($request);

        // Limit to max 4 products
        if ($comparison->getProductCount() >= 4) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez comparer que 4 produits maximum',
            ], 400);
        }

        $product = Product::findOrFail($request->product_id);

        if ($comparison->hasProduct($product->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce produit est déjà dans votre comparaison',
            ], 409);
        }

        $comparison->addProduct($product->id);

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté à la comparaison',
            'count' => $comparison->getProductCount(),
        ]);
    }

    /**
     * Remove product from comparison
     */
    public function remove(Request $request, $productId)
    {
        $comparison = $this->getOrCreateComparison($request);

        if (!$comparison->hasProduct($productId)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce produit n\'est pas dans votre comparaison',
            ], 404);
        }

        $comparison->removeProduct($productId);

        return response()->json([
            'success' => true,
            'message' => 'Produit retiré de la comparaison',
            'count' => $comparison->getProductCount(),
        ]);
    }

    /**
     * Clear all products from comparison
     */
    public function clear(Request $request)
    {
        $comparison = $this->getOrCreateComparison($request);

        if ($comparison) {
            $comparison->clear();
        }

        return response()->json([
            'success' => true,
            'message' => 'Comparaison vidée',
        ]);
    }

    /**
     * Compare products with detailed attributes
     */
    public function compare(Request $request)
    {
        $comparison = $this->getOrCreateComparison($request);

        if (!$comparison || $comparison->getProductCount() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez avoir au moins 2 produits pour les comparer',
            ], 400);
        }

        $products = Product::whereIn('id', $comparison->product_ids)
            ->with(['category', 'vendor', 'images', 'reviews'])
            ->get();

        // Build comparison matrix
        $comparisonData = [
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'compare_price' => $product->compare_price,
                    'rating' => $product->rating,
                    'reviews_count' => $product->total_reviews,
                    'category' => $product->category->name ?? 'N/A',
                    'vendor' => $product->vendor->shop_name ?? 'N/A',
                    'in_stock' => $product->isInStock(),
                    'image' => $product->primaryImage->url ?? null,
                    'brand' => $product->brand->name ?? 'N/A',
                    'weight' => $product->weight,
                    'dimensions' => $product->dimensions,
                    'meta_data' => $product->meta_data,
                ];
            }),
            'attributes' => $this->getCommonAttributes($products),
            'highlights' => $this->getHighlights($products),
        ];

        return response()->json([
            'success' => true,
            'comparison' => $comparisonData,
        ]);
    }

    /**
     * Get or create comparison for user/session
     */
    private function getOrCreateComparison(Request $request): ProductComparison
    {
        if (auth()->check()) {
            return ProductComparison::firstOrCreate(
                ['user_id' => auth()->id()],
                ['product_ids' => []]
            );
        }

        // For guest users, use session ID
        $sessionId = $request->session()->getId();

        return ProductComparison::firstOrCreate(
            ['session_id' => $sessionId],
            ['product_ids' => []]
        );
    }

    /**
     * Get common attributes across products
     */
    private function getCommonAttributes($products): array
    {
        $attributes = [
            'Prix' => $products->pluck('price')->map(fn($price) => number_format($price, 2) . ' TND')->toArray(),
            'Note' => $products->pluck('rating')->map(fn($rating) => $rating . '/5')->toArray(),
            'Avis' => $products->pluck('total_reviews')->map(fn($count) => $count . ' avis')->toArray(),
            'Vendeur' => $products->map(fn($p) => $p->vendor->shop_name ?? 'N/A')->toArray(),
            'Catégorie' => $products->map(fn($p) => $p->category->name ?? 'N/A')->toArray(),
            'En stock' => $products->map(fn($p) => $p->isInStock() ? 'Oui' : 'Non')->toArray(),
        ];

        // Add weight if all products have it
        if ($products->every(fn($p) => $p->weight)) {
            $attributes['Poids'] = $products->pluck('weight')->map(fn($w) => $w . ' kg')->toArray();
        }

        return $attributes;
    }

    /**
     * Get highlights (best values) for comparison
     */
    private function getHighlights($products): array
    {
        return [
            'best_price' => $products->min('price'),
            'best_rating' => $products->max('rating'),
            'most_reviewed' => $products->max('total_reviews'),
            'lowest_price_id' => $products->sortBy('price')->first()->id,
            'highest_rating_id' => $products->sortByDesc('rating')->first()->id,
        ];
    }

    /**
     * Check if product is in comparison
     */
    public function check(Request $request, $productId)
    {
        $comparison = $this->getOrCreateComparison($request);

        return response()->json([
            'success' => true,
            'in_comparison' => $comparison->hasProduct($productId),
            'count' => $comparison->getProductCount(),
        ]);
    }
}
