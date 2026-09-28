<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSlider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminSliderController extends Controller
{
    /**
     * Display a listing of home page hero sliders.
     */
    public function index(Request $request): View
    {
        $query = HomeSlider::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('badge_text', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sliders = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $metrics = [
            'total_slides' => HomeSlider::count(),
            'active_slides' => HomeSlider::where('is_active', true)->count(),
        ];

        return view('admin.sliders.index', compact('sliders', 'metrics'));
    }

    /**
     * Show the form for creating a new hero banner slide.
     */
    public function create(): View
    {
        $nextOrder = (HomeSlider::max('sort_order') ?? -1) + 1;

        return view('admin.sliders.create', compact('nextOrder'));
    }

    /**
     * Store a newly created home slider in storage.
     */
    public function store(Request $request): RedirectResponse
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
            'sort_order' => ['required', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! $request->hasFile('slide_image') && empty($validated['image_url'])) {
            return back()->withInput()->withErrors([
                'slide_image' => 'Please upload a desktop banner image from your local system or provide a valid image URL.',
            ]);
        }

        // Process desktop image
        if ($request->hasFile('slide_image')) {
            $path = $request->file('slide_image')->store('sliders', 'public');
            $imageUrl = Storage::url($path);
        } else {
            $imageUrl = $validated['image_url'];
        }

        // Process mobile image if provided
        $mobileImageUrl = null;
        if ($request->hasFile('mobile_slide_image')) {
            $mPath = $request->file('mobile_slide_image')->store('sliders/mobile', 'public');
            $mobileImageUrl = Storage::url($mPath);
        } elseif (! empty($validated['mobile_image_url'])) {
            $mobileImageUrl = $validated['mobile_image_url'];
        }

        HomeSlider::create([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'image_url' => $imageUrl,
            'mobile_image_url' => $mobileImageUrl,
            'button_text' => $validated['button_text'] ?? null,
            'button_link' => $validated['button_link'] ?? null,
            'text_align' => $validated['text_align'],
            'theme' => $validated['theme'],
            'sort_order' => $validated['sort_order'],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.sliders.index')
            ->with('success', "Home banner '{$validated['title']}' created successfully.");
    }

    /**
     * Show the form for editing the specified slider.
     */
    public function edit(HomeSlider $slider): View
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    /**
     * Update the specified slider in storage.
     */
    public function update(Request $request, HomeSlider $slider): RedirectResponse
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
            'sort_order' => ['required', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Process desktop image
        $imageUrl = $slider->getRawOriginal('image_url') ?? $slider->image_url;
        if ($request->hasFile('slide_image')) {
            $path = $request->file('slide_image')->store('sliders', 'public');
            $imageUrl = Storage::url($path);
        } elseif ($request->filled('image_url')) {
            $imageUrl = $validated['image_url'];
        }

        // Process mobile image
        $mobileImageUrl = $slider->getRawOriginal('mobile_image_url') ?? $slider->mobile_image_url;
        if ($request->hasFile('mobile_slide_image')) {
            $mPath = $request->file('mobile_slide_image')->store('sliders/mobile', 'public');
            $mobileImageUrl = Storage::url($mPath);
        } elseif ($request->filled('mobile_image_url')) {
            $mobileImageUrl = $validated['mobile_image_url'];
        }

        $slider->update([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'image_url' => $imageUrl,
            'mobile_image_url' => $mobileImageUrl,
            'button_text' => $validated['button_text'] ?? null,
            'button_link' => $validated['button_link'] ?? null,
            'text_align' => $validated['text_align'],
            'theme' => $validated['theme'],
            'sort_order' => $validated['sort_order'],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.sliders.index')
            ->with('success', "Home banner '{$slider->title}' updated successfully.");
    }

    /**
     * Remove the specified slider from storage.
     */
    public function destroy(HomeSlider $slider): RedirectResponse
    {
        $title = $slider->title;
        $slider->delete();

        return redirect()->route('admin.sliders.index')
            ->with('success', "Home banner '{$title}' deleted successfully.");
    }

    /**
     * Quick toggle active status.
     */
    public function toggleActive(HomeSlider $slider): RedirectResponse
    {
        $slider->is_active = ! $slider->is_active;
        $slider->save();

        $status = $slider->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Home banner \"{$slider->title}\" is now {$status}.");
    }

    /**
     * Quick update sort order.
     */
    public function updateSortOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sliders' => ['required', 'array'],
            'sliders.*.id' => ['required', 'exists:home_sliders,id'],
            'sliders.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['sliders'] as $item) {
            HomeSlider::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return back()->with('success', 'Slide sort order updated successfully.');
    }
}
