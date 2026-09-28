<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlantAttribute;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminApiProductController extends Controller
{
    /**
     * List all products with administrative filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'variants', 'plantAttributes']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('botanical_name', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($vq) use ($search) {
                        $vq->where('sku', 'like', "%{$search}%");
                    });
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $products = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'per_page' => $products->perPage(),
            ],
        ]);
    }

    /**
     * Get single product details.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with(['category', 'variants', 'plantAttributes'])->find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Create a new product.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'botanical_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['required', 'in:plant,planter,soil_fertilizer,accessory'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'primary_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,avif,gif', 'max:10240'],
            'primary_image_url' => ['nullable', 'string', 'max:1000'],
            'gallery_images' => ['nullable'],
            'is_featured' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'requires_special_shipping' => ['nullable', 'boolean'],

            // Plant attributes
            'plant_attributes' => ['nullable', 'array'],
            'plant_attributes.light_requirement' => ['nullable', 'string'],
            'plant_attributes.watering_frequency' => ['nullable', 'string'],
            'plant_attributes.difficulty_level' => ['nullable', 'string'],
            'plant_attributes.pet_friendly' => ['nullable', 'boolean'],
            'plant_attributes.air_purifying' => ['nullable', 'boolean'],
            'plant_attributes.pot_diameter_inches' => ['nullable', 'numeric'],
            'plant_attributes.mature_size' => ['nullable', 'string'],
            'plant_attributes.care_instructions' => ['nullable', 'array'],

            // Variants
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.sku' => ['required', 'string', 'distinct'],
            'variants.*.title' => ['required', 'string'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.compare_at_price' => ['nullable', 'numeric'],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'variants.*.low_stock_threshold' => ['nullable', 'integer'],
            'variants.*.weight_grams' => ['nullable', 'integer'],
        ]);

        if (! $request->hasFile('primary_image') && empty($validated['primary_image_url'])) {
            return response()->json([
                'success' => false,
                'message' => 'The primary_image file or primary_image_url is required.',
                'errors' => ['primary_image' => ['Please upload a primary botanical image or provide a primary image URL.']],
            ], 422);
        }

        $product = DB::transaction(function () use ($validated, $request) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            // Primary Image
            if ($request->hasFile('primary_image')) {
                $path = $request->file('primary_image')->store('products/primary', 'public');
                $primaryImageUrl = Storage::url($path);
            } else {
                $primaryImageUrl = $validated['primary_image_url'];
            }

            // Gallery Images
            $galleryImages = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('products/gallery', 'public');
                        $galleryImages[] = Storage::url($path);
                    }
                }
            } elseif (! empty($validated['gallery_images']) && is_array($validated['gallery_images'])) {
                $galleryImages = $validated['gallery_images'];
            }

            $product = Product::create([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'botanical_name' => $validated['botanical_name'] ?? null,
                'slug' => $slug,
                'type' => $validated['type'],
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'],
                'base_price' => $validated['base_price'],
                'primary_image_url' => $primaryImageUrl,
                'gallery_images' => array_values($galleryImages),
                'is_featured' => $request->boolean('is_featured'),
                'is_published' => $request->boolean('is_published', true),
                'requires_special_shipping' => $request->boolean('requires_special_shipping'),
            ]);

            if (! empty($validated['plant_attributes']) || $validated['type'] === 'plant') {
                $attrs = $validated['plant_attributes'] ?? [];
                PlantAttribute::create([
                    'product_id' => $product->id,
                    'light_requirement' => $attrs['light_requirement'] ?? 'Bright Indirect',
                    'watering_frequency' => $attrs['watering_frequency'] ?? 'Weekly',
                    'difficulty_level' => $attrs['difficulty_level'] ?? 'Beginner Friendly',
                    'pet_friendly' => (bool) ($attrs['pet_friendly'] ?? false),
                    'air_purifying' => (bool) ($attrs['air_purifying'] ?? false),
                    'pot_diameter_inches' => $attrs['pot_diameter_inches'] ?? 6.0,
                    'mature_size' => $attrs['mature_size'] ?? null,
                    'care_instructions' => $attrs['care_instructions'] ?? null,
                ]);
            }

            foreach ($validated['variants'] as $v) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $v['sku'],
                    'title' => $v['title'],
                    'price' => $v['price'],
                    'compare_at_price' => $v['compare_at_price'] ?? null,
                    'stock_quantity' => $v['stock_quantity'],
                    'low_stock_threshold' => $v['low_stock_threshold'] ?? 5,
                    'weight_grams' => $v['weight_grams'] ?? 1000,
                    'is_active' => true,
                ]);
            }

            return $product->load(['category', 'variants', 'plantAttributes']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'botanical_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'type' => ['sometimes', 'required', 'in:plant,planter,soil_fertilizer,accessory'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['sometimes', 'required', 'string'],
            'base_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'primary_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,avif,gif', 'max:10240'],
            'primary_image_url' => ['sometimes', 'required', 'string', 'max:1000'],
            'gallery_images' => ['nullable'],
            'is_featured' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'requires_special_shipping' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('primary_image')) {
            $path = $request->file('primary_image')->store('products/primary', 'public');
            $validated['primary_image_url'] = Storage::url($path);
            unset($validated['primary_image']);
        }

        if ($request->hasFile('gallery_images')) {
            $galleryImages = [];
            foreach ($request->file('gallery_images') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('products/gallery', 'public');
                    $galleryImages[] = Storage::url($path);
                }
            }
            $validated['gallery_images'] = $galleryImages;
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product->load(['category', 'variants', 'plantAttributes']),
        ]);
    }

    /**
     * Delete product.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        if ($product->orderItems()->exists()) {
            $product->update(['is_published' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Product is referenced in existing orders. It has been unpublished instead of permanently deleted.',
            ]);
        }

        DB::transaction(function () use ($product) {
            $product->plantAttributes()->delete();
            $product->variants()->delete();
            $product->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }
}
