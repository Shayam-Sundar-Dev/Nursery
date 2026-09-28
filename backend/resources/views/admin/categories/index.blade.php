@extends('admin.layouts.admin')

@section('title', 'Botanical Categories')
@section('page_title', 'Botanical Categories')
@section('page_subtitle', 'Organize houseplants, planters, soil mixes, and garden tools into hierarchical departments')

@section('content')
<div class="space-y-6" x-data="{
    showCreateModal: false,
    showEditModal: false,
    editCategory: { id: null, name: '', parent_id: '', description: '', banner_url: '', is_active: true },
    openEdit(cat) {
        this.editCategory = {
            id: cat.id,
            name: cat.name,
            parent_id: cat.parent_id || '',
            description: cat.description || '',
            banner_url: cat.banner_url || '',
            is_active: Boolean(cat.is_active)
        };
        this.showEditModal = true;
    }
}">
    <!-- Header / Action Button -->
    <div class="flex items-center justify-between">
        <div class="text-sm text-stone-500">
            Total of <strong class="text-stone-900">{{ $categories->count() }}</strong> botanical departments & subcategories.
        </div>
        <button type="button" @click="showCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-botanical-700 hover:bg-botanical-800 text-white text-sm font-semibold shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ New Category</span>
        </button>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-600">
                <thead class="bg-stone-50 text-[11px] uppercase tracking-wider font-semibold text-stone-500 border-b border-stone-100">
                    <tr>
                        <th class="px-5 py-3">Category Name</th>
                        <th class="px-5 py-3">Parent Department</th>
                        <th class="px-5 py-3">Products Count</th>
                        <th class="px-5 py-3">Banner Preview</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-stone-900 flex items-center gap-2">
                                    @if($category->parent_id)
                                        <span class="text-stone-300 text-sm font-mono">&mdash;</span>
                                    @endif
                                    {{ $category->name }}
                                </div>
                                <div class="text-[11px] font-mono text-stone-400">slug: {{ $category->slug }}</div>
                                @if($category->description)
                                    <div class="text-xs text-stone-500 truncate max-w-sm mt-0.5">{{ $category->description }}</div>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-xs font-medium">
                                @if($category->parent)
                                    <span class="px-2 py-0.5 rounded-full bg-stone-100 text-stone-700">{{ $category->parent->name }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-botanical-50 text-botanical-800 font-semibold">Root Department</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-xs font-semibold text-stone-900">
                                <a href="{{ route('admin.products.index', ['category_id' => $category->id]) }}" class="hover:underline text-botanical-700">
                                    {{ $category->products_count }} plant(s)
                                </a>
                            </td>

                            <td class="px-5 py-4">
                                @if($category->banner_url)
                                    <img src="{{ $category->banner_url }}" alt="{{ $category->name }}" class="w-16 h-8 rounded-lg object-cover border border-stone-200">
                                @else
                                    <span class="text-stone-300 text-xs italic">No banner</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if($category->is_active)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">Active</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-stone-100 text-stone-600">Hidden</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="openEdit({{ json_encode($category) }})" class="p-2 text-stone-600 hover:text-botanical-700 hover:bg-stone-100 rounded-lg transition" title="Edit Category">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete category {{ addslashes($category->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-400 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Delete Category">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-stone-400">
                                No categories created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Create Category -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-stone-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showCreateModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-stone-200 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h3 class="text-base font-bold text-stone-900">Create Botanical Category</h3>
                <button type="button" @click="showCreateModal = false" class="text-stone-400 hover:text-stone-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Rare Aroids, Hanging Ferns"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Parent Category</label>
                    <select name="parent_id" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        <option value="">None (Top-Level Department)</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Description</label>
                    <textarea name="description" rows="2.5" placeholder="Description of the plants or accessories in this group..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Banner Image URL</label>
                    <input type="url" name="banner_url" placeholder="https://..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <label class="flex items-center gap-2 cursor-pointer pt-2">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-botanical-600 focus:ring-botanical-500">
                    <span class="text-xs font-semibold text-stone-700">Active and visible in store navigation</span>
                </label>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-stone-100">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-100 text-sm font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-botanical-700 hover:bg-botanical-800 text-white text-sm font-semibold shadow-sm">
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Category -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-stone-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showEditModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-stone-200 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h3 class="text-base font-bold text-stone-900">Edit Botanical Category</h3>
                <button type="button" @click="showEditModal = false" class="text-stone-400 hover:text-stone-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'/admin/categories/' + editCategory.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Category Name *</label>
                    <input type="text" name="name" x-model="editCategory.name" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Parent Category</label>
                    <select name="parent_id" x-model="editCategory.parent_id" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        <option value="">None (Top-Level Department)</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Description</label>
                    <textarea name="description" x-model="editCategory.description" rows="2.5"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Banner Image URL</label>
                    <input type="url" name="banner_url" x-model="editCategory.banner_url"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <label class="flex items-center gap-2 cursor-pointer pt-2">
                    <input type="checkbox" name="is_active" value="1" x-model="editCategory.is_active" class="w-4 h-4 rounded text-botanical-600 focus:ring-botanical-500">
                    <span class="text-xs font-semibold text-stone-700">Active and visible in store navigation</span>
                </label>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-stone-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-100 text-sm font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-botanical-700 hover:bg-botanical-800 text-white text-sm font-semibold shadow-sm">
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
