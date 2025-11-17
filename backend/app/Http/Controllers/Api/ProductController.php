<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products with filters
     */
    public function index(Request $request)
    {
        $query = Product::with(['vendor', 'category', 'brand', 'primaryImage'])
            ->active();

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by brand
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by vendor
        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter featured products
        if ($request->has('featured') && $request->featured) {
            $query->featured();
        }

        // Filter in stock only
        if ($request->has('in_stock') && $request->in_stock) {
            $query->inStock();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Display the specified product
     */
    public function show($id)
    {
        $product = Product::with([
            'vendor',
            'category',
            'brand',
            'images',
            'variants',
            'reviews' => function ($query) {
                $query->approved()->latest()->limit(10);
            },
        ])->findOrFail($id);

        // Increment views
        $product->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Store a newly created product (Vendor only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'sku' => 'required|string|unique:products',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
        ]);

        $product = Product::create(array_merge(
            $request->all(),
            ['vendor_id' => $request->user()->vendor->id ?? null]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Authorization check (vendor can only update their own products)
        if ($product->vendor_id !== $request->user()->vendor?->id && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'name' => 'string|max:255',
            'category_id' => 'exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'string',
            'price' => 'numeric|min:0',
            'stock_quantity' => 'integer|min:0',
        ]);

        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Get featured products
     */
    public function featured()
    {
        $products = Product::with(['vendor', 'category', 'primaryImage'])
            ->active()
            ->featured()
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get related products
     */
    public function related($id)
    {
        $product = Product::findOrFail($id);

        $related = Product::with(['vendor', 'category', 'primaryImage'])
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $related,
        ]);
    }
}
