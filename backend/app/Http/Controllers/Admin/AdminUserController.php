<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Display a listing of administrative users.
     */
    public function index(Request $request): View
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

        $admins = $query->latest()->paginate(15)->withQueryString();
        $roles = AdminRole::adminRoles();

        return view('admin.users.index', compact('admins', 'roles'));
    }

    /**
     * Show the form for creating a new admin user.
     */
    public function create(): View
    {
        $roles = AdminRole::adminRoles();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created admin user in storage.
     */
    public function store(Request $request): RedirectResponse
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

        return redirect()->route('admin.users.index')
            ->with('success', "Admin user \"{$user->name}\" with role [{$user->roleLabel()}] created successfully.");
    }

    /**
     * Show the form for editing the specified admin user.
     */
    public function edit(User $user): View
    {
        if (! $user->is_admin) {
            abort(404, 'User is not an administrator.');
        }

        $roles = AdminRole::adminRoles();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified admin user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        if (! $user->is_admin) {
            abort(404, 'User is not an administrator.');
        }

        $adminRoleValues = array_map(fn (AdminRole $r) => $r->value, AdminRole::adminRoles());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in($adminRoleValues)],
            'password' => ['nullable', 'string', Password::min(8)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Safety check: protect against demoting or deactivating the last active super admin
        if ($user->role === AdminRole::SuperAdmin && ($validated['role'] !== AdminRole::SuperAdmin->value || ! $request->boolean('is_active', true))) {
            $otherSuperAdminsCount = User::where('role', AdminRole::SuperAdmin->value)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherSuperAdminsCount === 0) {
                return back()->with('error', 'Action prohibited: Cannot demote or deactivate the only remaining active Super Administrator.');
            }
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', "Administrator profile for \"{$user->name}\" was successfully updated.");
    }

    /**
     * Remove the specified admin user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if (! $user->is_admin) {
            abort(404, 'User is not an administrator.');
        }

        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Security safeguard: You cannot delete your own administrative account.');
        }

        // Prevent deleting the last super admin
        if ($user->role === AdminRole::SuperAdmin) {
            $superAdminsCount = User::where('role', AdminRole::SuperAdmin->value)->count();
            if ($superAdminsCount <= 1) {
                return back()->with('error', 'Cannot delete the sole Super Administrator.');
            }
        }

        $userName = $user->name;

        // If user has orders or companions, demote to customer instead of cascade deleting
        if ($user->orders()->exists() || $user->userPlants()->exists()) {
            $user->update([
                'is_admin' => false,
                'role' => AdminRole::Customer,
            ]);

            return redirect()->route('admin.users.index')
                ->with('warning', "Admin privileges for \"{$userName}\" were revoked. Historical customer orders and records preserved.");
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Administrator \"{$userName}\" has been permanently removed.");
    }
}
