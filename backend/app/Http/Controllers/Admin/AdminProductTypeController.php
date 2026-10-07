<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProductTypeController extends Controller
{
    /**
     * Display a listing of product types.
     */
    public function index(Request $request): View
    {
        $query = ProductType::withCount('products');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $productTypes = $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.product-types.index', compact('productTypes'));
    }

    /**
     * Store a newly created product type in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash', 'unique:product_types,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:60'],
            'badge_color' => ['nullable', 'string', 'in:emerald,amber,stone,blue,teal,purple,rose,indigo'],
            'requires_botanical_attributes' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : ProductType::generateUniqueSlug($validated['name']);

        $productType = ProductType::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? 'leaf',
            'badge_color' => $validated['badge_color'] ?? 'emerald',
            'requires_botanical_attributes' => $request->boolean('requires_botanical_attributes', false),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Product type "'.$productType->name.'" created successfully.');
    }

    /**
     * Update the specified product type in storage.
     */
    public function update(Request $request, ProductType $productType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('product_types', 'slug')->ignore($productType->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:60'],
            'badge_color' => ['nullable', 'string', 'in:emerald,amber,stone,blue,teal,purple,rose,indigo'],
            'requires_botanical_attributes' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $newSlug = Str::slug($validated['slug']);
        $oldSlug = $productType->slug;

        // If the slug changed, update all products referencing the old slug
        if ($newSlug !== $oldSlug) {
            Product::where('type', $oldSlug)->update(['type' => $newSlug]);
        }

        $productType->update([
            'name' => $validated['name'],
            'slug' => $newSlug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? 'leaf',
            'badge_color' => $validated['badge_color'] ?? 'emerald',
            'requires_botanical_attributes' => $request->boolean('requires_botanical_attributes', false),
            'is_active' => $request->boolean('is_active', false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Product type "'.$productType->name.'" updated successfully.');
    }

    /**
     * Remove the specified product type from storage.
     */
    public function destroy(ProductType $productType): RedirectResponse
    {
        $name = $productType->name;
        $productCount = Product::where('type', $productType->slug)->count();

        if ($productCount > 0) {
            return redirect()->route('admin.product-types.index')
                ->with('error', 'Cannot delete product type "'.$name.'" because '.$productCount.' product(s) are currently assigned to it. Reassign or delete those products first.');
        }

        $productType->delete();

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Product type "'.$name.'" deleted successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(ProductType $productType): RedirectResponse
    {
        $productType->is_active = ! $productType->is_active;
        $productType->save();

        $status = $productType->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Product type "'.$productType->name.'" has been '.$status.'.');
    }
}

