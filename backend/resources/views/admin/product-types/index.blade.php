@extends('admin.layouts.admin')

@section('title', 'Product Types')
@section('page_title', 'Product Types Management')
@section('page_subtitle', 'Configure botanical product classifications, plant care triggers, and catalog taxonomy')

@section('content')
<div class="space-y-6" x-data="{
    showCreateModal: false,
    showEditModal: false,
    editType: {
        id: null,
        name: '',
        slug: '',
        description: '',
        icon: 'leaf',
        badge_color: 'emerald',
        requires_botanical_attributes: false,
        is_active: true,
        sort_order: 0
    },
    openEdit(type) {
        this.editType = {
            id: type.id,
            name: type.name,
            slug: type.slug,
            description: type.description || '',
            icon: type.icon || 'leaf',
            badge_color: type.badge_color || 'emerald',
            requires_botanical_attributes: Boolean(type.requires_botanical_attributes),
            is_active: Boolean(type.is_active),
            sort_order: type.sort_order || 0
        };
        this.showEditModal = true;
    }
}">
    <!-- Header / Stats Overview & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-sm text-stone-500">
                Total of <strong class="text-stone-900">{{ $productTypes->count() }}</strong> product classifications configured.
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" @click="showCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-botanical-700 hover:bg-botanical-800 text-white text-sm font-semibold shadow-sm transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ New Product Type</span>
            </button>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="p-4 rounded-2xl bg-white border border-stone-200 shadow-xs">
        <form method="GET" action="{{ route('admin.product-types.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product types by name, slug, or description..."
                       class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-botanical-500">
            </div>
            <button type="submit" class="w-full sm:w-auto py-2 px-4 rounded-xl bg-botanical-800 hover:bg-botanical-900 text-white font-medium text-sm transition">
                Search
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.product-types.index') }}" class="p-2 rounded-xl text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition" title="Clear Search">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
    </div>

    <!-- Product Types Table -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-600">
                <thead class="bg-stone-50 text-[11px] uppercase tracking-wider font-semibold text-stone-500 border-b border-stone-100">
                    <tr>
                        <th class="px-5 py-3.5">Product Type</th>
                        <th class="px-5 py-3.5">Badge Style</th>
                        <th class="px-5 py-3.5">Botanical Specs Trigger</th>
                        <th class="px-5 py-3.5">Assigned Products</th>
                        <th class="px-5 py-3.5">Order</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($productTypes as $type)
                        <tr class="hover:bg-stone-50/70 transition">
                            <!-- Name & Slug -->
                            <td class="px-5 py-4">
                                <div class="font-bold text-stone-900 flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-stone-100 flex items-center justify-center text-stone-600 shrink-0">
                                        @if($type->icon === 'leaf')
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                        @elseif($type->icon === 'box')
                                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        @elseif($type->icon === 'layers')
                                            <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        @elseif($type->icon === 'wrench')
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        @elseif($type->icon === 'sprout')
                                            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        @else
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-stone-900">{{ $type->name }}</div>
                                        <div class="text-[11px] font-mono text-stone-400">code: {{ $type->slug }}</div>
                                    </div>
                                </div>
                                @if($type->description)
                                    <div class="text-xs text-stone-500 truncate max-w-xs mt-1">{{ $type->description }}</div>
                                @endif
                            </td>

                            <!-- Badge Preview -->
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $type->badge_class }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                    <span>{{ $type->name }}</span>
                                </span>
                            </td>

                            <!-- Botanical Specs Trigger -->
                            <td class="px-5 py-4">
                                @if($type->requires_botanical_attributes)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Light & Care Specs</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-stone-100 text-stone-500">
                                        <span>Standard Product</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Products Count -->
                            <td class="px-5 py-4 text-xs font-semibold text-stone-900">
                                <a href="{{ route('admin.products.index', ['type' => $type->slug]) }}" class="hover:underline text-botanical-700 flex items-center gap-1.5">
                                    <span>{{ $type->products_count }} product(s)</span>
                                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>

                            <!-- Sort Order -->
                            <td class="px-5 py-4 text-xs font-mono text-stone-500">
                                {{ $type->sort_order }}
                            </td>

                            <!-- Status & Toggle -->
                            <td class="px-5 py-4">
                                <form action="{{ route('admin.product-types.toggle-active', $type) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1.5 cursor-pointer group" title="Click to toggle active status">
                                        @if($type->is_active)
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 group-hover:bg-emerald-100 transition">Active</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-stone-100 text-stone-600 group-hover:bg-stone-200 transition">Hidden</span>
                                        @endif
                                    </button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="openEdit({{ json_encode($type) }})" class="p-2 text-stone-600 hover:text-botanical-700 hover:bg-stone-100 rounded-lg transition cursor-pointer" title="Edit Product Type">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    @if($type->products_count === 0)
                                        <form action="{{ route('admin.product-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete product type \'{{ $type->name }}\'?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-stone-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Delete Type">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="p-2 text-stone-300 cursor-not-allowed" title="Cannot delete: in use by {{ $type->products_count }} product(s)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-stone-400">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto text-stone-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    </div>
                                    <div class="font-semibold text-stone-800 text-sm">No product types found</div>
                                    <p class="text-xs text-stone-500">Get started by creating your first botanical product classification.</p>
                                    <button type="button" @click="showCreateModal = true" class="px-4 py-2 rounded-xl bg-botanical-700 hover:bg-botanical-800 text-white text-xs font-semibold transition">
                                        + Create Product Type
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- CREATE MODAL -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <div @click="showCreateModal = false" class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity"></div>
            <div class="inline-block w-full max-w-lg p-6 my-8 text-left bg-white rounded-3xl shadow-xl transform transition-all relative z-10 border border-stone-100">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <div>
                        <h3 class="font-serif text-lg font-bold text-stone-900">Add New Product Type</h3>
                        <p class="text-xs text-stone-500 mt-0.5">Define a product classification and care specification triggers</p>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="p-2 text-stone-400 hover:text-stone-700 rounded-xl hover:bg-stone-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.product-types.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Type Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Bonsai & Rare Flora"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Slug / Identifier Code</label>
                            <input type="text" name="slug" placeholder="e.g. bonsai (auto-generated if blank)"
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-mono focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Badge Color</label>
                            <select name="badge_color" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                                <option value="emerald">Emerald (Botanical Green)</option>
                                <option value="amber">Amber (Pottery / Terracotta)</option>
                                <option value="stone">Stone (Earth / Neutral)</option>
                                <option value="blue">Blue (Water & Tools)</option>
                                <option value="teal">Teal (Fresh / Seedling)</option>
                                <option value="purple">Purple (Luxury / Bundle)</option>
                                <option value="rose">Rose (Floral / Petals)</option>
                                <option value="indigo">Indigo (Specialty)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Icon Shape</label>
                            <select name="icon" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                                <option value="leaf">Leaf (Plants & Flora)</option>
                                <option value="box">Box (Planters & Pots)</option>
                                <option value="layers">Layers (Soil & Fertilizers)</option>
                                <option value="wrench">Wrench (Garden Tools)</option>
                                <option value="sprout">Sprout (Seeds & Propagules)</option>
                                <option value="package">Package (Bundles & Kits)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Sort Order</label>
                            <input type="number" name="sort_order" value="0" min="0"
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Description (Optional)</label>
                        <textarea name="description" rows="2" placeholder="Brief description of products belonging to this type..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none"></textarea>
                    </div>

                    <div class="p-3.5 rounded-xl bg-sand-50/80 border border-stone-200/80 space-y-2.5">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="requires_botanical_attributes" value="1"
                                   class="mt-0.5 w-4 h-4 rounded text-botanical-700 focus:ring-botanical-500 border-stone-300">
                            <div>
                                <span class="text-xs font-bold text-stone-800 block">Requires Botanical Care Attributes</span>
                                <span class="text-[11px] text-stone-500 block">Check this if products of this type require sunlight, watering schedule, pet friendliness, and plant care instructions.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer pt-2 border-t border-stone-200/60">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="w-4 h-4 rounded text-botanical-700 focus:ring-botanical-500 border-stone-300">
                            <span class="text-xs font-bold text-stone-800">Active (Visible in product forms & storefront)</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 rounded-xl border border-stone-200 text-stone-600 text-xs font-semibold hover:bg-stone-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-botanical-800 hover:bg-botanical-900 text-white text-xs font-semibold shadow-sm">
                            Save Product Type
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <div @click="showEditModal = false" class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity"></div>
            <div class="inline-block w-full max-w-lg p-6 my-8 text-left bg-white rounded-3xl shadow-xl transform transition-all relative z-10 border border-stone-100">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <div>
                        <h3 class="font-serif text-lg font-bold text-stone-900">Edit Product Type</h3>
                        <p class="text-xs text-stone-500 mt-0.5">Update product type configuration and attributes</p>
                    </div>
                    <button type="button" @click="showEditModal = false" class="p-2 text-stone-400 hover:text-stone-700 rounded-xl hover:bg-stone-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'{{ url('admin/product-types') }}/' + editType.id" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Type Name *</label>
                        <input type="text" name="name" x-model="editType.name" required
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Slug / Identifier Code *</label>
                            <input type="text" name="slug" x-model="editType.slug" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-mono focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                            <span class="text-[10px] text-amber-600 block mt-1">Note: Modifying slug updates all products currently referencing it.</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Badge Color</label>
                            <select name="badge_color" x-model="editType.badge_color" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                                <option value="emerald">Emerald (Botanical Green)</option>
                                <option value="amber">Amber (Pottery / Terracotta)</option>
                                <option value="stone">Stone (Earth / Neutral)</option>
                                <option value="blue">Blue (Water & Tools)</option>
                                <option value="teal">Teal (Fresh / Seedling)</option>
                                <option value="purple">Purple (Luxury / Bundle)</option>
                                <option value="rose">Rose (Floral / Petals)</option>
                                <option value="indigo">Indigo (Specialty)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Icon Shape</label>
                            <select name="icon" x-model="editType.icon" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                                <option value="leaf">Leaf (Plants & Flora)</option>
                                <option value="box">Box (Planters & Pots)</option>
                                <option value="layers">Layers (Soil & Fertilizers)</option>
                                <option value="wrench">Wrench (Garden Tools)</option>
                                <option value="sprout">Sprout (Seeds & Propagules)</option>
                                <option value="package">Package (Bundles & Kits)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Sort Order</label>
                            <input type="number" name="sort_order" x-model="editType.sort_order" min="0"
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Description (Optional)</label>
                        <textarea name="description" x-model="editType.description" rows="2"
                                  class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none"></textarea>
                    </div>

                    <div class="p-3.5 rounded-xl bg-sand-50/80 border border-stone-200/80 space-y-2.5">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="requires_botanical_attributes" value="1" x-model="editType.requires_botanical_attributes"
                                   class="mt-0.5 w-4 h-4 rounded text-botanical-700 focus:ring-botanical-500 border-stone-300">
                            <div>
                                <span class="text-xs font-bold text-stone-800 block">Requires Botanical Care Attributes</span>
                                <span class="text-[11px] text-stone-500 block">Check this if products of this type require sunlight, watering schedule, pet friendliness, and plant care instructions.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer pt-2 border-t border-stone-200/60">
                            <input type="checkbox" name="is_active" value="1" x-model="editType.is_active"
                                   class="w-4 h-4 rounded text-botanical-700 focus:ring-botanical-500 border-stone-300">
                            <span class="text-xs font-bold text-stone-800">Active (Visible in product forms & storefront)</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-100">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2.5 rounded-xl border border-stone-200 text-stone-600 text-xs font-semibold hover:bg-stone-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-botanical-800 hover:bg-botanical-900 text-white text-xs font-semibold shadow-sm">
                            Update Product Type
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

