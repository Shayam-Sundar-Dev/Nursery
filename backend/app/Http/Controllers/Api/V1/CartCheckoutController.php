<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartCheckoutController extends Controller
{
    /**
     * Validate a coupon code against a subtotal.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($validated['code']));
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return response()->json([
                'success' => false,
                'message' => "Coupon code '{$code}' was not found.",
            ], 404);
        }

        $check = $coupon->validateForCart((float) $validated['subtotal'], $request->user());

        if (! $check['valid']) {
            return response()->json([
                'success' => false,
                'message' => $check['message'],
            ], 422);
        }

        $discount = $coupon->calculateDiscount((float) $validated['subtotal'], 9.99);

        return response()->json([
            'success' => true,
            'message' => "Coupon '{$coupon->code}' applied successfully!",
            'coupon' => [
                'code' => $coupon->code,
                'description' => $coupon->description,
                'discount_type' => $coupon->discount_type,
                'discount_amount' => (float) $coupon->discount_amount,
                'calculated_discount' => $discount,
                'discount_label' => $coupon->discountLabel(),
            ],
        ]);
    }

    /**
     * Check deliverability for live plants by postal code and seasonal insulation necessity.
     */
    public function checkDeliverability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'postal_code' => 'required|string|max:12',
            'state' => 'nullable|string|max:100',
        ]);

        $code = trim($validated['postal_code']);
        $state = $validated['state'] ?? null;
        $stateShipping = SiteSetting::getShippingFeeForState($state);

        // In a real-world app, this queries carrier APIs (FedEx/UPS/ShipStation)
        // Restricted postal codes simulating remote/off-grid zones where live transit > 4 days
        $isDeliverable = ! str_starts_with($code, '999') && ! str_starts_with($code, '000');

        // Simulating winter/extreme heat seasons requiring thermal wrap
        $requiresThermalPackaging = str_starts_with($code, '0') || str_starts_with($code, '1');

        $transitText = $stateShipping['estimated_days'] ?? ($isDeliverable ? '2-3 days' : null);

        return response()->json([
            'success' => true,
            'postal_code' => $code,
            'state' => $state,
            'is_deliverable' => $isDeliverable,
            'estimated_transit_days' => $isDeliverable ? ($stateShipping['estimated_days'] ?? 2) : null,
            'estimated_transit_text' => $transitText,
            'requires_thermal_packaging' => $requiresThermalPackaging,
            'insulation_fee' => $requiresThermalPackaging ? 4.50 : 0.00,
            'shipping_fee' => $stateShipping['fee'],
            'is_state_rate' => $stateShipping['matched'],
            'message' => $isDeliverable
                ? ($requiresThermalPackaging ? 'Deliverable with climate-controlled insulation wrap.' : 'Standard live-plant transit available.')
                : 'Live plants cannot currently be safely shipped to this remote destination due to transit time limits.',
        ]);
    }

    /**
     * Calculate order totals, tax, shipping, and packaging fees.
     */
    public function checkoutSummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'postal_code' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        $subtotal = 0;
        $hasLivePlant = false;

        foreach ($validated['items'] as $item) {
            $variant = ProductVariant::with('product')->findOrFail($item['variant_id']);
            $subtotal += ($variant->price * $item['quantity']);

            if ($variant->product && $variant->product->type === 'plant') {
                $hasLivePlant = true;
            }
        }

        $freeShippingThreshold = (float) SiteSetting::get('free_shipping_threshold', 75.00);
        $thermalPackagingFee = (float) SiteSetting::get('thermal_packaging_fee', 4.50);

        $state = $validated['state'] ?? null;
        $stateShipping = SiteSetting::getShippingFeeForState($state);
        $baseShippingFee = (float) $stateShipping['fee'];

        $shipping = $subtotal > $freeShippingThreshold ? 0.00 : $baseShippingFee;

        // Coupon calculation
        $discount = 0.00;
        $appliedCoupon = null;
        $couponMessage = null;

        if (! empty($validated['coupon_code'])) {
            $code = strtoupper(trim($validated['coupon_code']));
            $coupon = Coupon::where('code', $code)->first();

            if ($coupon) {
                $check = $coupon->validateForCart($subtotal, $request->user());
                if ($check['valid']) {
                    $discount = $coupon->calculateDiscount($subtotal, $shipping);
                    $appliedCoupon = [
                        'code' => $coupon->code,
                        'discount_type' => $coupon->discount_type,
                        'discount_amount' => (float) $coupon->discount_amount,
                        'calculated_discount' => $discount,
                        'label' => $coupon->discountLabel(),
                    ];
                } else {
                    $couponMessage = $check['message'];
                }
            } else {
                $couponMessage = "Coupon '{$code}' not found.";
            }
        }

        // Standard 8% tax applied after discount or on subtotal
        $taxableAmount = max(0.00, $subtotal - $discount);
        $tax = round($taxableAmount * 0.08, 2);

        // Insulation packaging fee if shipping live plants in cold/hot zones
        $postalCode = $validated['postal_code'] ?? '';
        $requiresThermal = $hasLivePlant && (str_starts_with($postalCode, '0') || str_starts_with($postalCode, '1'));
        $insulationFee = $requiresThermal ? $thermalPackagingFee : 0.00;

        $total = max(0.00, $subtotal - $discount + $tax + $shipping + $insulationFee);

        return response()->json([
            'success' => true,
            'breakdown' => [
                'subtotal' => round($subtotal, 2),
                'discount' => round($discount, 2),
                'tax' => $tax,
                'shipping' => $shipping,
                'base_shipping_fee' => $baseShippingFee,
                'shipping_state' => $state,
                'is_state_rate_applied' => $stateShipping['matched'],
                'estimated_transit_days' => $stateShipping['estimated_days'],
                'insulation_fee' => $insulationFee,
                'thermal_packaging' => $insulationFee,
                'total' => round($total, 2),
                'grand_total' => round($total, 2),
                'item_count' => count($validated['items']),
                'qualifies_for_free_shipping' => $subtotal > $freeShippingThreshold,
                'coupon' => $appliedCoupon,
                'coupon_error' => $couponMessage,
            ],
        ]);
    }

    /**
     * Atomically process checkout and reserve stock with database locking.
     */
    public function createOrder(Request $request): JsonResponse
    {
        if ($request->has('shipping_address_line1') && ! $request->has('shipping_address')) {
            $request->merge([
                'shipping_address' => [
                    'street' => trim($request->input('shipping_address_line1') . ' ' . $request->input('shipping_address_line2', '')),
                    'city' => (string) $request->input('city', ''),
                    'state' => (string) $request->input('state', ''),
                    'postal_code' => (string) $request->input('postal_code', ''),
                ],
            ]);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:150',
            'customer_email' => 'required|email|max:150',
            'customer_phone' => 'nullable|string|max:50',
            'shipping_address' => 'required|array',
            'shipping_address.street' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'gift_message' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $orderItemsData = [];
            $hasLivePlant = false;

            foreach ($validated['items'] as $itemData) {
                // Lock variant row for update to eliminate race condition
                $variant = ProductVariant::with('product')
                    ->where('id', $itemData['variant_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($variant->stock_quantity < $itemData['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$variant->title}. Only {$variant->stock_quantity} left.",
                    ], 422);
                }

                // Decrement inventory
                $variant->decrement('stock_quantity', $itemData['quantity']);

                $lineTotal = $variant->price * $itemData['quantity'];
                $subtotal += $lineTotal;

                if ($variant->product && $variant->product->type === 'plant') {
                    $hasLivePlant = true;
                }

                $orderItemsData[] = [
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product ? $variant->product->name : 'Botanical Item',
                    'variant_title' => $variant->title,
                    'unit_price' => $variant->price,
                    'quantity' => $itemData['quantity'],
                    'subtotal' => $lineTotal,
                ];
            }

            $postalCode = $validated['shipping_address']['postal_code'];
            $state = $validated['shipping_address']['state'] ?? null;
            $freeShippingThreshold = (float) SiteSetting::get('free_shipping_threshold', 75.00);
            $thermalPackagingFee = (float) SiteSetting::get('thermal_packaging_fee', 4.50);

            $stateShipping = SiteSetting::getShippingFeeForState($state);
            $baseShippingFee = (float) $stateShipping['fee'];

            $requiresThermal = $hasLivePlant && (str_starts_with($postalCode, '0') || str_starts_with($postalCode, '1'));
            $insulationFee = $requiresThermal ? $thermalPackagingFee : 0.00;
            $shipping = $subtotal > $freeShippingThreshold ? 0.00 : $baseShippingFee;

            // Coupon calculation & reservation
            $couponId = null;
            $couponCode = null;
            $discountAmount = 0.00;

            if (! empty($validated['coupon_code'])) {
                $code = strtoupper(trim($validated['coupon_code']));
                $coupon = Coupon::where('code', $code)->lockForUpdate()->first();

                if ($coupon) {
                    $check = $coupon->validateForCart($subtotal, $request->user());
                    if ($check['valid']) {
                        $couponId = $coupon->id;
                        $couponCode = $coupon->code;
                        $discountAmount = $coupon->calculateDiscount($subtotal, $shipping);
                        $coupon->recordUsage();
                    }
                }
            }

            $taxableAmount = max(0.00, $subtotal - $discountAmount);
            $tax = round($taxableAmount * 0.08, 2);
            $total = max(0.00, $subtotal - $discountAmount + $tax + $shipping + $insulationFee);

            $order = Order::create([
                'order_number' => 'NUR-'.strtoupper(Str::random(8)),
                'user_id' => $request->user()?->id,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'status' => 'processing',
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'shipping_fee' => $shipping,
                'insulation_packaging_fee' => $insulationFee,
                'coupon_id' => $couponId,
                'coupon_code' => $couponCode,
                'discount_amount' => $discountAmount,
                'total_amount' => $total,
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['shipping_address'],
                'postal_code' => $postalCode,
                'gift_message' => $validated['gift_message'] ?? null,
                'paid_at' => now(),
            ]);

            foreach ($orderItemsData as $item) {
                $order->items()->create($item);
            }

            // Save address and phone for authenticated customer for future use and clear cloud cart
            if ($user = $request->user()) {
                $user->update([
                    'phone' => $validated['customer_phone'] ?? $user->phone,
                    'street_address' => $validated['shipping_address']['street'] ?? $user->street_address,
                    'city' => $validated['shipping_address']['city'] ?? $user->city,
                    'state' => $validated['shipping_address']['state'] ?? $user->state,
                    'postal_code' => $validated['shipping_address']['postal_code'] ?? $user->postal_code,
                    'cart' => [],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully with botanical care packaging!',
                'order' => $order->load('items'),
            ], 201);
        });
    }
}
