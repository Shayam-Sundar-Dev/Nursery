<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderTrackingController extends Controller
{
    /**
     * Retrieve tracking and live transit updates for an order.
     */
    public function track(string $orderNumber): JsonResponse
    {
        $order = Order::with('items')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Transit milestones with plant-specific care checkpoints
        $steps = [
            [
                'title' => 'Order Received & Inspected',
                'description' => 'Plant health and roots checked at the greenhouse.',
                'completed' => true,
                'timestamp' => $order->created_at->toIso8601String(),
            ],
            [
                'title' => 'Hydration & Root Ball Packing',
                'description' => 'Roots secured with moist sphagnum moss and breathable wrap.',
                'completed' => in_array($order->status, ['packed_with_hydration', 'shipped', 'out_for_delivery', 'delivered']),
                'timestamp' => $order->shipped_at?->toIso8601String(),
            ],
            [
                'title' => 'In Transit via Express Courier',
                'description' => $order->carrier_name ? "Handed over to {$order->carrier_name}" : 'Awaiting courier dispatch.',
                'completed' => in_array($order->status, ['shipped', 'out_for_delivery', 'delivered']),
                'tracking_code' => $order->tracking_code,
            ],
            [
                'title' => 'Delivered to Your Door',
                'description' => 'Unbox gently, place in bright indirect light, and let adapt for 48 hours.',
                'completed' => $order->status === 'delivered',
                'timestamp' => $order->delivered_at?->toIso8601String(),
            ],
        ];

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'carrier_name' => $order->carrier_name ?? 'Botanical Express',
            'tracking_code' => $order->tracking_code ?? 'TRK-PENDING',
            'milestones' => $steps,
            'unboxing_advice' => 'Remember: A little foliage drop during the first 3 days after transit is normal as your plant acclimatizes to its new environment.',
        ]);
    }
}
