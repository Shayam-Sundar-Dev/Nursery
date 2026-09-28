<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiOrderController extends Controller
{
    /**
     * List orders with administrative filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['items.product', 'user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('tracking_code', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = (int) $request->input('per_page', 15);
        $orders = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Show single order with items and customer info.
     */
    public function show(int $id): JsonResponse
    {
        $order = Order::with(['items.product.plantAttributes', 'items.variant', 'user'])->find($id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Update order status, tracking, and weather override.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

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
        ];

        if ($request->has('dispatch_weather_alert_override')) {
            $updates['dispatch_weather_alert_override'] = $request->boolean('dispatch_weather_alert_override');
        }

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
            if (! $order->delivered_at) {
                $updates['delivered_at'] = now();
            }
        }

        $order->update($updates);

        return response()->json([
            'success' => true,
            'message' => "Order #{$order->order_number} status updated to {$validated['status']}.",
            'data' => $order->fresh(['items', 'user']),
        ]);
    }
}
