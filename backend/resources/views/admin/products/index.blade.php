@extends('admin.layouts.admin')

@section('title', 'Plant & Product Catalog')
@section('page_title', 'Botanical Catalog & Products')
@section('page_subtitle', 'Manage house plants, ceramic planters, potting soils, botanical care guides, and inventory variants')

@section('content')
<div class="space-y-6">

    <!-- Header Actions and Filters Bar -->
    <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-xs">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Search Input -->
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1">Search Catalog</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, botanical name, or SKU..."
                           class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-botanical-500">
                    <svg class="w-4 h-4 text-stone-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1">Category</label>
                <select name="category_id" class="w-full py-2 px-3 text-sm rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-botanical-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Type Filter -->
            <div>
                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1">Botanical Type</label>
                <select name="type" class="w-full py-2 px-3 text-sm rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-botanical-500">
                    <option value="">All Types</option>
                    @foreach($productTypes as $pt)
                        <option value="{{ $pt->slug }}" {{ request('type') == $pt->slug ? 'selected' : '' }}>
                            {{ $pt->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Filter & Submit -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1">Stock Level</label>
                    <select name="stock_status" class="w-full py-2 px-3 text-sm rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-botanical-500">
                        <option value="">All Stock</option>
                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock (≤ threshold)</option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <button type="submit" class="py-2 px-4 rounded-xl bg-botanical-800 hover:bg-botanical-900 text-white font-medium text-sm transition">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'category_id', 'type', 'stock_status']))
                    <a href="{{ route('admin.products.index') }}" class="p-2 rounded-xl text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition" title="Clear Filters">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-600">
                <thead class="bg-stone-50 text-[11px] uppercase tracking-wider font-semibold text-stone-500 border-b border-stone-100">
                    <tr>
                        <th class="px-5 py-3">Product / Botanical Specimen</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Base Price</th>
                        <th class="px-5 py-3">Inventory Variants</th>
                        <th class="px-5 py-3">Botanical Specs</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($products as $product)
                        @php
                            $totalStock = $product->variants->sum('stock_quantity');
                            $hasLowStock = $product->variants->contains(fn($v) => $v->stock_quantity <= $v->low_stock_threshold);
                        @endphp
                        <tr class="hover:bg-stone-50/70 transition">
                            <!-- Image and Titles -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3.5">
                                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}"
                                         class="w-12 h-12 rounded-xl object-cover border border-stone-200 shadow-xs flex-shrink-0 bg-stone-100">
                                    <div class="min-w-0">
                                        <div class="font-bold text-stone-900 truncate flex items-center gap-1.5">
                                            {{ $product->name }}
                                            @if($product->is_featured)
                                                <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-100 text-amber-800">★ Featured</span>
                                            @endif
                                        </div>
                                        @if($product->botanical_name)
                                            <div class="text-xs italic text-botanical-700 font-serif truncate">{{ $product->botanical_name }}</div>
                                        @endif
                                        <div class="text-[11px] text-stone-400 font-mono">slug: {{ $product->slug }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-5 py-4 text-xs font-medium text-stone-700">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </td>

                            <!-- Type -->
                            <td class="px-5 py-4">
                                @if($product->productType)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $product->productType->badge_class }}">
                                        <span>{{ $product->productType->name }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-stone-100 text-stone-700">
                                        {{ ucfirst($product->type) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Base Price -->
                            <td class="px-5 py-4 font-bold text-stone-900">
                                ₹{{ number_format($product->base_price, 2) }}
                            </td>

                            <!-- Variants and Stock -->
                            <td class="px-5 py-4">
                                <div class="text-xs font-medium text-stone-900">
                                    {{ $product->variants->count() }} variant(s)
                                </div>
                                <div class="mt-0.5 text-xs">
                                    @if($totalStock <= 0)
                                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-700">Out of Stock</span>
                                    @elseif($hasLowStock)
                                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">{{ $totalStock }} units (Low)</span>
                                    @else
                                        <span class="text-stone-500 font-semibold">{{ $totalStock }} in stock</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Botanical Specs -->
                            <td class="px-5 py-4 text-xs">
                                @if($product->plantAttributes)
                                    <div class="flex flex-wrap gap-1 max-w-[170px]">
                                        @if($product->plantAttributes->light_requirement)
                                            <span class="px-1.5 py-0.5 rounded bg-stone-100 text-stone-600 text-[10px]">☀️ {{ $product->plantAttributes->light_requirement }}</span>
                                        @endif
                                        @if($product->plantAttributes->pet_friendly)
                                            <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px]">🐾 Pet Safe</span>
                                        @endif
                                        @if($product->plantAttributes->air_purifying)
                                            <span class="px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 text-[10px]">💨 Air Purifying</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-stone-400 text-xs italic">N/A</span>
                                @endif
                            </td>

                            <!-- Published / Featured Toggles -->
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1.5">
                                    <form action="{{ route('admin.products.toggle-publish', $product) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2 py-0.5 rounded text-[11px] font-semibold transition {{ $product->is_published ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-stone-200 text-stone-600 hover:bg-stone-300' }}">
                                            {{ $product->is_published ? '✓ Published' : '○ Draft' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.products.toggle-featured', $product) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2 py-0.5 rounded text-[11px] font-semibold transition {{ $product->is_featured ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-stone-100 text-stone-400 hover:bg-stone-200' }}">
                                            {{ $product->is_featured ? '★ Featured' : '☆ Standard' }}
                                        </button>
                                    </form>
                                </div>
                            </td>

                            <!-- Action Buttons -->
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="p-2 text-stone-600 hover:text-botanical-700 hover:bg-stone-100 rounded-lg transition"
                                       title="Edit Botanical Specifications">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to remove {{ addslashes($product->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-400 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Delete Product">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-stone-400">
                                No products found matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-stone-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
