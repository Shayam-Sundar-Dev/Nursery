<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSlider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminApiSliderController extends Controller
{
    /**
     * List all sliders.
     */
    public function index(Request $request): JsonResponse
    {
        $query = HomeSlider::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sliders = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $sliders->items(),
            'meta' => [
                'current_page' => $sliders->currentPage(),
                'last_page' => $sliders->lastPage(),
                'total' => $sliders->total(),
                'per_page' => $sliders->perPage(),
            ],
            'summary' => [
                'total_slides' => HomeSlider::count(),
                'active_slides' => HomeSlider::where('is_active', true)->count(),
            ],
        ]);
    }

    /**
     * Create slider.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'badge_text' => ['nullable', 'string', 'max:100'],
            'slide_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,avif', 'max:10240'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'mobile_slide_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,avif', 'max:10240'],
            'mobile_image_url' => ['nullable', 'string', 'max:1000'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'text_align' => ['required', 'in:left,center,right'],
            'theme' => ['required', 'in:dark,light,botanical'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! $request->hasFile('slide_image') && empty($validated['image_url'])) {
            return response()->json([
                'success' => false,
                'message' => 'The slide_image file or image_url is required.',
            ], 422);
        }

        if ($request->hasFile('slide_image')) {
            $path = $request->file('slide_image')->store('sliders', 'public');
            $imageUrl = Storage::url($path);
        } else {
            $imageUrl = $validated['image_url'];
        }

        $mobileImageUrl = null;
        if ($request->hasFile('mobile_slide_image')) {
            $mPath = $request->file('mobile_slide_image')->store('sliders/mobile', 'public');
            $mobileImageUrl = Storage::url($mPath);
        } elseif (! empty($validated['mobile_image_url'])) {
            $mobileImageUrl = $validated['mobile_image_url'];
        }

        $slider = HomeSlider::create([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'image_url' => $imageUrl,
            'mobile_image_url' => $mobileImageUrl,
            'button_text' => $validated['button_text'] ?? null,
            'button_link' => $validated['button_link'] ?? null,
            'text_align' => $validated['text_align'],
            'theme' => $validated['theme'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Home banner '{$slider->title}' created successfully.",
            'data' => $slider,
        ], 201);
    }

    /**
     * Show slider.
     */
    public function show(HomeSlider $slider): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $slider,
        ]);
    }

    /**
     * Update slider.
     */
    public function update(Request $request, HomeSlider $slider): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'badge_text' => ['nullable', 'string', 'max:100'],
            'slide_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,avif', 'max:10240'],
            'image_url' => ['sometimes', 'required', 'string', 'max:1000'],
            'mobile_slide_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,avif', 'max:10240'],
            'mobile_image_url' => ['nullable', 'string', 'max:1000'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'text_align' => ['sometimes', 'required', 'in:left,center,right'],
            'theme' => ['sometimes', 'required', 'in:dark,light,botanical'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('slide_image')) {
            $path = $request->file('slide_image')->store('sliders', 'public');
            $validated['image_url'] = Storage::url($path);
            unset($validated['slide_image']);
        }

        if ($request->hasFile('mobile_slide_image')) {
            $mPath = $request->file('mobile_slide_image')->store('sliders/mobile', 'public');
            $validated['mobile_image_url'] = Storage::url($mPath);
            unset($validated['mobile_slide_image']);
        }

        $slider->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Home banner '{$slider->title}' updated successfully.",
            'data' => $slider,
        ]);
    }

    /**
     * Delete slider.
     */
    public function destroy(HomeSlider $slider): JsonResponse
    {
        $slider->delete();

        return response()->json([
            'success' => true,
            'message' => 'Home banner deleted successfully.',
        ]);
    }
}
