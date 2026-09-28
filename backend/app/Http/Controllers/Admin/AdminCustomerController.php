<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    /**
     * Display a listing of registered customers / gardeners.
     */
    public function index(Request $request): View
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

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Display the specified customer, orders, and digital garden companions.
     */
    public function show(User $user): View
    {
        $user->load(['orders.items', 'userPlants.product.plantAttributes']);

        $totalSpent = $user->orders->where('status', '!=', 'cancelled')->sum('total_amount');

        return view('admin.customers.show', compact('user', 'totalSpent'));
    }
}
