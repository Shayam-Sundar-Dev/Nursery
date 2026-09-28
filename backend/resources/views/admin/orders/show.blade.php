@extends('admin.layouts.admin')

@section('title', 'Order #' . $order->order_number)
@section('page_title', 'Order #' . $order->order_number)
@section('page_subtitle', 'Live Plant Fulfillment, Climate Packaging Verification & Carrier Tracking')

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Action -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-600 hover:text-stone-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Orders
        </a>
        <div class="flex items-center gap-2">
            <a href="/api/v1/orders/{{ $order->order_number }}/track" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-stone-200 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-50 transition">
                <svg class="w-4 h-4 text-botanical-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Customer Live Tracking API
            </a>
        </div>
    </div>

    <!-- 2-Column Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Order Items & Customer / Shipping Details -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Botanical Items Card -->
            <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                    <h3 class="text-base font-bold text-stone-900">Ordered Botanical Specimens & Care Items</h3>
                    <span class="text-xs text-stone-500">{{ $order->items->count() }} item(s)</span>
                </div>

                <div class="divide-y divide-stone-100">
                    @foreach($order->items as $item)
                        <div class="py-3.5 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5 min-w-0">
                                @if($item->product && $item->product->primary_image_url)
                                    <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product_name }}"
                                         class="w-14 h-14 rounded-xl object-cover border border-stone-200 bg-stone-100 flex-shrink-0">
                                @else
                                    <div class="w-14 h-14 rounded-xl bg-botanical-100 flex items-center justify-center text-botanical-700 font-bold text-xs flex-shrink-0">
                                        PLANT
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-stone-900 truncate">
                                        @if($item->product)
                                            <a href="{{ route('admin.products.edit', $item->product) }}" class="hover:underline text-botanical-800">
                                                {{ $item->product_name }}
                                            </a>
                                        @else
                                            {{ $item->product_name }}
                                        @endif
                                    </h4>
                                    @if($item->variant_title)
                                        <div class="text-xs text-stone-500">{{ $item->variant_title }}</div>
                                    @endif
                                    @if($item->product?->botanical_name)
                                        <div class="text-[11px] font-serif italic text-botanical-700">{{ $item->product->botanical_name }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right flex-shrink-0">
                                <div class="text-sm font-bold text-stone-900">₹{{ number_format($item->subtotal, 2) }}</div>
                                <div class="text-xs text-stone-500">₹{{ number_format($item->unit_price, 2) }} &times; {{ $item->quantity }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Financial Breakdown -->
                <div class="pt-4 border-t border-stone-100 space-y-2 text-sm text-stone-600">
                    <div class="flex justify-between">
                        <span>Items Subtotal:</span>
                        <span class="font-medium text-stone-900">₹{{ number_format($order->subtotal, 2) }}</span>
                    </div>

                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-emerald-700 font-medium">
                            <span>Promotional Discount ({{ $order->coupon_code }}):</span>
                            <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif

                    @if($order->insulation_packaging_fee > 0)
                        <div class="flex justify-between text-teal-800 bg-teal-50/60 p-2 rounded-lg">
                            <span class="flex items-center gap-1.5 font-medium">
                                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                Thermal Insulation & Phase-Change Pack:
                            </span>
                            <span class="font-bold">₹{{ number_format($order->insulation_packaging_fee, 2) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <span>Botanical Expedited Shipping:</span>
                        <span class="font-medium text-stone-900">₹{{ number_format($order->shipping_fee, 2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Estimated State Tax:</span>
                        <span class="font-medium text-stone-900">₹{{ number_format($order->tax_amount, 2) }}</span>
                    </div>

                    <div class="flex justify-between pt-2 border-t border-stone-200 text-base font-bold text-stone-900">
                        <span>Grand Total Paid:</span>
                        <span class="text-botanical-800">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>

                @if($order->gift_message)
                    <div class="mt-4 p-3.5 rounded-xl bg-amber-50/70 border border-amber-200 text-xs">
                        <div class="font-bold text-amber-900 mb-0.5">Customer Gift Note:</div>
                        <p class="text-amber-800 italic">"{{ $order->gift_message }}"</p>
                    </div>
                @endif
            </div>

            <!-- Customer & Shipping Addresses Card -->
            <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-4">
                <h3 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-3">Destination & Customer Profile</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <h4 class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">Shipping Destination</h4>
                        @php $ship = $order->shipping_address ?? []; @endphp
                        <div class="font-bold text-stone-900">{{ $ship['recipient'] ?? $order->customer_name }}</div>
                        <div class="text-stone-600">{{ $ship['street'] ?? 'N/A' }}</div>
                        <div class="text-stone-600">
                            {{ $ship['city'] ?? '' }}{{ !empty($ship['state']) ? ', '.$ship['state'] : '' }} {{ $ship['postal_code'] ?? $order->postal_code }}
                        </div>
                        <div class="text-stone-500 text-xs mt-1">{{ $ship['country'] ?? 'US' }}</div>
                    </div>

                    <div>
                        <h4 class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">Customer Contact</h4>
                        <div class="font-medium text-stone-900">{{ $order->customer_name }}</div>
                        <div class="text-stone-600 text-xs mt-0.5 font-mono">{{ $order->customer_email }}</div>
                        <div class="text-stone-600 text-xs mt-0.5">{{ $order->customer_phone ?? 'No phone provided' }}</div>

                        @if($order->user)
                            <div class="mt-3 pt-2 border-t border-stone-100">
                                <a href="{{ route('admin.customers.show', $order->user) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-botanical-700 hover:underline">
                                    View Gardener Profile & Adopted Plants &rarr;
                                </a>
                            </div>
                        @else
                            <div class="mt-2 text-xs text-stone-400 italic">Guest Checkout Customer</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Col: Fulfillment Workflow & Weather Climate Protection -->
        <div class="space-y-6">

            <!-- Fulfillment Status & Carrier Dispatch Form -->
            <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-5">
                <div class="border-b border-stone-100 pb-3">
                    <h3 class="text-base font-bold text-stone-900">Fulfillment & Status Manager</h3>
                    <p class="text-xs text-stone-500">Update transit phases and assign tracking carrier</p>
                </div>

                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Fulfillment Status *</label>
                        <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending Nursery Review</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing & Root Wrapping</option>
                            <option value="transit" {{ $order->status === 'transit' ? 'selected' : '' }}>In Transit (Handed to Carrier)</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered to Customer Door</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled & Refunded</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Carrier Service Name</label>
                        <input type="text" name="carrier_name" value="{{ old('carrier_name', $order->carrier_name ?? 'Botanical Express Transit') }}" placeholder="e.g. FedEx Climate Express, UPS"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Carrier Tracking Code</label>
                        <input type="text" name="tracking_code" value="{{ old('tracking_code', $order->tracking_code) }}" placeholder="e.g. BOT-TRK-981240"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm font-mono focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                    </div>

                    <!-- Weather Transit Safety Override -->
                    <div class="p-3.5 rounded-xl bg-stone-50 border border-stone-200 space-y-2">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="dispatch_weather_alert_override" value="1"
                                   {{ $order->dispatch_weather_alert_override ? 'checked' : '' }}
                                   class="mt-1 w-4 h-4 rounded text-botanical-600 focus:ring-botanical-500">
                            <div>
                                <span class="text-xs font-bold text-stone-900">Authorize Weather Dispatch</span>
                                <p class="text-[11px] text-stone-500">Check to approve parcel release even if cold/heat wave advisory is active for destination ZIP ({{ $order->postal_code }}).</p>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-botanical-700 hover:bg-botanical-800 text-white font-semibold text-sm transition shadow-sm">
                        Save Order Status & Tracking
                    </button>
                </form>
            </div>

            <!-- Transit Milestones & Timestamps Card -->
            <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-4">
                <h3 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-3">Transit Timeline</h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Order Placed:</span>
                        <span class="font-medium text-stone-800">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Payment Confirmed:</span>
                        <span class="font-medium text-stone-800">
                            {{ $order->paid_at ? $order->paid_at->format('M d, Y h:i A') : 'Pending / Not set' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Dispatched into Transit:</span>
                        <span class="font-medium text-stone-800">
                            {{ $order->shipped_at ? $order->shipped_at->format('M d, Y h:i A') : 'Awaiting fulfillment' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Delivered & Unboxed:</span>
                        <span class="font-medium text-stone-800">
                            {{ $order->delivered_at ? $order->delivered_at->format('M d, Y h:i A') : 'Not yet delivered' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
