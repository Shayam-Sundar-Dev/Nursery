@extends('admin.layouts.admin')

@section('title', 'Botanical Operations Dashboard')
@section('page_title', 'Botanical Operations Dashboard')
@section('page_subtitle', 'Live Nursery Fulfillment, Inventory Health, and Climate-Controlled Dispatch Monitoring')

@section('content')
<div class="space-y-6">

    <!-- Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Revenue Card -->
        <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-500">Total Botanical Revenue</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <div class="text-2xl font-bold text-stone-900">{{ $currencySymbol }}{{ number_format($totalRevenue, 2) }}</div>
                <span class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">Delivered & Active</span>
            </div>
            <div class="mt-3 text-xs text-stone-500 flex items-center gap-1">
                <span>Across {{ $totalOrders }} lifetime orders</span>
            </div>
        </div>

        <!-- Orders In Fulfillment -->
        <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-500">Live Orders & Transit</span>
                <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <div class="text-2xl font-bold text-stone-900">{{ $pendingOrders + $processingOrders + $inTransitOrders }}</div>
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-medium">{{ $pendingOrders }} pending</span>
                    <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-medium">{{ $inTransitOrders }} in transit</span>
                </div>
            </div>
            <div class="mt-3 text-xs text-stone-500">
                <span>{{ $deliveredOrders }} completed orders delivered safely</span>
            </div>
        </div>

        <!-- Weather Transit Holds -->
        <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-500">Weather Transit Holds</span>
                <span class="w-10 h-10 rounded-xl {{ $weatherAlertHolds > 0 ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-600' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <div class="text-2xl font-bold {{ $weatherAlertHolds > 0 ? 'text-amber-800' : 'text-stone-900' }}">{{ $weatherAlertHolds }}</div>
                @if($weatherAlertHolds > 0)
                    <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">Requires Review</span>
                @else
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">All Clear</span>
                @endif
            </div>
            <div class="mt-3 text-xs text-stone-500">
                <span>Cold/Heat frost advisory inspection</span>
            </div>
        </div>

        <!-- Botanical Stock Alerts -->
        <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-stone-500">Stock & Inventory Health</span>
                <span class="w-10 h-10 rounded-xl {{ $totalLowStockCount > 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <div class="text-2xl font-bold {{ $totalLowStockCount > 0 ? 'text-red-700' : 'text-stone-900' }}">{{ $totalLowStockCount }}</div>
                @if($outOfStockCount > 0)
                    <span class="text-xs font-semibold text-red-700 bg-red-100 px-2 py-0.5 rounded-full">{{ $outOfStockCount }} depleted</span>
                @else
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">{{ $publishedProducts }} plants live</span>
                @endif
            </div>
            <div class="mt-3 text-xs text-stone-500">
                <span>Variants below safety replenishment point</span>
            </div>
        </div>
    </div>

    <!-- Storefront & Site Management Live Overview -->
    <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-botanical-50 border border-botanical-200 flex items-center justify-center text-botanical-800 flex-shrink-0 overflow-hidden">
                @if(!empty($siteLogoUrl))
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="w-full h-full object-contain p-1">
                @else
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-stone-900 text-sm">{{ $siteName }}</h3>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $maintenanceMode ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $maintenanceMode ? 'bg-amber-500' : 'bg-emerald-500 animate-pulse' }}"></span>
                        {{ $maintenanceMode ? 'Maintenance Mode Active' : 'Storefront Live' }}
                    </span>
                </div>
                <div class="text-xs text-stone-500 mt-0.5">
                    @if($announcementActive && !empty($announcementText))
                        <span class="font-medium text-botanical-700">Announcement:</span> "{{ \Illuminate\Support\Str::limit($announcementText, 65) }}"
                    @else
                        <span>{{ $siteTagline }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs">
            <div class="px-3 py-1.5 rounded-xl bg-stone-50 border border-stone-200">
                <span class="text-stone-400">Free Transit:</span>
                <span class="font-bold text-stone-800">{{ $currencySymbol }}{{ number_format($freeShippingThreshold, 2) }}</span>
            </div>
            <div class="px-3 py-1.5 rounded-xl bg-stone-50 border border-stone-200">
                <span class="text-stone-400">Currency:</span>
                <span class="font-bold text-stone-800">{{ $currencyCode }} ({{ $currencySymbol }})</span>
            </div>
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.settings.index') }}" class="px-3.5 py-1.5 rounded-xl bg-botanical-50 hover:bg-botanical-100 text-botanical-800 font-semibold transition inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Manage Site &rarr;</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Revenue Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-stone-200 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-stone-900">Botanical Revenue Trend</h2>
                    <p class="text-xs text-stone-500">Sales volume performance across the last 6 months</p>
                </div>
                <div class="text-xs font-semibold text-botanical-700 bg-botanical-50 px-2.5 py-1 rounded-lg">
                    Real-time Orders
                </div>
            </div>
            <div class="h-64 relative">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Fulfillment Distribution Donut Chart -->
        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs flex flex-col justify-between">
            <div>
                <h2 class="text-base font-bold text-stone-900 mb-1">Order Pipeline Distribution</h2>
                <p class="text-xs text-stone-500 mb-4">Status of live-plant orders in the delivery cycle</p>
                <div class="h-52 relative flex items-center justify-center">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-stone-100 grid grid-cols-2 gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                    <span class="text-stone-600">Pending: {{ $pendingOrders }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <span class="text-stone-600">Processing: {{ $processingOrders }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                    <span class="text-stone-600">In Transit: {{ $inTransitOrders }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <span class="text-stone-600">Delivered: {{ $deliveredOrders }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Operations & Inventory Alert Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders Feed -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-stone-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-stone-900">Recent Plant Orders</h2>
                    <p class="text-xs text-stone-500">Latest nursery checkout transactions and shipping status</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-botanical-700 hover:text-botanical-800 transition flex items-center gap-1">
                    View All Orders
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-stone-600">
                    <thead class="bg-stone-50 text-[11px] uppercase tracking-wider font-semibold text-stone-500 border-b border-stone-100">
                        <tr>
                            <th class="px-5 py-3">Order Number</th>
                            <th class="px-5 py-3">Recipient</th>
                            <th class="px-5 py-3">Plants / Items</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="px-5 py-3.5 font-mono text-xs font-semibold text-stone-900">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline text-botanical-700">
                                        #{{ $order->order_number }}
                                    </a>
                                    @if(!$order->dispatch_weather_alert_override && $order->status === 'pending')
                                        <span class="block text-[10px] font-sans font-semibold text-amber-700">⚠ Weather Review</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-stone-900">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-stone-400">{{ $order->postal_code }} &bull; {{ $order->customer_email }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-stone-600">
                                    {{ $order->items->count() }} item(s)
                                    <div class="text-stone-400 truncate max-w-[160px]">
                                        {{ $order->items->pluck('product_name')->join(', ') }}
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-semibold text-stone-900">
                                    {{ $currencySymbol }}{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($order->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                            Pending
                                        </span>
                                    @elseif($order->status === 'processing')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-800 border border-blue-200">
                                            Processing
                                        </span>
                                    @elseif($order->status === 'transit')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-800 border border-indigo-200">
                                            In Transit
                                        </span>
                                    @elseif($order->status === 'delivered')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            Delivered
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-stone-100 text-stone-700">
                                            Cancelled
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-botanical-700 hover:bg-botanical-50 transition">
                                        Manage &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-xs text-stone-400">
                                    No customer orders placed yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Replenishment Feed -->
        <div class="bg-white rounded-2xl border border-stone-200 shadow-xs flex flex-col">
            <div class="p-5 border-b border-stone-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-stone-900">Restock Priority Feed</h2>
                    <p class="text-xs text-stone-500">Live plants nearing out-of-stock threshold</p>
                </div>
                <a href="{{ route('admin.inventory.index') }}" class="text-xs font-semibold text-botanical-700 hover:text-botanical-800 transition">
                    View Stock
                </a>
            </div>

            <div class="p-4 flex-1 space-y-3 overflow-y-auto max-h-[380px]">
                @forelse($lowStockVariants as $variant)
                    <div class="p-3.5 rounded-xl border border-stone-200 bg-stone-50/60 hover:bg-stone-50 transition flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-stone-900 truncate">{{ $variant->product->name }}</h4>
                            <p class="text-[11px] text-stone-500 truncate">{{ $variant->title }} &bull; SKU: <span class="font-mono">{{ $variant->sku }}</span></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            @if($variant->stock_quantity <= 0)
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-700">0 left</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">{{ $variant->stock_quantity }} left</span>
                            @endif
                            <form action="{{ route('admin.inventory.update', $variant) }}" method="POST" class="mt-1 flex items-center gap-1">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="stock_quantity" value="{{ $variant->stock_quantity + 10 }}">
                                <button type="submit" title="Add 10 Units to Stock" class="text-[10px] font-semibold text-botanical-700 hover:text-botanical-900 underline">
                                    +10 Restock
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-stone-400">
                        <svg class="w-8 h-8 text-emerald-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        All botanical variants are comfortably above low stock thresholds!
                    </div>
                @endforelse
            </div>

            <div class="p-4 bg-botanical-50/50 rounded-b-2xl border-t border-stone-100 text-xs text-botanical-800 flex items-center justify-between">
                <span>Catalog summary: {{ $totalProducts }} active specimens</span>
                <a href="{{ route('admin.products.create') }}" class="font-bold underline hover:text-botanical-950">+ New Plant</a>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Monthly Revenue Line Chart
        const revCtx = document.getElementById('revenueChart').getContext('2d');
        const revData = @json($monthlyRevenue);
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: Object.keys(revData),
                datasets: [{
                    label: 'Revenue (₹)',
                    data: Object.values(revData),
                    borderColor: '#2A4E2F',
                    backgroundColor: 'rgba(42, 78, 47, 0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#2A4E2F',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) { return '{{ $currencySymbol }}' + val; }
                        },
                        grid: { color: '#F1F5F9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Order Status Donut Chart
        const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
        const statusData = @json($statusBreakdown);
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: [
                        '#F59E0B', // Pending
                        '#3B82F6', // Processing
                        '#6366F1', // In Transit
                        '#10B981', // Delivered
                        '#9CA3AF'  // Cancelled
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endpush
