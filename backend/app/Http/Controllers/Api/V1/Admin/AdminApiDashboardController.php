<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserPlant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class AdminApiDashboardController extends Controller
{
    /**
     * Return summary statistics and trends for the admin dashboard.
     */
    public function stats(): JsonResponse
    {
        $totalRevenue = (float) Order::whereNotIn('status', ['cancelled'])->sum('total_amount');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $inTransitOrders = Order::where('status', 'transit')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $weatherAlertHolds = Order::where('status', 'pending')
            ->where('dispatch_weather_alert_override', false)
            ->count();

        $lowStockVariants = ProductVariant::with('product')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderBy('stock_quantity', 'asc')
            ->take(10)
            ->get();

        $totalLowStockCount = ProductVariant::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $outOfStockCount = ProductVariant::where('stock_quantity', '<=', 0)->count();

        // Monthly trends
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthLabel = $month->format('M Y');
            $rev = Order::whereNotIn('status', ['cancelled'])
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_amount');

            $monthlyRevenue[] = [
                'month' => $monthLabel,
                'revenue' => (float) $rev,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'financials' => [
                    'total_revenue' => $totalRevenue,
                ],
                'orders' => [
                    'total' => $totalOrders,
                    'pending' => $pendingOrders,
                    'processing' => $processingOrders,
                    'transit' => $inTransitOrders,
                    'delivered' => $deliveredOrders,
                    'cancelled' => Order::where('status', 'cancelled')->count(),
                    'weather_alert_holds' => $weatherAlertHolds,
                ],
                'inventory' => [
                    'total_products' => Product::count(),
                    'published_products' => Product::where('is_published', true)->count(),
                    'total_categories' => Category::count(),
                    'total_variants' => ProductVariant::count(),
                    'low_stock_count' => $totalLowStockCount,
                    'out_of_stock_count' => $outOfStockCount,
                    'critical_stock_alerts' => $lowStockVariants,
                ],
                'customers' => [
                    'total_customers' => User::where('is_admin', false)->count(),
                    'total_adopted_plants' => UserPlant::count(),
                ],
                'monthly_revenue' => $monthlyRevenue,
            ],
        ]);
    }
}
