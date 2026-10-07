<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCatalogController extends Controller
{
    /**
     * Display a paginated listing of botanical products with faceted filters.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'nullable|string|max:100',
            'category_id' => 'nullable',
            'type' => 'nullable|string|max:100',
            'light' => 'nullable|string|max:100',
            'light_requirement' => 'nullable|string|max:100',
            'watering' => 'nullable|string|max:100',
            'watering_frequency' => 'nullable|string|max:100',
            'difficulty' => 'nullable|string|max:100',
            'pet_friendly' => 'nullable',
            'air_purifying' => 'nullable',
            'search' => 'nullable|string|max:100',
            'sort' => 'nullable|string|in:price_asc,price_desc,price_low,price_high,newest,featured,popular',
            'per_page' => 'nullable|integer|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Product::with(['category', 'plantAttributes', 'variants'])
            ->published()
            ->filterBotanical($validated);

        $sort = $request->input('sort', 'popular');
        match ($sort) {
            'price_asc', 'price_low' => $query->orderBy('base_price', 'asc'),
            'price_desc', 'price_high' => $query->orderBy('base_price', 'desc'),
            'newest' => $query->latest(),
            default => $query->orderByDesc('is_featured')->latest(),
        };

        $perPage = (int) ($request->input('per_page') ?? $request->input('limit') ?? 24);
        $products = $query->paginate($perPage);

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
     * Display single product with full botanical specs, variants, and pot pairings.
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['category', 'plantAttributes', 'variants'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        // If the product is a plant with a pot diameter, find matching decorative planters
        $matchingPlanters = [];
        if ($product->type === 'plant' && $product->plantAttributes?->pot_diameter_inches) {
            $diameter = $product->plantAttributes->pot_diameter_inches;
            $matchingPlanters = Product::with('variants')
                ->where('type', 'planter')
                ->published()
                ->where('id', '!=', $product->id)
                ->limit(4)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $product,
            'matching_planters' => $matchingPlanters,
        ]);
    }

    /**
     * Return category tree for navigation and filters.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::with(['children' => function ($q) {
            $q->where('is_active', true)
                ->withCount(['products' => function ($pq) {
                    $pq->published();
                }]);
        }])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->published();
            }])
            ->get();

        // Calculate total products_count including all descendant child categories
        $categories->each(function (Category $category) {
            $childIds = $this->getAllDescendantCategoryIds($category->id);
            if (! empty($childIds)) {
                $category->products_count = Product::whereIn('category_id', array_merge([$category->id], $childIds))
                    ->published()
                    ->count();
            }
        });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Display listing of active product types with live published product counts.
     */
    public function productTypes(): JsonResponse
    {
        $types = ProductType::active()
            ->withCount(['products' => function ($query) {
                $query->published();
            }])
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Recursively collect all descendant category IDs.
     */
    protected function getAllDescendantCategoryIds(int $parentId): array
    {
        $childrenIds = Category::where('parent_id', $parentId)->pluck('id')->all();
        $allIds = $childrenIds;

        foreach ($childrenIds as $childId) {
            $allIds = array_merge($allIds, $this->getAllDescendantCategoryIds($childId));
        }

        return array_unique($allIds);
    }

    /**
     * Plant Finder Quiz Recommendation Engine.
     * Evaluates light level, care schedule, and pet presence.
     */
    public function plantFinder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'light_level' => 'required|string|in:low,bright_indirect,direct_sun',
            'care_routine' => 'required|string|in:forgetful,moderate,attentive',
            'has_pets' => 'required|boolean',
        ]);

        $query = Product::with(['category', 'plantAttributes', 'variants'])
            ->where('type', 'plant')
            ->published();

        // Filter light level
        $lightRequirement = match ($validated['light_level']) {
            'low' => 'Low Light',
            'bright_indirect' => 'Bright Indirect',
            'direct_sun' => 'Direct Sun',
        };

        // Filter care routine (difficulty and watering)
        $difficulty = match ($validated['care_routine']) {
            'forgetful' => 'Beginner Friendly',
            'moderate' => 'Moderate',
            'attentive' => 'Expert',
        };

        $recommendations = $query->whereHas('plantAttributes', function ($q) use ($lightRequirement, $difficulty, $validated) {
            $q->where('light_requirement', $lightRequirement)
                ->where('difficulty_level', $difficulty);

            if ($validated['has_pets']) {
                $q->where('pet_friendly', true);
            }
        })->limit(6)->get();

        // Fallback 1: Beginner-friendly plants with same pet criteria
        if ($recommendations->isEmpty()) {
            $recommendations = Product::with(['category', 'plantAttributes', 'variants'])
                ->where('type', 'plant')
                ->published()
                ->whereHas('plantAttributes', function ($q) use ($validated) {
                    $q->where('difficulty_level', 'Beginner Friendly');
                    if ($validated['has_pets']) {
                        $q->where('pet_friendly', true);
                    }
                })
                ->limit(6)
                ->get();
        }

        // Fallback 2: Any popular published plants so user is never left with an empty screen
        if ($recommendations->isEmpty()) {
            $recommendations = Product::with(['category', 'plantAttributes', 'variants'])
                ->where('type', 'plant')
                ->published()
                ->limit(6)
                ->get();
        }

        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
        ]);
    }
}
