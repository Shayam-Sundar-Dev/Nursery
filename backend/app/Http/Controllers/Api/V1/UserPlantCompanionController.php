<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserPlant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPlantCompanionController extends Controller
{
    /**
     * List plants in user's digital garden with watering schedules.
     */
    public function index(Request $request): JsonResponse
    {
        // For guest/demo mode, return public demo plants or user-specific plants if authenticated
        $userId = $request->user()?->id;

        $plants = UserPlant::with(['product.plantAttributes'])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->latest()
            ->get()
            ->map(function ($plant) {
                return [
                    'id' => $plant->id,
                    'nickname' => $plant->nickname,
                    'product_name' => $plant->product?->name ?? 'Custom Plant',
                    'botanical_name' => $plant->product?->botanical_name,
                    'adopted_at' => $plant->adopted_at,
                    'last_watered_at' => $plant->last_watered_at?->toIso8601String(),
                    'next_watering_due' => $plant->next_watering_due?->toIso8601String(),
                    'is_overdue' => $plant->isWateringOverdue(),
                    'reminder_frequency_days' => $plant->reminder_frequency_days,
                    'notes' => $plant->notes,
                    'photo_url' => $plant->photo_url ?? $plant->product?->primary_image_url,
                    'light_requirement' => $plant->product?->plantAttributes?->light_requirement ?? 'Bright Indirect',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $plants,
        ]);
    }

    /**
     * Adopt/register a plant into the digital garden companion.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|integer|exists:products,id',
            'nickname' => 'required|string|max:100',
            'reminder_frequency_days' => 'nullable|integer|min:1|max:60',
            'notes' => 'nullable|string|max:1000',
            'photo_url' => 'nullable|url|max:500',
        ]);

        $user = $request->user();

        $plant = UserPlant::create([
            'user_id' => $user ? $user->id : 1, // Fallback demo user
            'product_id' => $validated['product_id'] ?? null,
            'nickname' => $validated['nickname'],
            'reminder_frequency_days' => $validated['reminder_frequency_days'] ?? 7,
            'last_watered_at' => now(),
            'notes' => $validated['notes'] ?? null,
            'photo_url' => $validated['photo_url'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$plant->nickname} has been added to your Digital Jungle!",
            'plant' => $plant->load('product'),
        ], 201);
    }

    /**
     * Mark a plant as watered and recalculate the next watering cycle.
     */
    public function water(int $id): JsonResponse
    {
        $plant = UserPlant::findOrFail($id);
        $plant->update(['last_watered_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Quenched! Next watering scheduled in {$plant->reminder_frequency_days} days.",
            'last_watered_at' => $plant->last_watered_at->toIso8601String(),
            'next_watering_due' => $plant->next_watering_due->toIso8601String(),
        ]);
    }
}
