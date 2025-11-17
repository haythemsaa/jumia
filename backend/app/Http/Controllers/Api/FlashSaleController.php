<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use Illuminate\Http\Request;

class FlashSaleController extends Controller
{
    /**
     * Get active flash sales
     */
    public function index()
    {
        $flashSales = FlashSale::active()
            ->with(['products.primaryImage', 'flashSaleProducts'])
            ->get();

        $flashSales->transform(function ($sale) {
            $sale->time_remaining = $sale->getTimeRemaining();
            return $sale;
        });

        return response()->json([
            'success' => true,
            'flash_sales' => $flashSales,
        ]);
    }

    /**
     * Get upcoming flash sales
     */
    public function upcoming()
    {
        $flashSales = FlashSale::upcoming()
            ->with(['products.primaryImage'])
            ->orderBy('starts_at')
            ->get();

        return response()->json([
            'success' => true,
            'upcoming_flash_sales' => $flashSales,
        ]);
    }

    /**
     * Get flash sale details
     */
    public function show($id)
    {
        $user = auth()->user();
        $flashSale = FlashSale::with(['flashSaleProducts.product.primaryImage'])->findOrFail($id);

        if (!$flashSale->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette vente flash n\'est pas active',
            ], 400);
        }

        // Check if user can access (based on tier)
        if ($user && !$flashSale->canUserAccess($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Votre niveau de fidélité ne permet pas d\'accéder à cette vente flash',
            ], 403);
        }

        $flashSale->time_remaining = $flashSale->getTimeRemaining();

        return response()->json([
            'success' => true,
            'flash_sale' => $flashSale,
        ]);
    }

    /**
     * Check if user can purchase flash sale product
     */
    public function checkEligibility(Request $request, $flashSaleProductId)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise',
            ], 401);
        }

        $flashSaleProduct = FlashSaleProduct::with('flashSale')->findOrFail($flashSaleProductId);

        if (!$flashSaleProduct->flashSale->isActive()) {
            return response()->json([
                'success' => false,
                'eligible' => false,
                'message' => 'La vente flash n\'est pas active',
            ]);
        }

        if (!$flashSaleProduct->flashSale->canUserAccess($user)) {
            return response()->json([
                'success' => false,
                'eligible' => false,
                'message' => 'Niveau de fidélité insuffisant',
            ]);
        }

        $quantity = $request->input('quantity', 1);
        $canPurchase = $flashSaleProduct->canPurchase($user, $quantity);

        return response()->json([
            'success' => true,
            'eligible' => $canPurchase,
            'remaining_stock' => $flashSaleProduct->getRemainingStock(),
            'max_per_customer' => $flashSaleProduct->max_per_customer,
        ]);
    }
}
