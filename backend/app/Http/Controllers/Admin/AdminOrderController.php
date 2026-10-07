<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SiteSetting;
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

    /**
     * Print shipping address label (From - To) for an individual order.
     * Restricted to orders in Processing and Packaging status.
     */
    public function printShippingLabel(Order $order): View|RedirectResponse
    {
        if ($order->status !== 'processing') {
            return redirect()->route('admin.orders.index', ['status' => 'processing'])
                ->with('error', "Shipping address labels can only be printed for orders in Processing & Packaging status (Order #{$order->order_number} is '{$order->status}').");
        }

        $order->load(['items.product', 'items.variant', 'user']);
        $orders = collect([$order]);
        $fromAddress = $this->getNurseryFromAddress();

        return view('admin.orders.print-shipping-label', compact('orders', 'fromAddress'));
    }

    /**
     * Bulk print shipping address labels (From - To) for orders in Processing & Packaging status.
     */
    public function bulkPrintShippingLabels(Request $request): View|RedirectResponse
    {
        $query = Order::with(['items.product', 'items.variant', 'user'])
            ->where('status', 'processing');

        if ($ids = $request->input('order_ids')) {
            $idArray = is_array($ids) ? $ids : explode(',', (string) $ids);
            $query->whereIn('id', array_filter($idArray));
        }

        $orders = $query->latest()->get();

        if ($orders->isEmpty()) {
            return redirect()->route('admin.orders.index', ['status' => 'processing'])
                ->with('error', 'No orders in Processing & Packaging status found to print.');
        }

        $fromAddress = $this->getNurseryFromAddress();

        return view('admin.orders.print-shipping-label', compact('orders', 'fromAddress'));
    }

    /**
     * Retrieve nursery sender information from site settings.
     */
    protected function getNurseryFromAddress(): array
    {
        return [
            'company_name' => SiteSetting::get('site_name', 'Verdant Botanical Nursery & Garden'),
            'tagline' => SiteSetting::get('site_tagline', 'Live-Plant Specialized Fulfillment & Rare Botanical Specimens'),
            'address' => SiteSetting::get('nursery_address', '742 Evergreen Botanical Way, Greenhouse 4, Portland, OR 97201'),
            'phone' => SiteSetting::get('contact_phone', '+1 (555) 321-GROW'),
            'email' => SiteSetting::get('contact_email', 'care@verdantnursery.test'),
        ];
    }
}

