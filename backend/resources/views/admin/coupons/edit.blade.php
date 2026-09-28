@extends('admin.layouts.admin')

@section('title', 'Edit Coupon: ' . $coupon->code)
@section('page_title', 'Edit Coupon')
@section('page_subtitle', 'Modify promotional parameters, thresholds, and validity window for ' . $coupon->code)

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    code: '{{ old('code', $coupon->code) }}',
    discountType: '{{ old('discount_type', $coupon->discount_type) }}',
    discountAmount: '{{ old('discount_amount', $coupon->discount_amount) }}',
    minOrderAmount: '{{ old('min_order_amount', $coupon->min_order_amount) }}',
    maxDiscountAmount: '{{ old('max_discount_amount', $coupon->max_discount_amount) }}',
    description: '{{ old('description', $coupon->description) }}'
}">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-600 hover:text-stone-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Coupons
        </a>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $coupon->statusBadgeClasses() }}">
                Status: {{ ucfirst($coupon->status) }}
            </span>
            <span class="text-xs text-stone-500 font-mono">
                Used: {{ number_format($coupon->usage_count) }} {{ $coupon->usage_limit ? '/ ' . number_format($coupon->usage_limit) : '' }}
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
            <div class="font-bold mb-1">Please correct the following errors:</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Live Preview Voucher Ticket -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-botanical-800 to-botanical-900 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 opacity-10">
            <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
        </div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wider uppercase bg-white/20 text-emerald-200 mb-2">
                    Active Voucher Preview
                </span>
                <div class="text-3xl font-black font-mono tracking-wider" x-text="code || 'DISCOUNT-CODE'"></div>
                <div class="text-xs text-botanical-200 mt-1" x-text="description || 'Valid for nursery catalog checkout'"></div>
            </div>
            <div class="text-right sm:border-l sm:border-white/20 sm:pl-6">
                <div class="text-2xl font-black text-amber-300">
                    <span x-show="discountType === 'percentage'"><span x-text="discountAmount || '0'"></span>% OFF</span>
                    <span x-show="discountType === 'fixed'">₹<span x-text="discountAmount || '0'"></span> OFF</span>
                    <span x-show="discountType === 'free_shipping'">FREE SHIPPING</span>
                </div>
                <div class="text-xs text-botanical-300 mt-0.5">
                    <span x-show="minOrderAmount > 0">Min cart subtotal ₹<span x-text="minOrderAmount"></span></span>
                    <span x-show="!minOrderAmount || minOrderAmount == 0">No minimum spend required</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-5">
            <div class="border-b border-stone-100 pb-3 flex items-center justify-between">
                <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-botanical-100 text-botanical-800 flex items-center justify-center text-xs font-bold">1</span>
                    Coupon Code & Campaign Information
                </h3>
                <span class="text-xs font-mono text-stone-400">ID: {{ $coupon->id }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Coupon Code *</label>
                    <input type="text" name="code" x-model="code" required
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-mono uppercase font-bold focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                    <p class="text-[11px] text-stone-400 mt-1">Customers enter this alphanumeric code at cart or checkout.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Campaign Description / Notes</label>
                    <input type="text" name="description" x-model="description"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
            </div>

            <!-- Discount Type Selection -->
            <div class="pt-2">
                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-2">Discount Incentive Type *</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="relative flex items-center p-3 rounded-xl border-2 cursor-pointer transition"
                           :class="discountType === 'percentage' ? 'border-botanical-600 bg-botanical-50/40' : 'border-stone-200 hover:border-stone-300'">
                        <input type="radio" name="discount_type" value="percentage" x-model="discountType" class="hidden">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm">%</span>
                            <div>
                                <div class="text-xs font-bold text-stone-900">Percentage Discount</div>
                                <div class="text-[11px] text-stone-500">e.g. 15% or 20% off cart</div>
                            </div>
                        </div>
                    </label>

                    <label class="relative flex items-center p-3 rounded-xl border-2 cursor-pointer transition"
                           :class="discountType === 'fixed' ? 'border-botanical-600 bg-botanical-50/40' : 'border-stone-200 hover:border-stone-300'">
                        <input type="radio" name="discount_type" value="fixed" x-model="discountType" class="hidden">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-sm">₹</span>
                            <div>
                                <div class="text-xs font-bold text-stone-900">Fixed Rupee Off</div>
                                <div class="text-[11px] text-stone-500">e.g. ₹50 or ₹100 flat savings</div>
                            </div>
                        </div>
                    </label>

                    <label class="relative flex items-center p-3 rounded-xl border-2 cursor-pointer transition"
                           :class="discountType === 'free_shipping' ? 'border-botanical-600 bg-botanical-50/40' : 'border-stone-200 hover:border-stone-300'">
                        <input type="radio" name="discount_type" value="free_shipping" x-model="discountType" class="hidden">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-800 flex items-center justify-center font-bold text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            </span>
                            <div>
                                <div class="text-xs font-bold text-stone-900">Free Transit Shipping</div>
                                <div class="text-[11px] text-stone-500">Waives live plant carrier fee</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Discount Values -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 pt-2">
                <div x-show="discountType !== 'free_shipping'">
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">
                        <span x-show="discountType === 'percentage'">Discount Percentage (%) *</span>
                        <span x-show="discountType === 'fixed'">Discount Amount (₹) *</span>
                    </label>
                    <input type="number" step="0.01" min="0" name="discount_amount" x-model="discountAmount"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Minimum Cart Subtotal (₹)</label>
                    <input type="number" step="0.01" min="0" name="min_order_amount" x-model="minOrderAmount"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div x-show="discountType === 'percentage'">
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Max Discount Cap (₹)</label>
                    <input type="number" step="0.01" min="0" name="max_discount_amount" x-model="maxDiscountAmount" placeholder="e.g. 50.00"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- 2. Usage Restrictions & Schedule -->
        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-5">
            <div class="border-b border-stone-100 pb-3">
                <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center text-xs font-bold">2</span>
                    Redemption Limits & Campaign Schedule
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Total Redemptions Allowed (Global Limit)</label>
                    <input type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="e.g. 100 uses (Leave empty for unlimited)"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                    <p class="text-[11px] text-stone-400 mt-1">Currently used {{ $coupon->usage_count }} times.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Uses Per Customer Account</label>
                    <input type="number" min="1" name="per_user_limit" value="{{ old('per_user_limit', $coupon->per_user_limit) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Valid From (Start Date/Time)</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Valid Until (Expiration Date/Time)</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
            </div>

            <div class="pt-3 border-t border-stone-100">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded text-botanical-600 focus:ring-botanical-500">
                    <div>
                        <span class="text-xs font-bold text-stone-800">Coupon Active</span>
                        <div class="text-[11px] text-stone-500">Toggle whether this coupon can currently be redeemed at checkout.</div>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.coupons.index') }}" class="px-5 py-2.5 rounded-xl border border-stone-200 text-sm font-semibold text-stone-700 hover:bg-stone-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-botanical-700 text-white font-semibold text-sm hover:bg-botanical-800 transition shadow-xs">
                Update Coupon
            </button>
        </div>
    </form>
</div>
@endsection
