<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ReviewResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Get reviews for a product
     */
    public function index(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $query = $product->reviews()->approved()->with(['user', 'vendorResponse']);

        // Filter by rating
        if ($request->has('rating')) {
            $query->byRating($request->rating);
        }

        // Filter verified purchases only
        if ($request->boolean('verified_only')) {
            $query->verified();
        }

        // Sort
        $sortBy = $request->input('sort_by', 'recent');
        switch ($sortBy) {
            case 'helpful':
                $query->orderBy('helpful_count', 'desc');
                break;
            case 'rating_high':
                $query->orderBy('rating', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('rating', 'asc');
                break;
            default:
                $query->latest();
        }

        $reviews = $query->paginate(10);

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
            'summary' => [
                'average_rating' => $product->rating,
                'total_reviews' => $product->reviews_count,
                'rating_breakdown' => $this->getRatingBreakdown($product),
            ],
        ]);
    }

    /**
     * Create a new review
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:200',
            'comment' => 'required|string|max:1000',
            'images.*' => 'nullable|image|max:2048', // Max 2MB per image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        // Verify user has ordered this product
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $user->id)
            ->whereHas('items', function ($query) use ($request) {
                $query->where('product_id', $request->product_id);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez avoir acheté ce produit pour laisser un avis',
            ], 403);
        }

        // Check if user already reviewed this product
        $existingReview = ProductReview::where('product_id', $request->product_id)
            ->where('user_id', $user->id)
            ->where('order_id', $request->order_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà laissé un avis pour ce produit',
            ], 409);
        }

        // Upload images if provided
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $imagePaths[] = $path;
            }
        }

        // Determine if it's a verified purchase
        $isVerifiedPurchase = in_array($order->status, ['delivered', 'completed']);

        // Create review
        $review = ProductReview::create([
            'product_id' => $request->product_id,
            'user_id' => $user->id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'images' => $imagePaths,
            'is_verified_purchase' => $isVerifiedPurchase,
            'status' => config('reviews.auto_approve', false) ? 'approved' : 'pending',
        ]);

        // Auto-approve and update product rating if enabled
        if ($review->status === 'approved') {
            $review->product->updateRating();
        }

        return response()->json([
            'success' => true,
            'message' => $review->status === 'approved'
                ? 'Votre avis a été publié avec succès'
                : 'Votre avis est en cours de modération',
            'review' => $review,
        ], 201);
    }

    /**
     * Mark review as helpful
     */
    public function markAsHelpful($reviewId)
    {
        $review = ProductReview::findOrFail($reviewId);
        $review->markAsHelpful();

        return response()->json([
            'success' => true,
            'helpful_count' => $review->helpful_count,
        ]);
    }

    /**
     * Vendor responds to a review
     */
    public function respond(Request $request, $reviewId)
    {
        $validator = Validator::make($request->all(), [
            'response' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $review = ProductReview::findOrFail($reviewId);
        $user = auth()->user();

        // Check if user is the vendor of this product
        if ($review->product->vendor_id !== $user->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez répondre qu\'aux avis de vos propres produits',
            ], 403);
        }

        // Check if vendor already responded
        if ($review->vendorResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà répondu à cet avis',
            ], 409);
        }

        $response = ReviewResponse::create([
            'product_review_id' => $review->id,
            'vendor_id' => $user->vendor->id,
            'response' => $request->response,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Votre réponse a été publiée',
            'response' => $response,
        ], 201);
    }

    /**
     * Get user's reviews
     */
    public function userReviews()
    {
        $user = auth()->user();

        $reviews = ProductReview::where('user_id', $user->id)
            ->with(['product', 'vendorResponse'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Admin: Approve review
     */
    public function approve($reviewId)
    {
        $review = ProductReview::findOrFail($reviewId);
        $review->approve();

        return response()->json([
            'success' => true,
            'message' => 'Avis approuvé',
        ]);
    }

    /**
     * Admin: Reject review
     */
    public function reject($reviewId)
    {
        $review = ProductReview::findOrFail($reviewId);
        $review->reject();

        return response()->json([
            'success' => true,
            'message' => 'Avis rejeté',
        ]);
    }

    /**
     * Admin: Get pending reviews
     */
    public function pending()
    {
        $reviews = ProductReview::pending()
            ->with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Get rating breakdown for a product
     */
    private function getRatingBreakdown(Product $product): array
    {
        $breakdown = [];

        for ($i = 5; $i >= 1; $i--) {
            $count = $product->reviews()->approved()->byRating($i)->count();
            $percentage = $product->reviews_count > 0
                ? round(($count / $product->reviews_count) * 100)
                : 0;

            $breakdown[$i] = [
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return $breakdown;
    }

    /**
     * Delete review (user can delete their own review within 24h)
     */
    public function destroy($reviewId)
    {
        $user = auth()->user();
        $review = ProductReview::findOrFail($reviewId);

        // Only user who created the review can delete it
        if ($review->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        // Allow deletion only within 24 hours
        if ($review->created_at->diffInHours(now()) > 24) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez supprimer un avis que dans les 24 heures suivant sa publication',
            ], 403);
        }

        // Delete images
        if ($review->images) {
            foreach ($review->images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $review->delete();

        // Update product rating
        $review->product->updateRating();

        return response()->json([
            'success' => true,
            'message' => 'Avis supprimé',
        ]);
    }
}
