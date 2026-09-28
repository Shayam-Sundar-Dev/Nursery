<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiInventoryController extends Controller
{
    /**
     * List variants with inventory health details.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductVariant::with(['product.category']);

        if ($filter = $request->input('filter')) {
            if ($filter === 'low_stock') {
                $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->where('stock_quantity', '>', 0);
            } elseif ($filter === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($filter === 'in_stock') {
                $query->whereColumn('stock_quantity', '>', 'low_stock_threshold');
            }
        }

        $perPage = (int) $request->input('per_page', 25);
        $variants = $query->orderBy('stock_quantity', 'asc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $variants->items(),
            'meta' => [
                'current_page' => $variants->currentPage(),
                'total' => $variants->total(),
            ],
        ]);
    }

    /**
     * Update stock level for a variant.
     */
    public function updateStock(Request $request, int $id): JsonResponse
    {
        $variant = ProductVariant::with('product')->find($id);

        if (! $variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found.',
            ], 404);
        }

        $validated = $request->validate([
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
        ]);

        $variant->update([
            'stock_quantity' => $validated['stock_quantity'],
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? $variant->low_stock_threshold,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Stock updated for SKU: {$variant->sku}.",
            'data' => $variant,
        ]);
    }
}
