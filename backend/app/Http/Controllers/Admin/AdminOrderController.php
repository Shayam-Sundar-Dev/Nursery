<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View
    {
        $query = Order::with(['items.product', 'user']);

        // Search by order number, customer name, email, or tracking code
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('tracking_code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by weather override alert
        if ($request->has('weather_hold') && $request->input('weather_hold') !== '') {
            $query->where('dispatch_weather_alert_override', ! $request->boolean('weather_hold'))
                ->where('status', 'pending');
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        $statusCounts = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'transit' => Order::where('status', 'transit')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'weather_hold' => Order::where('status', 'pending')->where('dispatch_weather_alert_override', false)->count(),
        ];

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * Display the specified order details.
     */
    public function show(Order $order): View
    {
        $order->load(['items.product.plantAttributes', 'items.variant', 'user.userPlants']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the order status, carrier, tracking code, and weather override.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,transit,delivered,cancelled'],
            'carrier_name' => ['nullable', 'string', 'max:100'],
            'tracking_code' => ['nullable', 'string', 'max:100'],
            'dispatch_weather_alert_override' => ['nullable', 'boolean'],
        ]);

        $updates = [
            'status' => $validated['status'],
            'carrier_name' => $validated['carrier_name'] ?? $order->carrier_name,
            'tracking_code' => $validated['tracking_code'] ?? $order->tracking_code,
            'dispatch_weather_alert_override' => $request->boolean('dispatch_weather_alert_override'),
        ];

        // Automatically maintain accurate transit timestamps
        if ($validated['status'] === 'processing' && ! $order->paid_at) {
            $updates['paid_at'] = now();
        }

        if ($validated['status'] === 'transit') {
            if (! $order->paid_at) {
                $updates['paid_at'] = now();
            }
            if (! $order->shipped_at) {
                $updates['shipped_at'] = now();
            }
        }

        if ($validated['status'] === 'delivered') {
            if (! $order->shipped_at) {
                $updates['shipped_at'] = now()->subHour();
            }
            if (! $order->delivered_at) {
                $updates['delivered_at'] = now();
            }
        }

        $order->update($updates);

        return back()->with('success', "Order #{$order->order_number} has been updated to {$validated['status']}.");
    }
}
