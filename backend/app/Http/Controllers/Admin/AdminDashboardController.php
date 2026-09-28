<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserPlant;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the botanical admin dashboard.
     */
    public function index(): View
    {
        $totalRevenue = Order::whereNotIn('status', ['cancelled'])->sum('total_amount');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $inTransitOrders = Order::where('status', 'transit')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();

        // Weather dispatch holds: pending orders where override hasn't been set
        $weatherAlertHolds = Order::where('status', 'pending')
            ->where('dispatch_weather_alert_override', false)
            ->count();

        // Inventory health
        $lowStockVariants = ProductVariant::with('product')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderBy('stock_quantity', 'asc')
            ->take(8)
            ->get();

        $totalLowStockCount = ProductVariant::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $outOfStockCount = ProductVariant::where('stock_quantity', '<=', 0)->count();

        // Products & Categories
        $totalProducts = Product::count();
        $publishedProducts = Product::where('is_published', true)->count();
        $totalCategories = Category::count();
        $totalCustomers = User::where('is_admin', false)->count();
        $totalAdoptedPlants = UserPlant::count();

        // Recent orders
        $recentOrders = Order::with(['items.product', 'user'])
            ->latest()
            ->take(6)
            ->get();

        // Status breakdown for donut chart
        $statusBreakdown = [
            'Pending' => $pendingOrders,
            'Processing' => $processingOrders,
            'In Transit' => $inTransitOrders,
            'Delivered' => $deliveredOrders,
            'Cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        // Revenue trends for last 6 months
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthLabel = $month->format('M Y');
            $revenue = Order::whereNotIn('status', ['cancelled'])
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_amount');

            $monthlyRevenue[$monthLabel] = (float) $revenue;
        }

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'pendingOrders',
            'processingOrders',
            'inTransitOrders',
            'deliveredOrders',
            'weatherAlertHolds',
            'lowStockVariants',
            'totalLowStockCount',
            'outOfStockCount',
            'totalProducts',
            'publishedProducts',
            'totalCategories',
            'totalCustomers',
            'totalAdoptedPlants',
            'recentOrders',
            'statusBreakdown',
            'monthlyRevenue'
        ));
    }
}
