<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCouponController extends Controller
{
    /**
     * Display a listing of coupons and promotion campaigns.
     */
    public function index(Request $request): View
    {
        $query = Coupon::query();

        // Search by code or description
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by discount type
        if ($type = $request->input('discount_type')) {
            $query->where('discount_type', $type);
        }

        // Filter by status (active, expired, inactive, exhausted)
        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'expired') {
                $query->whereNotNull('expires_at')->where('expires_at', '<', now());
            } elseif ($status === 'exhausted') {
                $query->whereNotNull('usage_limit')->whereColumn('usage_count', '>=', 'usage_limit');
            }
        }

        $coupons = $query->latest()->paginate(15)->withQueryString();

        // Metrics for summary cards
        $metrics = [
            'total_coupons' => Coupon::count(),
            'active_coupons' => Coupon::active()->count(),
            'total_redemptions' => Coupon::sum('usage_count'),
            'expiring_soon' => Coupon::where('is_active', true)
                ->whereBetween('expires_at', [now(), now()->addDays(7)])
                ->count(),
        ];

        return view('admin.coupons.index', compact('coupons', 'metrics'));
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create(): View
    {
        return view('admin.coupons.create');
    }

    /**
     * Store a newly created coupon in storage.
     */
    public function store(Request $request): RedirectResponse
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

        Coupon::create([
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

        return redirect()->route('admin.coupons.index')
            ->with('success', "Coupon '{$code}' created successfully.");
    }

    /**
     * Show the form for editing the specified coupon.
     */
    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified coupon in storage.
     */
    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', "unique:coupons,code,{$coupon->id}"],
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

        $coupon->update([
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
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.coupons.index')
            ->with('success', "Coupon '{$code}' updated successfully.");
    }

    /**
     * Remove the specified coupon from storage.
     */
    public function destroy(Coupon $coupon): RedirectResponse
    {
        $code = $coupon->code;

        // If coupon has been used in orders, soft deactivate instead of breaking order foreign keys
        if ($coupon->orders()->exists()) {
            $coupon->update(['is_active' => false]);

            return redirect()->route('admin.coupons.index')
                ->with('warning', "Coupon '{$code}' has order history and was deactivated instead of deleted.");
        }

        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', "Coupon '{$code}' deleted successfully.");
    }

    /**
     * Quick toggle coupon active status.
     */
    public function toggleActive(Coupon $coupon): RedirectResponse
    {
        $coupon->is_active = ! $coupon->is_active;
        $coupon->save();

        $status = $coupon->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Coupon '{$coupon->code}' {$status}.");
    }
}
