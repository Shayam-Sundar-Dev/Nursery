<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    /**
     * Register a new botanical customer account.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'is_admin' => false,
            'is_active' => true,
        ]);

        // Automatically link any past guest orders matching this email
        Order::where('customer_email', $user->email)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        $token = $user->createToken('customer-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Botanical account created successfully.',
            'token' => $token,
            'user' => $this->formatUserData($user),
        ], 201);
    }

    /**
     * Customer login with email & password.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', strtolower(trim($validated['email'])))->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email credentials provided.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account access has been suspended.',
            ], 403);
        }

        // Link past guest orders matching email
        Order::where('customer_email', $user->email)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        $token = $user->createToken('customer-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Welcome back to your botanical garden.',
            'token' => $token,
            'user' => $this->formatUserData($user),
        ]);
    }

    /**
     * Social Authentication (Google, GitHub, Facebook).
     * Supports one-click OAuth token exchange or verified identity payload.
     */
    public function socialLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:google,github,facebook,apple',
            'provider_id' => 'nullable|string|max:150',
            'email' => 'required|email|max:150',
            'name' => 'required|string|max:150',
            'avatar' => 'nullable|string|max:500',
        ]);

        $email = strtolower(trim($validated['email']));

        $user = User::where('email', $email)->first();

        if ($user) {
            // Update social provider credentials if not yet set
            $user->update([
                'provider' => $validated['provider'],
                'provider_id' => $validated['provider_id'] ?? $user->provider_id,
                'avatar' => $validated['avatar'] ?? $user->avatar,
            ]);
        } else {
            // Auto-provision customer account via social sign-in
            $user = User::create([
                'name' => $validated['name'],
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'is_admin' => false,
                'is_active' => true,
                'provider' => $validated['provider'],
                'provider_id' => $validated['provider_id'] ?? Str::uuid()->toString(),
                'avatar' => $validated['avatar'] ?? null,
            ]);
        }

        // Attach previous orders to newly authenticated customer
        Order::where('customer_email', $user->email)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        $token = $user->createToken('customer-social-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => "Successfully signed in with {$validated['provider']}.",
            'token' => $token,
            'user' => $this->formatUserData($user),
        ]);
    }

    /**
     * Get current authenticated customer profile.
     */
    public function user(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => $this->formatUserData($user),
        ]);
    }

    /**
     * Get orders history for the authenticated customer.
     */
    public function orders(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $orders = Order::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('customer_email', $user->email);
        })
            ->with(['items.variant.product'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Logout and revoke Sanctum access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully signed out.',
        ]);
    }

    /**
     * Send a password reset link to the customer's email.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:150',
        ]);

        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email address.',
            ]);
        }

        // Email not found — return generic message to avoid user enumeration
        return response()->json([
            'success' => true,
            'message' => 'If that email is registered, a reset link has been sent.',
        ]);
    }

    /**
     * Reset the customer's password using the token from the email link.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email|max:150',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully. You can now sign in.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired reset token. Please request a new link.',
        ], 422);
    }

    /**
     * Update customer profile details and saved address.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:50',
            'street_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $user->fill(array_filter($validated, fn ($val) => ! is_null($val)));
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile and delivery address updated successfully.',
            'user' => $this->formatUserData($user),
        ]);
    }

    /**
     * Synchronize customer cart items across devices.
     */
    public function syncCart(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'items' => 'present|array',
            'merge' => 'nullable|boolean',
        ]);

        $incoming = $validated['items'];

        if ($request->boolean('merge')) {
            $existing = is_array($user->cart) ? $user->cart : [];
            $merged = collect($existing)->keyBy('variantId');
            foreach ($incoming as $item) {
                if (isset($item['variantId'])) {
                    if ($merged->has($item['variantId'])) {
                        $curr = $merged->get($item['variantId']);
                        $curr['quantity'] = max($curr['quantity'], $item['quantity']);
                        $merged->put($item['variantId'], $curr);
                    } else {
                        $merged->put($item['variantId'], $item);
                    }
                }
            }
            $user->cart = $merged->values()->toArray();
        } else {
            $user->cart = $incoming;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cart synced across devices.',
            'cart' => $user->cart,
        ]);
    }

    /**
     * Synchronize customer wishlist items across devices.
     */
    public function syncWishlist(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'items' => 'present|array',
            'merge' => 'nullable|boolean',
        ]);

        $incoming = $validated['items'];

        if ($request->boolean('merge')) {
            $existing = is_array($user->wishlist) ? $user->wishlist : [];
            $merged = collect($existing)->keyBy('id');
            foreach ($incoming as $item) {
                if (isset($item['id'])) {
                    $merged->put($item['id'], $item);
                }
            }
            $user->wishlist = $merged->values()->toArray();
        } else {
            $user->wishlist = $incoming;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist synced across devices.',
            'wishlist' => $user->wishlist,
        ]);
    }

    /**
     * Format consistent user data array.
     */
    private function formatUserData(User $user): array
    {
        $latestOrder = null;
        if (! $user->street_address || ! $user->phone) {
            $latestOrder = Order::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('customer_email', $user->email);
            })->latest()->first();
        }

        $phone = $user->phone ?: ($latestOrder?->customer_phone);
        $shippingAddr = is_array($latestOrder?->shipping_address) ? $latestOrder->shipping_address : [];
        $street = $user->street_address ?: ($shippingAddr['street'] ?? $shippingAddr['street_address'] ?? null);
        $city = $user->city ?: ($shippingAddr['city'] ?? null);
        $state = $user->state ?: ($shippingAddr['state'] ?? null);
        $postalCode = $user->postal_code ?: ($shippingAddr['postal_code'] ?? null);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $phone,
            'street_address' => $street,
            'city' => $city,
            'state' => $state,
            'postal_code' => $postalCode,
            'cart' => is_array($user->cart) ? $user->cart : [],
            'wishlist' => is_array($user->wishlist) ? $user->wishlist : [],
            'avatar' => $user->avatar,
            'provider' => $user->provider,
            'is_admin' => (bool) $user->is_admin,
            'role' => $user->role?->value,
            'role_label' => $user->roleLabel(),
            'orders_count' => Order::where('user_id', $user->id)->orWhere('customer_email', $user->email)->count(),
            'plants_count' => $user->userPlants()->count(),
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
