<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PlantAttribute;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    /**
     * Display a listing of products and botanical inventory.
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'variants', 'plantAttributes']);

        // Search by name, botanical name, or SKU
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('botanical_name', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($vq) use ($search) {
                        $vq->where('sku', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by category
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Filter by stock status
        if ($stockStatus = $request->input('stock_status')) {
            if ($stockStatus === 'out_of_stock') {
                $query->whereDoesntHave('variants', function ($q) {
                    $q->where('stock_quantity', '>', 0);
                });
            } elseif ($stockStatus === 'low_stock') {
                $query->whereHas('variants', function ($q) {
                    $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                        ->where('stock_quantity', '>', 0);
                });
            } elseif ($stockStatus === 'in_stock') {
                $query->whereHas('variants', function ($q) {
                    $q->where('stock_quantity', '>', 0);
                });
            }
        }

        // Filter by publication
        if ($request->has('is_published') && $request->input('is_published') !== '') {
            $query->where('is_published', $request->boolean('is_published'));
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new botanical product.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'botanical_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['required', 'in:plant,planter,soil_fertilizer,accessory'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'primary_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,avif,gif,svg', 'max:10240'],
            'primary_image_url' => ['nullable', 'string', 'max:1000'],
            'gallery_images' => ['nullable'],
            'gallery_images.*' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,avif,gif,svg', 'max:10240'],
            'gallery_images_urls' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'requires_special_shipping' => ['nullable', 'boolean'],

            // Plant attributes (optional for non-plants, required/expected for plants)
            'light_requirement' => ['nullable', 'string', 'max:100'],
            'watering_frequency' => ['nullable', 'string', 'max:100'],
            'difficulty_level' => ['nullable', 'string', 'max:100'],
            'pet_friendly' => ['nullable', 'boolean'],
            'air_purifying' => ['nullable', 'boolean'],
            'pot_diameter_inches' => ['nullable', 'numeric', 'min:0'],
            'mature_size' => ['nullable', 'string', 'max:100'],
            'seasonality' => ['nullable', 'string', 'max:100'],
            'growth_rate' => ['nullable', 'string', 'max:100'],
            'ideal_temperature_range' => ['nullable', 'string', 'max:100'],
            'humidity_requirement' => ['nullable', 'string', 'max:100'],
            'care_instructions_light' => ['nullable', 'string'],
            'care_instructions_watering' => ['nullable', 'string'],
            'care_instructions_soil' => ['nullable', 'string'],
            'care_instructions_pro_tip' => ['nullable', 'string'],

            // Variants
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.sku' => ['required', 'string', 'distinct', 'max:100'],
            'variants.*.title' => ['required', 'string', 'max:255'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'variants.*.low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'variants.*.weight_grams' => ['nullable', 'integer', 'min:0'],
        ]);

        if (! $request->hasFile('primary_image') && empty($validated['primary_image_url'])) {
            return back()->withInput()->withErrors([
                'primary_image' => 'A primary botanical image must be uploaded from your system or specified via URL.',
            ]);
        }

        DB::transaction(function () use ($request, $validated) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            // Primary Image: upload file or use provided URL
            if ($request->hasFile('primary_image')) {
                $path = $request->file('primary_image')->store('products/primary', 'public');
                $primaryImageUrl = Storage::url($path);
            } else {
                $primaryImageUrl = $validated['primary_image_url'];
            }

            // Gallery Images: upload local files, URLs, or fallback
            $galleryImages = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $galleryFile) {
                    if ($galleryFile && $galleryFile->isValid()) {
                        $path = $galleryFile->store('products/gallery', 'public');
                        $galleryImages[] = Storage::url($path);
                    }
                }
            }

            if (! empty($validated['gallery_images_urls'])) {
                $urls = array_filter(array_map('trim', explode("\n", $validated['gallery_images_urls'])));
                $galleryImages = array_merge($galleryImages, $urls);
            }

            if (empty($galleryImages) && ! empty($request->input('gallery_images'))) {
                $input = $request->input('gallery_images');
                if (is_string($input)) {
                    $galleryImages = array_filter(array_map('trim', explode("\n", $input)));
                } elseif (is_array($input)) {
                    $galleryImages = array_filter($input, fn ($item) => is_string($item));
                }
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

            // Save Plant Attributes if type is plant or attributes provided
            if ($validated['type'] === 'plant' || ! empty($validated['light_requirement'])) {
                $careInstructions = [];
                if (! empty($validated['care_instructions_light'])) {
                    $careInstructions['light'] = $validated['care_instructions_light'];
                }
                if (! empty($validated['care_instructions_watering'])) {
                    $careInstructions['watering'] = $validated['care_instructions_watering'];
                }
                if (! empty($validated['care_instructions_soil'])) {
                    $careInstructions['soil'] = $validated['care_instructions_soil'];
                }
                if (! empty($validated['care_instructions_pro_tip'])) {
                    $careInstructions['pro_tip'] = $validated['care_instructions_pro_tip'];
                }

                PlantAttribute::create([
                    'product_id' => $product->id,
                    'light_requirement' => $validated['light_requirement'] ?? 'Bright Indirect',
                    'watering_frequency' => $validated['watering_frequency'] ?? 'Weekly',
                    'difficulty_level' => $validated['difficulty_level'] ?? 'Beginner Friendly',
                    'pet_friendly' => $request->boolean('pet_friendly'),
                    'air_purifying' => $request->boolean('air_purifying'),
                    'pot_diameter_inches' => $validated['pot_diameter_inches'] ?? 6.0,
                    'mature_size' => $validated['mature_size'] ?? null,
                    'seasonality' => $validated['seasonality'] ?? null,
                    'growth_rate' => $validated['growth_rate'] ?? 'Moderate',
                    'ideal_temperature_range' => $validated['ideal_temperature_range'] ?? '18°C - 26°C',
                    'humidity_requirement' => $validated['humidity_requirement'] ?? 'Average (40-60%)',
                    'care_instructions' => ! empty($careInstructions) ? $careInstructions : null,
                ]);
            }

            // Save Variants
            foreach ($validated['variants'] as $vData) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $vData['sku'],
                    'title' => $vData['title'],
                    'price' => $vData['price'],
                    'compare_at_price' => $vData['compare_at_price'] ?? null,
                    'stock_quantity' => $vData['stock_quantity'],
                    'low_stock_threshold' => $vData['low_stock_threshold'] ?? 5,
                    'weight_grams' => $vData['weight_grams'] ?? 1000,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product "'.$validated['name'].'" created successfully.');
    }

    /**
     * Show the specified product.
     */
    public function show(Product $product): View
    {
        return $this->edit($product);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $product->load(['category', 'variants', 'plantAttributes']);
        $categories = Category::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'botanical_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['required', 'in:plant,planter,soil_fertilizer,accessory'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'primary_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,avif,gif,svg', 'max:10240'],
            'primary_image_url' => ['nullable', 'string', 'max:1000'],
            'gallery_images' => ['nullable'],
            'gallery_images.*' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,avif,gif,svg', 'max:10240'],
            'gallery_images_urls' => ['nullable', 'string'],
            'existing_gallery_images' => ['nullable', 'array'],
            'existing_gallery_images.*' => ['string'],
            'is_featured' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'requires_special_shipping' => ['nullable', 'boolean'],

            // Plant attributes
            'light_requirement' => ['nullable', 'string', 'max:100'],
            'watering_frequency' => ['nullable', 'string', 'max:100'],
            'difficulty_level' => ['nullable', 'string', 'max:100'],
            'pet_friendly' => ['nullable', 'boolean'],
            'air_purifying' => ['nullable', 'boolean'],
            'pot_diameter_inches' => ['nullable', 'numeric', 'min:0'],
            'mature_size' => ['nullable', 'string', 'max:100'],
            'seasonality' => ['nullable', 'string', 'max:100'],
            'growth_rate' => ['nullable', 'string', 'max:100'],
            'ideal_temperature_range' => ['nullable', 'string', 'max:100'],
            'humidity_requirement' => ['nullable', 'string', 'max:100'],
            'care_instructions_light' => ['nullable', 'string'],
            'care_instructions_watering' => ['nullable', 'string'],
            'care_instructions_soil' => ['nullable', 'string'],
            'care_instructions_pro_tip' => ['nullable', 'string'],

            // Existing & new variants
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'exists:product_variants,id'],
            'variants.*.sku' => ['required', 'string', 'max:100'],
            'variants.*.title' => ['required', 'string', 'max:255'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'variants.*.low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'variants.*.weight_grams' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $product, $validated) {
            // Primary Image: upload replacement or keep existing/URL
            $primaryImageUrl = $product->getRawOriginal('primary_image_url') ?? $product->primary_image_url;
            if ($request->hasFile('primary_image')) {
                $path = $request->file('primary_image')->store('products/primary', 'public');
                $primaryImageUrl = Storage::url($path);
            } elseif ($request->filled('primary_image_url')) {
                $primaryImageUrl = $validated['primary_image_url'];
            }

            // Gallery Images: retain selected existing, upload new local files, or append URLs
            $galleryImages = $request->input('existing_gallery_images', []);
            if (! is_array($galleryImages)) {
                $galleryImages = [];
            }

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $galleryFile) {
                    if ($galleryFile && $galleryFile->isValid()) {
                        $path = $galleryFile->store('products/gallery', 'public');
                        $galleryImages[] = Storage::url($path);
                    }
                }
            }

            if (! empty($validated['gallery_images_urls'])) {
                $urls = array_filter(array_map('trim', explode("\n", $validated['gallery_images_urls'])));
                $galleryImages = array_merge($galleryImages, $urls);
            }

            if (empty($galleryImages) && ! empty($request->input('gallery_images'))) {
                $input = $request->input('gallery_images');
                if (is_string($input)) {
                    $galleryImages = array_filter(array_map('trim', explode("\n", $input)));
                } elseif (is_array($input)) {
                    $galleryImages = array_filter($input, fn ($item) => is_string($item));
                }
            }

            $product->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'botanical_name' => $validated['botanical_name'] ?? null,
                'type' => $validated['type'],
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'],
                'base_price' => $validated['base_price'],
                'primary_image_url' => $primaryImageUrl,
                'gallery_images' => array_values($galleryImages),
                'is_featured' => $request->boolean('is_featured'),
                'is_published' => $request->boolean('is_published'),
                'requires_special_shipping' => $request->boolean('requires_special_shipping'),
            ]);

            // Plant attributes
            if ($validated['type'] === 'plant' || ! empty($validated['light_requirement'])) {
                $careInstructions = [];
                if (! empty($validated['care_instructions_light'])) {
                    $careInstructions['light'] = $validated['care_instructions_light'];
                }
                if (! empty($validated['care_instructions_watering'])) {
                    $careInstructions['watering'] = $validated['care_instructions_watering'];
                }
                if (! empty($validated['care_instructions_soil'])) {
                    $careInstructions['soil'] = $validated['care_instructions_soil'];
                }
                if (! empty($validated['care_instructions_pro_tip'])) {
                    $careInstructions['pro_tip'] = $validated['care_instructions_pro_tip'];
                }

                PlantAttribute::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'light_requirement' => $validated['light_requirement'] ?? 'Bright Indirect',
                        'watering_frequency' => $validated['watering_frequency'] ?? 'Weekly',
                        'difficulty_level' => $validated['difficulty_level'] ?? 'Beginner Friendly',
                        'pet_friendly' => $request->boolean('pet_friendly'),
                        'air_purifying' => $request->boolean('air_purifying'),
                        'pot_diameter_inches' => $validated['pot_diameter_inches'] ?? 6.0,
                        'mature_size' => $validated['mature_size'] ?? null,
                        'seasonality' => $validated['seasonality'] ?? null,
                        'growth_rate' => $validated['growth_rate'] ?? 'Moderate',
                        'ideal_temperature_range' => $validated['ideal_temperature_range'] ?? '18°C - 26°C',
                        'humidity_requirement' => $validated['humidity_requirement'] ?? 'Average (40-60%)',
                        'care_instructions' => ! empty($careInstructions) ? $careInstructions : null,
                    ]
                );
            }

            // Sync Variants
            $submittedVariantIds = [];
            foreach ($validated['variants'] as $vData) {
                if (! empty($vData['id'])) {
                    $variant = ProductVariant::where('product_id', $product->id)->find($vData['id']);
                    if ($variant) {
                        $variant->update([
                            'sku' => $vData['sku'],
                            'title' => $vData['title'],
                            'price' => $vData['price'],
                            'compare_at_price' => $vData['compare_at_price'] ?? null,
                            'stock_quantity' => $vData['stock_quantity'],
                            'low_stock_threshold' => $vData['low_stock_threshold'] ?? 5,
                            'weight_grams' => $vData['weight_grams'] ?? 1000,
                            'is_active' => true,
                        ]);
                        $submittedVariantIds[] = $variant->id;
                    }
                } else {
                    $newVariant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $vData['sku'],
                        'title' => $vData['title'],
                        'price' => $vData['price'],
                        'compare_at_price' => $vData['compare_at_price'] ?? null,
                        'stock_quantity' => $vData['stock_quantity'],
                        'low_stock_threshold' => $vData['low_stock_threshold'] ?? 5,
                        'weight_grams' => $vData['weight_grams'] ?? 1000,
                        'is_active' => true,
                    ]);
                    $submittedVariantIds[] = $newVariant->id;
                }
            }

            // Deactivate or delete variants not in submitted list if no order items exist
            $remaining = ProductVariant::where('product_id', $product->id)
                ->whereNotIn('id', $submittedVariantIds)
                ->get();

            foreach ($remaining as $var) {
                if ($var->orderItems()->exists()) {
                    $var->update(['is_active' => false]);
                } else {
                    $var->delete();
                }
            }
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product "'.$product->name.'" updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;

        // Check if there are orders containing this product
        if ($product->orderItems()->exists()) {
            // Soft unpublish to prevent orphan order records
            $product->update(['is_published' => false]);

            return redirect()->route('admin.products.index')
                ->with('warning', 'Product "'.$name.'" is associated with historical orders and was unpublished instead of deleted.');
        }

        DB::transaction(function () use ($product) {
            $product->plantAttributes()->delete();
            $product->variants()->delete();
            $product->delete();
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product "'.$name.'" was deleted successfully.');
    }

    /**
     * Toggle the published status of the product.
     */
    public function togglePublish(Product $product): RedirectResponse
    {
        $product->is_published = ! $product->is_published;
        $product->save();

        $status = $product->is_published ? 'published' : 'unpublished';

        return back()->with('success', "Product \"{$product->name}\" is now {$status}.");
    }

    /**
     * Toggle the featured status of the product.
     */
    public function toggleFeatured(Product $product): RedirectResponse
    {
        $product->is_featured = ! $product->is_featured;
        $product->save();

        $status = $product->is_featured ? 'featured' : 'standard';

        return back()->with('success', "Product \"{$product->name}\" marked as {$status}.");
    }
}
