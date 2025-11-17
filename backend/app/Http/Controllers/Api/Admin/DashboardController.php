<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function index(Request $request): JsonResponse
    {
        $period = $request->input('period', '30days');

        $stats = [
            'overview' => $this->getOverviewStats($period),
            'revenue' => $this->getRevenueStats($period),
            'orders' => $this->getOrderStats($period),
            'products' => $this->getProductStats(),
            'users' => $this->getUserStats($period),
            'top_products' => $this->getTopProducts($period),
            'top_vendors' => $this->getTopVendors($period),
            'charts' => [
                'sales' => $this->getSalesChart($period),
            ],
        ];

        return response()->json($stats);
    }

    protected function getOverviewStats(string $period): array
    {
        $days = $this->getPeriodDays($period);
        $startDate = now()->subDays($days);

        return [
            'total_revenue' => Order::where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->sum('total'),
            'total_orders' => Order::where('created_at', '>=', $startDate)->count(),
            'total_customers' => User::where('role', 'client')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'average_order_value' => Order::where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->avg('total'),
        ];
    }

    protected function getRevenueStats(string $period): array
    {
        $days = $this->getPeriodDays($period);

        $currentPeriod = Order::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->sum('total');

        $previousPeriod = Order::where('status', 'completed')
            ->whereBetween('created_at', [
                now()->subDays($days * 2),
                now()->subDays($days)
            ])
            ->sum('total');

        $growth = $previousPeriod > 0 
            ? (($currentPeriod - $previousPeriod) / $previousPeriod) * 100 
            : 100;

        return [
            'current_period' => $currentPeriod,
            'previous_period' => $previousPeriod,
            'growth_percentage' => round($growth, 2),
        ];
    }

    protected function getOrderStats(string $period): array
    {
        $days = $this->getPeriodDays($period);
        $startDate = now()->subDays($days);

        return [
            'total' => Order::where('created_at', '>=', $startDate)->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')
                ->where('created_at', '>=', $startDate)
                ->count(),
        ];
    }

    protected function getProductStats(): array
    {
        return [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'out_of_stock' => Product::where('stock', 0)->count(),
        ];
    }

    protected function getUserStats(string $period): array
    {
        $days = $this->getPeriodDays($period);

        return [
            'total_users' => User::count(),
            'new_users' => User::where('created_at', '>=', now()->subDays($days))->count(),
            'vendors' => User::where('role', 'vendor')->count(),
            'customers' => User::where('role', 'client')->count(),
        ];
    }

    protected function getTopProducts(string $period, int $limit = 10): array
    {
        $days = $this->getPeriodDays($period);

        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->where('orders.created_at', '>=', now()->subDays($days))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    protected function getTopVendors(string $period, int $limit = 10): array
    {
        $days = $this->getPeriodDays($period);

        return DB::table('users')
            ->join('products', 'users.id', '=', 'products.vendor_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->where('orders.created_at', '>=', now()->subDays($days))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    protected function getSalesChart(string $period): array
    {
        $days = $this->getPeriodDays($period);

        $sales = Order::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as order_count')
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
