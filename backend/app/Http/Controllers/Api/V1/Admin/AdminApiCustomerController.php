<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiCustomerController extends Controller
{
    /**
     * List customers and their botanical metrics.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::where('is_admin', false)
            ->withCount(['orders', 'userPlants'])
            ->withSum('orders', 'total_amount');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $customers = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    /**
     * Show single customer with digital plant roster and order history.
     */
    public function show(int $id): JsonResponse
    {
        $customer = User::where('is_admin', false)
            ->with(['orders.items', 'userPlants.product.plantAttributes'])
            ->find($id);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $customer,
        ]);
    }
}
