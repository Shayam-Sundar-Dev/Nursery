@extends('admin.layouts.admin')

@section('title', 'Coupon & Discount Management')
@section('page_title', 'Coupons & Discounts')
@section('page_subtitle', 'Create and oversee botanical promotional codes, cart thresholds, and seasonal discounts')

@section('content')
<div class="space-y-6">
    <!-- Top Action & Overview Metrics -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="p-2.5 rounded-xl bg-botanical-100 text-botanical-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </span>
            <div>
                <h2 class="text-xl font-bold text-stone-900">Promotions & Vouchers</h2>
                <p class="text-xs text-stone-500">Manage promotional discounts applied at cart checkout</p>
            </div>
        </div>

        <a href="{{ route('admin.coupons.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-botanical-700 text-white font-semibold text-sm hover:bg-botanical-800 transition shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create New Coupon
        </a>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs">
            <div class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Total Coupons</div>
            <div class="text-2xl font-black text-stone-900 mt-1">{{ number_format($metrics['total_coupons']) }}</div>
            <div class="text-xs text-stone-400 mt-1">Configured in system</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs">
            <div class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Active Promos</div>
            <div class="text-2xl font-black text-emerald-700 mt-1">{{ number_format($metrics['active_coupons']) }}</div>
            <div class="text-xs text-emerald-600 mt-1">Ready for cart redemption</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs">
            <div class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Total Redemptions</div>
            <div class="text-2xl font-black text-botanical-800 mt-1">{{ number_format($metrics['total_redemptions']) }}</div>
            <div class="text-xs text-stone-400 mt-1">Orders with coupon savings</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs">
            <div class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Expiring in 7 Days</div>
            <div class="text-2xl font-black text-amber-700 mt-1">{{ number_format($metrics['expiring_soon']) }}</div>
            <div class="text-xs text-amber-600 mt-1">Needs review or extension</div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="p-4 rounded-2xl bg-white border border-stone-200 shadow-2xs">
        <form method="GET" action="{{ route('admin.coupons.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by coupon code (e.g. SPRING20) or description..."
                       class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
            </div>

            <div>
                <select name="discount_type" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                    <option value="">All Discount Types</option>
                    <option value="percentage" {{ request('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ request('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                    <option value="free_shipping" {{ request('discount_type') === 'free_shipping' ? 'selected' : '' }}>Free Shipping</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="exhausted" {{ request('status') === 'exhausted' ? 'selected' : '' }}>Usage Limit Reached</option>
                </select>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-stone-900 text-white text-sm font-semibold hover:bg-black transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'discount_type', 'status']))
                    <a href="{{ route('admin.coupons.index') }}" class="p-2.5 rounded-xl text-stone-500 hover:text-stone-800 hover:bg-stone-100 transition" title="Clear Filters">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Coupons Table -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Coupon Code & Details</th>
                        <th class="py-3.5 px-4">Discount Value</th>
                        <th class="py-3.5 px-4">Cart Requirements</th>
                        <th class="py-3.5 px-4">Redemptions</th>
                        <th class="py-3.5 px-4">Validity Window</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-sm">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-stone-50/60 transition">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-stone-100 text-stone-900 border border-stone-300">
                                        {{ $coupon->code }}
                                    </span>
                                </div>
                                @if($coupon->description)
                                    <div class="text-xs text-stone-500 mt-1 max-w-xs">{{ $coupon->description }}</div>
                                @endif
                            </td>

                            <td class="py-4 px-4">
                                <span class="font-bold text-stone-900">
                                    {{ $coupon->discountLabel() }}
                                </span>
                                @if($coupon->discount_type === 'percentage' && $coupon->max_discount_amount)
                                    <div class="text-[11px] text-stone-500">Max ₹{{ number_format($coupon->max_discount_amount, 2) }}</div>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-xs text-stone-600">
                                @if($coupon->min_order_amount > 0)
                                    <div>Min Subtotal: <strong class="text-stone-900">₹{{ number_format($coupon->min_order_amount, 2) }}</strong></div>
                                @else
                                    <span class="text-stone-400">No minimum</span>
                                @endif
                                <div class="text-[11px] text-stone-400 mt-0.5">{{ $coupon->per_user_limit ? $coupon->per_user_limit.' use/customer' : 'Unlimited/customer' }}</div>
                            </td>

                            <td class="py-4 px-4 text-xs">
                                <div class="font-semibold text-stone-900">
                                    {{ number_format($coupon->usage_count) }}
                                    @if($coupon->usage_limit)
                                        <span class="text-stone-400 font-normal">/ {{ number_format($coupon->usage_limit) }}</span>
                                    @else
                                        <span class="text-stone-400 font-normal">uses</span>
                                    @endif
                                </div>
                                @if($coupon->usage_limit)
                                    <div class="w-24 bg-stone-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                        <div class="bg-botanical-600 h-1.5 rounded-full" style="width: {{ min(100, ($coupon->usage_count / $coupon->usage_limit) * 100) }}%"></div>
                                    </div>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-xs text-stone-600">
                                @if($coupon->starts_at || $coupon->expires_at)
                                    <div>From: {{ $coupon->starts_at ? $coupon->starts_at->format('M d, Y') : 'Immediate' }}</div>
                                    <div class="{{ $coupon->expires_at && $coupon->expires_at->isPast() ? 'text-red-600 font-semibold' : '' }}">
                                        Until: {{ $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : 'Never expires' }}
                                    </div>
                                @else
                                    <span class="text-stone-400">Always valid</span>
                                @endif
                            </td>

                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $coupon->statusBadgeClasses() }}">
                                    {{ ucfirst($coupon->status) }}
                                </span>
                            </td>

                            <td class="py-4 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Toggle Active -->
                                    <form action="{{ route('admin.coupons.toggle-active', $coupon) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-1.5 rounded-lg border border-stone-200 hover:bg-stone-50 text-stone-600 transition"
                                                title="{{ $coupon->is_active ? 'Deactivate Coupon' : 'Activate Coupon' }}">
                                            @if($coupon->is_active)
                                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            @else
                                                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @endif
                                        </button>
                                    </form>

                                    <!-- Edit -->
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="p-1.5 rounded-lg border border-stone-200 hover:bg-stone-50 text-stone-600 transition" title="Edit Coupon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete coupon {{ $coupon->code }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg border border-red-100 hover:bg-red-50 text-red-600 transition" title="Delete Coupon">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-stone-500">
                                <svg class="w-12 h-12 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <div class="text-sm font-semibold text-stone-700">No promotional coupons found</div>
                                <p class="text-xs text-stone-400 mt-1">Get started by creating your first nursery seasonal discount code</p>
                                <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center gap-1.5 mt-3 text-xs font-semibold text-botanical-700 hover:underline">
                                    + Add New Coupon
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
            <div class="p-4 border-t border-stone-100">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
