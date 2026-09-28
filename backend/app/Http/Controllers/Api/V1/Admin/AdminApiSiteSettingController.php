<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminApiSiteSettingController extends Controller
{
    /**
     * Get all site settings grouped.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'settings' => SiteSetting::getAllGrouped(),
            'raw' => SiteSetting::allCached(),
        ]);
    }

    /**
     * Update settings headlessly.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['nullable', 'array'],
            'settings.*' => ['nullable'],
            'site_logo' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'site_favicon' => ['nullable', 'file', 'mimes:jpeg,png,ico,svg', 'max:2048'],
            'meta_og_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:8192'],
        ]);

        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('site', 'public');
            SiteSetting::set('site_logo', Storage::url($path), 'general', 'image');
        }

        if ($request->hasFile('site_favicon')) {
            $path = $request->file('site_favicon')->store('site', 'public');
            SiteSetting::set('site_favicon', Storage::url($path), 'general', 'image');
        }

        if ($request->hasFile('meta_og_image')) {
            $path = $request->file('meta_og_image')->store('site', 'public');
            SiteSetting::set('meta_og_image', Storage::url($path), 'seo', 'image');
        }

        if ($request->has('settings') && is_array($request->input('settings'))) {
            foreach ($request->input('settings') as $key => $value) {
                $existing = SiteSetting::where('key', $key)->first();
                $group = $existing ? $existing->group : 'general';
                $type = $existing ? $existing->type : (is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'string'));

                SiteSetting::set($key, $value, $group, $type);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Site settings updated successfully.',
            'settings' => SiteSetting::getAllGrouped(),
        ]);
    }
}
