@extends('admin.layouts.admin')

@section('title', $user->name . ' - Gardener Profile')
@section('page_title', $user->name)
@section('page_subtitle', 'Customer Profile, Order History & Digital Garden Companion Plants')

@section('content')
<div class="space-y-6">

    <!-- Top Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.customers.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-600 hover:text-stone-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Customers
        </a>
    </div>

    <!-- Overview Stats Banner -->
    <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-botanical-700 text-white flex items-center justify-center font-bold text-xl shadow-md">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-stone-900">{{ $user->name }}</h2>
                <div class="text-xs text-stone-500 font-mono">{{ $user->email }} &bull; Member since {{ $user->created_at->format('M Y') }}</div>
            </div>
        </div>

        <div class="flex items-center gap-6 border-t md:border-t-0 pt-4 md:pt-0 border-stone-100">
            <div>
                <span class="text-xs uppercase font-semibold text-stone-400">Total Lifetime Spend</span>
                <div class="text-xl font-bold text-stone-900">₹{{ number_format($totalSpent, 2) }}</div>
            </div>
            <div>
                <span class="text-xs uppercase font-semibold text-stone-400">Orders Count</span>
                <div class="text-xl font-bold text-stone-900">{{ $user->orders->count() }}</div>
            </div>
            <div>
                <span class="text-xs uppercase font-semibold text-stone-400">Digital Plants</span>
                <div class="text-xl font-bold text-emerald-700">{{ $user->userPlants->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Adopted Garden Companion Plants -->
    <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-stone-900">Adopted Digital Garden Roster</h3>
                <p class="text-xs text-stone-500">Live plants tracked in customer's digital companion garden</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-botanical-50 text-botanical-800">
                {{ $user->userPlants->count() }} Specimens
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($user->userPlants as $plant)
                @php
                    $isOverdue = $plant->isWateringOverdue();
                    $due = $plant->next_watering_due;
                @endphp
                <div class="p-4 rounded-xl border border-stone-200 bg-stone-50/50 hover:bg-stone-50 transition space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            @if($plant->product && $plant->product->primary_image_url)
                                <img src="{{ $plant->product->primary_image_url }}" alt="{{ $plant->nickname }}"
                                     class="w-12 h-12 rounded-xl object-cover border border-stone-200 bg-stone-100 flex-shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-botanical-100 text-botanical-800 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    🌿
                                </div>
                            @endif

                            <div>
                                <h4 class="text-sm font-bold text-stone-900">"{{ $plant->nickname }}"</h4>
                                <div class="text-xs text-stone-500">
                                    {{ $plant->product->name ?? 'Custom Plant' }}
                                    @if($plant->product?->botanical_name)
                                        <span class="font-serif italic text-botanical-700">({{ $plant->product->botanical_name }})</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            @if($isOverdue)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                    💧 Thirsty / Overdue
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                    ✓ Hydrated
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs bg-white p-3 rounded-lg border border-stone-100">
                        <div>
                            <span class="text-stone-400 block text-[10px] uppercase">Watering Cycle:</span>
                            <span class="font-medium text-stone-700">Every {{ $plant->reminder_frequency_days }} days</span>
                        </div>
                        <div>
                            <span class="text-stone-400 block text-[10px] uppercase">Last Watered:</span>
                            <span class="font-medium text-stone-700">{{ $plant->last_watered_at ? $plant->last_watered_at->format('M d, Y') : 'Not yet logged' }}</span>
                        </div>
                        <div class="col-span-2 pt-1 border-t border-stone-50">
                            <span class="text-stone-400 block text-[10px] uppercase">Next Watering Due:</span>
                            <span class="font-semibold {{ $isOverdue ? 'text-amber-800' : 'text-stone-800' }}">
                                {{ $due ? $due->diffForHumans() : 'Now' }}
                            </span>
                        </div>
                    </div>

                    @if($plant->notes)
                        <div class="text-xs text-stone-600 bg-amber-50/50 p-2.5 rounded-lg border border-amber-100/60 italic">
                            "{{ $plant->notes }}"
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-2 py-8 text-center text-xs text-stone-400">
                    This customer has not adopted any botanical specimens into their digital garden yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Customer Order History -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-stone-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-stone-900">Order Purchase History</h3>
            <span class="text-xs text-stone-500">{{ $user->orders->count() }} past orders</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-600">
                <thead class="bg-stone-50 text-[11px] uppercase tracking-wider font-semibold text-stone-500 border-b border-stone-100">
                    <tr>
                        <th class="px-5 py-3">Order Number</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Items</th>
                        <th class="px-5 py-3">Total Paid</th>
                        <th class="px-5 py-3">Fulfillment Status</th>
                        <th class="px-5 py-3 text-right">View Order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($user->orders as $order)
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="px-5 py-3.5 font-mono font-bold text-stone-900">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-botanical-700 hover:underline">
                                    #{{ $order->order_number }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-stone-500">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3.5 text-xs">
                                {{ $order->items->count() }} item(s)
                            </td>
                            <td class="px-5 py-3.5 font-bold text-stone-900 text-xs">
                                ₹{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $order->status === 'delivered' ? 'bg-emerald-50 text-emerald-800' : ($order->status === 'transit' ? 'bg-indigo-50 text-indigo-800' : 'bg-amber-50 text-amber-800') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-semibold text-botanical-700 hover:underline">
                                    Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-xs text-stone-400">
                                No past orders recorded for this customer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
