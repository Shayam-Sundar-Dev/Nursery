<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminApiUserController extends Controller
{
    /**
     * List all administrators.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::admins();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $admins = $query->latest()->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $admins->items(),
            'meta' => [
                'current_page' => $admins->currentPage(),
                'total' => $admins->total(),
            ],
        ]);
    }

    /**
     * Create a new administrator.
     */
    public function store(Request $request): JsonResponse
    {
        $adminRoleValues = array_map(fn (AdminRole $r) => $r->value, AdminRole::adminRoles());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'role' => ['required', Rule::in($adminRoleValues)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => true,
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Administrator \"{$user->name}\" created with role [{$user->roleLabel()}].",
            'data' => $user,
        ], 201);
    }

    /**
     * Show single administrator details.
     */
    public function show(int $id): JsonResponse
    {
        $admin = User::admins()->find($id);

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $admin,
        ]);
    }

    /**
     * Update administrator profile or role.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $admin = User::admins()->find($id);

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator not found.',
            ], 404);
        }

        $adminRoleValues = array_map(fn (AdminRole $r) => $r->value, AdminRole::adminRoles());

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users')->ignore($admin->id)],
            'role' => ['sometimes', 'required', Rule::in($adminRoleValues)],
            'password' => ['nullable', 'string', Password::min(8)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = $validated;
        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        } else {
            unset($data['password']);
        }

        $admin->update($data);

        return response()->json([
            'success' => true,
            'message' => "Administrator \"{$admin->name}\" updated successfully.",
            'data' => $admin,
        ]);
    }

    /**
     * Delete or demote administrator.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $admin = User::admins()->find($id);

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator not found.',
            ], 404);
        }

        if ($admin->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own administrative account.',
            ], 422);
        }

        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Administrator deleted successfully.',
        ]);
    }
}
