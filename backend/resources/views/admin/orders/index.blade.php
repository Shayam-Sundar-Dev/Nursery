@extends('admin.layouts.admin')

@section('title', 'Orders & Climate Transit')
@section('page_title', 'Customer Orders & Live-Plant Transit')
@section('page_subtitle', 'Monitor orders, temperature-controlled packaging, carrier tracking, and weather dispatch advisories')

@section('content')
<div class="space-y-6">

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-sm">
        <a href="{{ route('admin.orders.index') }}"
           class="px-3.5 py-1.5 rounded-xl font-medium transition {{ !request('status') && !request('weather_hold') ? 'bg-botanical-800 text-white font-semibold' : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200' }}">
            All Orders ({{ $statusCounts['all'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
           class="px-3.5 py-1.5 rounded-xl font-medium transition {{ request('status') === 'pending' ? 'bg-amber-600 text-white font-semibold' : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200' }}">
            Pending Review ({{ $statusCounts['pending'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}"
           class="px-3.5 py-1.5 rounded-xl font-medium transition {{ request('status') === 'processing' ? 'bg-blue-600 text-white font-semibold' : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200' }}">
            Processing & Packaging ({{ $statusCounts['processing'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'transit']) }}"
           class="px-3.5 py-1.5 rounded-xl font-medium transition {{ request('status') === 'transit' ? 'bg-indigo-600 text-white font-semibold' : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200' }}">
            In Transit ({{ $statusCounts['transit'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}"
           class="px-3.5 py-1.5 rounded-xl font-medium transition {{ request('status') === 'delivered' ? 'bg-emerald-700 text-white font-semibold' : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200' }}">
            Delivered ({{ $statusCounts['delivered'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['weather_hold' => '1']) }}"
           class="px-3.5 py-1.5 rounded-xl font-medium transition {{ request('weather_hold') ? 'bg-red-600 text-white font-semibold' : 'bg-white text-amber-800 hover:bg-amber-50 border border-amber-300' }}">
            ⚠ Weather Holds ({{ $statusCounts['weather_hold'] }})
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-xs">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col sm:flex-row items-center gap-4">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            @if(request('weather_hold'))
                <input type="hidden" name="weather_hold" value="{{ request('weather_hold') }}">
            @endif
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order #, customer name, email, or carrier tracking..."
                       class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-botanical-500">
                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-botanical-800 hover:bg-botanical-900 text-white font-medium text-sm transition">
                Search
            </button>
            @if(request()->anyFilled(['search', 'status', 'weather_hold']))
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-stone-500 hover:text-stone-800 font-medium">Clear</a>
            @endif
        </form>
    </div>

    <!-- Orders Section (with exclusive Processing & Packaging Print features) -->
    <div x-data="orderProcessingPrint()" class="space-y-4">

        @if(request('status') === 'processing')
            <!-- Dedicated Processing & Packaging Shipping Labels Action Banner -->
            <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 border border-blue-200 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-blue-950">Shipping Address Labels (From - To)</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-200 text-blue-900">
                                Fulfillment Mode
                            </span>
                        </div>
                        <p class="text-xs text-blue-800 mt-0.5">
                            Print logistics slips with return nursery address, recipient shipping address, and live plant packaging manifest for orders ready to package.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-end">
                    <button type="button"
                            @click="printSelected()"
                            x-show="selectedOrderIds.length > 0"
                            x-cloak
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-stone-900 hover:bg-black text-white text-xs font-bold transition shadow-sm cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print Selected (<span x-text="selectedOrderIds.length"></span>)
                    </button>

                    <a href="{{ route('admin.orders.bulk-print-shipping-labels') }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print All Shipping Labels ({{ $statusCounts['processing'] }})
                    </a>
                </div>
            </div>
        @endif

        <!-- Orders Table -->
        <div class="bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-stone-600">
                    <thead class="bg-stone-50 text-[11px] uppercase tracking-wider font-semibold text-stone-500 border-b border-stone-100">
                        <tr>
                            @if(request('status') === 'processing')
                                <th class="w-10 px-4 py-3 text-center">
                                    <input type="checkbox"
                                           @change="toggleSelectAll($event)"
                                           :checked="isAllSelected"
                                           title="Select all orders on this page"
                                           class="rounded border-stone-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                </th>
                            @endif
                            <th class="px-5 py-3">Order Number</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Customer & Destination</th>
                            <th class="px-5 py-3">Botanical Items</th>
                            <th class="px-5 py-3">Financial Total</th>
                            <th class="px-5 py-3">Climate Dispatch Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($orders as $order)
                            <tr class="hover:bg-stone-50/70 transition">
                                @if(request('status') === 'processing')
                                    <td class="px-4 py-4 text-center">
                                        <input type="checkbox"
                                               :value="{{ $order->id }}"
                                               x-model="selectedOrderIds"
                                               class="rounded border-stone-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    </td>
                                @endif

                                <td class="px-5 py-4 font-mono font-bold text-stone-900">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-botanical-700 hover:underline">
                                        #{{ $order->order_number }}
                                    </a>
                                    @if($order->tracking_code)
                                        <div class="text-[11px] font-sans font-normal text-stone-400">
                                            {{ $order->carrier_name ?? 'Carrier' }}: <span class="font-mono text-stone-600">{{ $order->tracking_code }}</span>
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-xs text-stone-500">
                                    <div>{{ $order->created_at->format('M d, Y') }}</div>
                                    <div class="text-[11px] text-stone-400">{{ $order->created_at->format('h:i A') }}</div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-medium text-stone-900">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-stone-400">{{ $order->postal_code }} &bull; {{ $order->customer_email }}</div>
                                </td>

                                <td class="px-5 py-4 text-xs">
                                    <div class="font-medium text-stone-800">{{ $order->items->count() }} item(s)</div>
                                    <div class="text-stone-400 truncate max-w-[180px]">{{ $order->items->pluck('product_name')->join(', ') }}</div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-bold text-stone-900">₹{{ number_format($order->total_amount, 2) }}</div>
                                    @if($order->insulation_packaging_fee > 0)
                                        <div class="text-[10px] text-teal-700 font-medium">+₹{{ number_format($order->insulation_packaging_fee, 2) }} thermal pack</div>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex flex-col gap-1 items-start">
                                        @if($order->status === 'pending')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                                Pending
                                            </span>
                                        @elseif($order->status === 'processing')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                                Processing
                                            </span>
                                        @elseif($order->status === 'transit')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                                In Transit
                                            </span>
                                        @elseif($order->status === 'delivered')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                Delivered
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-stone-100 text-stone-700">
                                                Cancelled
                                            </span>
                                        @endif

                                        @if(!$order->dispatch_weather_alert_override && $order->status === 'pending')
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-red-700 bg-red-50 border border-red-200 px-1.5 py-0.2 rounded">
                                                ⚠ Weather Hold
                                            </span>
                                        @elseif($order->dispatch_weather_alert_override)
                                            <span class="text-[10px] text-stone-400">Dispatch Authorized</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        @if(request('status') === 'processing')
                                            <a href="{{ route('admin.orders.print-shipping-label', $order) }}"
                                               target="_blank"
                                               title="Print Shipping Address (From - To)"
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 text-xs font-semibold transition">
                                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                Print Label
                                            </a>
                                        @endif

                                        <a href="{{ route('admin.orders.show', $order) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-stone-100 hover:bg-botanical-50 text-stone-700 hover:text-botanical-800 text-xs font-semibold transition">
                                            Fulfill & Details &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ request('status') === 'processing' ? '8' : '7' }}" class="px-5 py-12 text-center text-stone-400">
                                    @if(request('status') === 'processing')
                                        No orders currently in Processing & Packaging status.
                                    @else
                                        No orders found matching the filter criteria.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="p-4 border-t border-stone-100">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

<script>
    function orderProcessingPrint() {
        return {
            selectedOrderIds: [],
            pageOrderIds: @json($orders->pluck('id')),
            get isAllSelected() {
                return this.pageOrderIds.length > 0 && this.pageOrderIds.every(id => this.selectedOrderIds.includes(id));
            },
            toggleSelectAll(e) {
                if (e.target.checked) {
                    this.selectedOrderIds = Array.from(new Set([...this.selectedOrderIds, ...this.pageOrderIds]));
                } else {
                    this.selectedOrderIds = this.selectedOrderIds.filter(id => !this.pageOrderIds.includes(id));
                }
            },
            printSelected() {
                if (this.selectedOrderIds.length === 0) return;
                const url = '{{ route("admin.orders.bulk-print-shipping-labels") }}?order_ids=' + this.selectedOrderIds.join(',');
                window.open(url, '_blank');
            }
        };
    }
</script>
@endsection

