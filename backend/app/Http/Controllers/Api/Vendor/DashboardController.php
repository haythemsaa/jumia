<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get vendor dashboard statistics
     */
    public function index(Request $request): JsonResponse
    {
        $vendor = $request->user();
        $period = $request->input('period', '30days');
        $days = $this->getPeriodDays($period);

        $stats = [
            'overview' => $this->getOverviewStats($vendor, $days),
            'products' => $this->getProductStats($vendor),
            'orders' => $this->getOrderStats($vendor, $days),
            'revenue' => $this->getRevenueStats($vendor, $days),
            'commissions' => $this->getCommissionStats($vendor, $days),
            'top_products' => $this->getTopProducts($vendor, $days),
            'recent_orders' => $this->getRecentOrders($vendor),
            'charts' => [
                'sales' => $this->getSalesChart($vendor, $days),
            ],
        ];

        return response()->json($stats);
    }

    protected function getOverviewStats($vendor, int $days): array
    {
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        $totalRevenue = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('order_items.product_id', $productIds)
            ->where('orders.created_at', '>=', now()->subDays($days))
            ->where('orders.status', 'completed')
            ->sum(DB::raw('order_items.quantity * order_items.price'));

        $totalOrders = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('order_items.product_id', $productIds)
            ->where('orders.created_at', '>=', now()->subDays($days))
            ->distinct('orders.id')
            ->count('orders.id');

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'total_products' => Product::where('vendor_id', $vendor->id)->count(),
            'average_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
        ];
    }

    protected function getProductStats($vendor): array
    {
        return [
            'total' => Product::where('vendor_id', $vendor->id)->count(),
            'active' => Product::where('vendor_id', $vendor->id)
                ->where('status', 'active')
                ->count(),
            'out_of_stock' => Product::where('vendor_id', $vendor->id)
                ->where('stock', 0)
                ->count(),
        ];
    }

    protected function getOrderStats($vendor, int $days): array
    {
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        return [
            'pending' => DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('order_items.product_id', $productIds)
                ->where('orders.status', 'pending')
                ->distinct('orders.id')
                ->count('orders.id'),
            'completed' => DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('order_items.product_id', $productIds)
                ->where('orders.status', 'completed')
                ->where('orders.created_at', '>=', now()->subDays($days))
                ->distinct('orders.id')
                ->count('orders.id'),
        ];
    }

    protected function getRevenueStats($vendor, int $days): array
    {
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        $currentPeriod = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('order_items.product_id', $productIds)
            ->where('orders.status', 'completed')
            ->where('orders.created_at', '>=', now()->subDays($days))
            ->sum(DB::raw('order_items.quantity * order_items.price'));

        $previousPeriod = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('order_items.product_id', $productIds)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [
                now()->subDays($days * 2),
                now()->subDays($days)
            ])
            ->sum(DB::raw('order_items.quantity * order_items.price'));

        $growth = $previousPeriod > 0 
            ? (($currentPeriod - $previousPeriod) / $previousPeriod) * 100 
            : 100;

        return [
            'current_period' => $currentPeriod,
            'previous_period' => $previousPeriod,
            'growth_percentage' => round($growth, 2),
        ];
    }

    protected function getCommissionStats($vendor, int $days): array
    {
        return [
            'pending' => 0, // Will be calculated from vendor_commissions table
            'paid' => 0,
            'total_earned' => 0,
        ];
    }

    protected function getTopProducts($vendor, int $days, int $limit = 10): array
    {
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->whereIn('order_items.product_id', $productIds)
            ->where('orders.created_at', '>=', now()->subDays($days))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    protected function getRecentOrders($vendor, int $limit = 10): array
    {
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        return DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select(
                'orders.id',
                'orders.created_at',
                'orders.status',
                'orders.total',
                'users.name as customer_name'
            )
            ->whereIn('order_items.product_id', $productIds)
            ->distinct('orders.id')
            ->latest('orders.created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    protected function getSalesChart($vendor, int $days): array
    {
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        $sales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('order_items.product_id', $productIds)
            ->where('orders.status', 'completed')
            ->where('orders.created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->orderBy('date')
            ->get();

        return $sales->map(function($item) {
            return [
                'date' => $item->date,
                'revenue' => (float) $item->revenue,
                'orders' => $item->order_count,
            ];
        })->toArray();
    }

    protected function getPeriodDays(string $period): int
    {
        return match($period) {
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            'year' => 365,
            default => 30,
        };
    }
}
