<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminApiProductTypeController extends Controller
{
    /**
     * Display a listing of product types.
     */
    public function index(): JsonResponse
    {
        $productTypes = ProductType::withCount('products')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $productTypes,
        ]);
    }

    /**
     * Store a newly created product type in storage.
     */
    public function store(Request $request): JsonResponse
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

        return response()->json([
            'success' => true,
            'message' => 'Product type created successfully.',
            'data' => $productType->loadCount('products'),
        ], 201);
    }

    /**
     * Display the specified product type.
     */
    public function show(int $id): JsonResponse
    {
        $productType = ProductType::withCount('products')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $productType,
        ]);
    }

    /**
     * Update the specified product type in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $productType = ProductType::findOrFail($id);

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

        return response()->json([
            'success' => true,
            'message' => 'Product type updated successfully.',
            'data' => $productType->fresh()->loadCount('products'),
        ]);
    }

    /**
     * Remove the specified product type from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $productType = ProductType::findOrFail($id);
        $productCount = Product::where('type', $productType->slug)->count();

        if ($productCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete product type because '.$productCount.' product(s) are currently assigned to it. Reassign products first.',
            ], 422);
        }

        $productType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product type deleted successfully.',
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(int $id): JsonResponse
    {
        $productType = ProductType::findOrFail($id);
        $productType->is_active = ! $productType->is_active;
        $productType->save();

        return response()->json([
            'success' => true,
            'message' => 'Product type status updated.',
            'data' => $productType,
        ]);
    }
}

