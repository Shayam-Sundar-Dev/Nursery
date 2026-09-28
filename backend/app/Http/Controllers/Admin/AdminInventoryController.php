<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminInventoryController extends Controller
{
    /**
     * Display botanical inventory and variant stock levels.
     */
    public function index(Request $request): View
    {
        $query = ProductVariant::with(['product.category']);

        // Search by SKU or product name
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('botanical_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status
        $filter = $request->input('filter', 'all');
        if ($filter === 'low_stock') {
            $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->where('stock_quantity', '>', 0);
        } elseif ($filter === 'out_of_stock') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($filter === 'in_stock') {
            $query->whereColumn('stock_quantity', '>', 'low_stock_threshold');
        }

        $variants = $query->orderBy('stock_quantity', 'asc')->paginate(20)->withQueryString();

        $stats = [
            'total_variants' => ProductVariant::count(),
            'in_stock' => ProductVariant::whereColumn('stock_quantity', '>', 'low_stock_threshold')->count(),
            'low_stock' => ProductVariant::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->where('stock_quantity', '>', 0)->count(),
            'out_of_stock' => ProductVariant::where('stock_quantity', '<=', 0)->count(),
        ];

        return view('admin.inventory.index', compact('variants', 'stats', 'filter'));
    }

    /**
     * Update stock quantity for a variant.
     */
    public function updateStock(Request $request, ProductVariant $variant): RedirectResponse
    {
        $validated = $request->validate([
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
        ]);

        $variant->update([
            'stock_quantity' => $validated['stock_quantity'],
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? $variant->low_stock_threshold,
        ]);

        return back()->with('success', "Stock updated for {$variant->product->name} ({$variant->title}) to {$validated['stock_quantity']} units.");
    }
}
