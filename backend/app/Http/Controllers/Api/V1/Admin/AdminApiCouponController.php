<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiCouponController extends Controller
{
    /**
     * List all coupons with filters and metrics.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Coupon::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('discount_type')) {
            $query->where('discount_type', $type);
        }

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $coupons = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $coupons->items(),
            'meta' => [
                'current_page' => $coupons->currentPage(),
                'last_page' => $coupons->lastPage(),
                'total' => $coupons->total(),
                'per_page' => $coupons->perPage(),
            ],
            'summary' => [
                'total_coupons' => Coupon::count(),
                'active_coupons' => Coupon::active()->count(),
                'total_redemptions' => Coupon::sum('usage_count'),
            ],
        ]);
    }

    /**
     * Store a newly created coupon.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'description' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['required', 'in:percentage,fixed,free_shipping'],
            'discount_amount' => ['required_unless:discount_type,free_shipping', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $code = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', $validated['code']));

        $coupon = Coupon::create([
            'code' => $code,
            'description' => $validated['description'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount_amount' => $validated['discount_type'] === 'free_shipping' ? 0.00 : ($validated['discount_amount'] ?? 0.00),
            'min_order_amount' => $validated['min_order_amount'] ?? 0.00,
            'max_discount_amount' => $validated['max_discount_amount'] ?? null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'per_user_limit' => $validated['per_user_limit'] ?? 1,
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Coupon '{$code}' created successfully.",
            'data' => $coupon,
        ], 201);
    }

    /**
     * Show a coupon.
     */
    public function show(Coupon $coupon): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $coupon->loadCount('orders'),
        ]);
    }

    /**
     * Update a coupon.
     */
    public function update(Request $request, Coupon $coupon): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['sometimes', 'required', 'string', 'max:50', "unique:coupons,code,{$coupon->id}"],
            'description' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['sometimes', 'required', 'in:percentage,fixed,free_shipping'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['code'])) {
            $validated['code'] = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', $validated['code']));
        }

        $coupon->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Coupon '{$coupon->code}' updated successfully.",
            'data' => $coupon,
        ]);
    }

    /**
     * Delete a coupon.
     */
    public function destroy(Coupon $coupon): JsonResponse
    {
        if ($coupon->orders()->exists()) {
            $coupon->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => "Coupon '{$coupon->code}' is linked to order history and was deactivated instead of deleted.",
            ]);
        }

        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => "Coupon '{$coupon->code}' was deleted successfully.",
        ]);
    }
}
