@extends('admin.layouts.admin')

@section('title', 'Inventory & Botanical Stock')
@section('page_title', 'Inventory & Variant Replenishment')
@section('page_subtitle', 'Monitor live specimens, planters, soil bags, and restock variants before stock depletion')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Pills -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <a href="{{ route('admin.inventory.index') }}"
           class="p-4 rounded-2xl bg-white border border-stone-200 shadow-xs hover:border-botanical-500 transition">
            <span class="text-xs uppercase font-semibold text-stone-500">Total Variants</span>
            <div class="text-2xl font-bold text-stone-900 mt-1">{{ $stats['total_variants'] }}</div>
        </a>

        <a href="{{ route('admin.inventory.index', ['filter' => 'in_stock']) }}"
           class="p-4 rounded-2xl bg-white border border-stone-200 shadow-xs hover:border-emerald-500 transition">
            <span class="text-xs uppercase font-semibold text-emerald-600">Comfortably In Stock</span>
            <div class="text-2xl font-bold text-emerald-700 mt-1">{{ $stats['in_stock'] }}</div>
        </a>

        <a href="{{ route('admin.inventory.index', ['filter' => 'low_stock']) }}"
           class="p-4 rounded-2xl bg-white border border-stone-200 shadow-xs hover:border-amber-500 transition">
            <span class="text-xs uppercase font-semibold text-amber-600">Low Stock Alert (&le; Threshold)</span>
            <div class="text-2xl font-bold text-amber-700 mt-1">{{ $stats['low_stock'] }}</div>
        </a>

        <a href="{{ route('admin.inventory.index', ['filter' => 'out_of_stock']) }}"
           class="p-4 rounded-2xl bg-white border border-stone-200 shadow-xs hover:border-red-500 transition">
            <span class="text-xs uppercase font-semibold text-red-600">Depleted / Out of Stock</span>
            <div class="text-2xl font-bold text-red-700 mt-1">{{ $stats['out_of_stock'] }}</div>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-xs">
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex flex-col sm:flex-row items-center gap-4">
            <input type="hidden" name="filter" value="{{ request('filter') }}">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by SKU, variant title, or plant name..."
                       class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-botanical-500">
                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-botanical-800 hover:bg-botanical-900 text-white font-medium text-sm transition">
                Filter Stock
            </button>
            @if(request()->anyFilled(['search', 'filter']))
                <a href="{{ route('admin.inventory.index') }}" class="text-xs text-stone-500 hover:text-stone-800 font-medium">Clear</a>
            @endif
        </form>
    </div>

    <!-- Inventory Table with Inline Restock -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-600">
                <thead class="bg-stone-50 text-[11px] uppercase tracking-wider font-semibold text-stone-500 border-b border-stone-100">
                    <tr>
                        <th class="px-5 py-3">Specimen / Parent Product</th>
                        <th class="px-5 py-3">Variant Specification</th>
                        <th class="px-5 py-3">SKU</th>
                        <th class="px-5 py-3">Unit Price</th>
                        <th class="px-5 py-3">Available Stock</th>
                        <th class="px-5 py-3">Stock Health</th>
                        <th class="px-5 py-3 text-right">Quick Restock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($variants as $variant)
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-stone-900 flex items-center gap-2">
                                    <a href="{{ route('admin.products.edit', $variant->product) }}" class="hover:underline text-botanical-800">
                                        {{ $variant->product->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-stone-400">{{ $variant->product->category->name ?? 'Uncategorized' }}</div>
                            </td>

                            <td class="px-5 py-4 text-xs font-medium text-stone-800">
                                {{ $variant->title }}
                            </td>

                            <td class="px-5 py-4 font-mono text-xs text-stone-600">
                                {{ $variant->sku }}
                            </td>

                            <td class="px-5 py-4 font-bold text-stone-900 text-xs">
                                ₹{{ number_format($variant->price, 2) }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="font-bold text-sm {{ $variant->stock_quantity <= 0 ? 'text-red-700' : ($variant->stock_quantity <= $variant->low_stock_threshold ? 'text-amber-700' : 'text-stone-900') }}">
                                    {{ $variant->stock_quantity }}
                                </span>
                                <span class="text-xs text-stone-400">units</span>
                                <div class="text-[10px] text-stone-400">threshold: {{ $variant->low_stock_threshold }}</div>
                            </td>

                            <td class="px-5 py-4">
                                @if($variant->stock_quantity <= 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Depleted</span>
                                @elseif($variant->stock_quantity <= $variant->low_stock_threshold)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Low Stock</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800">Optimal</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <form action="{{ route('admin.inventory.update', $variant) }}" method="POST" class="inline-flex items-center gap-1.5 justify-end">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" min="0" name="stock_quantity" value="{{ $variant->stock_quantity }}"
                                           class="w-20 px-2 py-1 text-xs rounded-lg border border-stone-200 text-center font-bold focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-botanical-700 hover:bg-botanical-800 text-white text-xs font-semibold transition" title="Update Stock">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-stone-400">
                                No variants found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($variants->hasPages())
            <div class="p-4 border-t border-stone-100">
                {{ $variants->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
