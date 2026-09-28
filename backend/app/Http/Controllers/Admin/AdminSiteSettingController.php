<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminSiteSettingController extends Controller
{
    /**
     * Display the site management settings dashboard.
     */
    public function index(): View
    {
        $settings = SiteSetting::allCached();
        $grouped = SiteSetting::getAllGrouped();

        return view('admin.settings.index', compact('settings', 'grouped'));
    }

    /**
     * Update site settings from the admin control panel.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // General
            'site_name' => ['nullable', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:500'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'site_logo_file' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'site_logo_url' => ['nullable', 'string', 'max:1000'],
            'site_favicon_file' => ['nullable', 'file', 'mimes:jpeg,png,ico,svg', 'max:2048'],
            'site_favicon_url' => ['nullable', 'string', 'max:1000'],
            'footer_text' => ['nullable', 'string', 'max:1000'],
            'copyright_text' => ['nullable', 'string', 'max:255'],

            // Announcement
            'announcement_active' => ['nullable'],
            'announcement_text' => ['nullable', 'string', 'max:500'],
            'announcement_link' => ['nullable', 'string', 'max:500'],
            'announcement_bg_color' => ['nullable', 'string', 'max:20'],
            'announcement_text_color' => ['nullable', 'string', 'max:20'],

            // Shipping & Botanical Transit
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],
            'default_shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'thermal_packaging_fee' => ['nullable', 'numeric', 'min:0'],
            'weather_alert_active' => ['nullable'],
            'weather_alert_message' => ['nullable', 'string', 'max:500'],
            'guarantee_days' => ['nullable', 'integer', 'min:0'],
            'guarantee_text' => ['nullable', 'string', 'max:255'],

            // Contact & Nursery
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_whatsapp' => ['nullable', 'string', 'max:50'],
            'nursery_address' => ['nullable', 'string', 'max:500'],
            'operating_hours' => ['nullable', 'string', 'max:255'],
            'maps_url' => ['nullable', 'string', 'max:1000'],

            // Social
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_facebook' => ['nullable', 'string', 'max:255'],
            'social_pinterest' => ['nullable', 'string', 'max:255'],
            'social_youtube' => ['nullable', 'string', 'max:255'],
            'social_tiktok' => ['nullable', 'string', 'max:255'],

            // SEO
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'meta_og_image_file' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:8192'],
            'meta_og_image_url' => ['nullable', 'string', 'max:1000'],

            // Status
            'maintenance_mode' => ['nullable'],
            'maintenance_message' => ['nullable', 'string', 'max:1000'],

            // OAuth Social Login
            'google_oauth_enabled' => ['nullable'],
            'google_client_id' => ['nullable', 'string', 'max:500'],
            'google_client_secret' => ['nullable', 'string', 'max:500'],
            'github_oauth_enabled' => ['nullable'],
            'github_client_id' => ['nullable', 'string', 'max:500'],
            'github_client_secret' => ['nullable', 'string', 'max:500'],
            'facebook_oauth_enabled' => ['nullable'],
            'facebook_app_id' => ['nullable', 'string', 'max:500'],
            'facebook_app_secret' => ['nullable', 'string', 'max:500'],
        ]);

        // 1. Process Logo
        if ($request->hasFile('site_logo_file')) {
            $path = $request->file('site_logo_file')->store('site', 'public');
            SiteSetting::set('site_logo', Storage::url($path), 'general', 'image');
        } elseif ($request->filled('site_logo_url')) {
            SiteSetting::set('site_logo', $request->input('site_logo_url'), 'general', 'image');
        }

        // 2. Process Favicon
        if ($request->hasFile('site_favicon_file')) {
            $path = $request->file('site_favicon_file')->store('site', 'public');
            SiteSetting::set('site_favicon', Storage::url($path), 'general', 'image');
        } elseif ($request->filled('site_favicon_url')) {
            SiteSetting::set('site_favicon', $request->input('site_favicon_url'), 'general', 'image');
        }

        // 3. Process OG Image
        if ($request->hasFile('meta_og_image_file')) {
            $path = $request->file('meta_og_image_file')->store('site', 'public');
            SiteSetting::set('meta_og_image', Storage::url($path), 'seo', 'image');
        } elseif ($request->filled('meta_og_image_url')) {
            SiteSetting::set('meta_og_image', $request->input('meta_og_image_url'), 'seo', 'image');
        }

        // 4. Update Scalar & Text Fields
        $fields = [
            // General
            'site_name' => ['general', 'string'],
            'site_tagline' => ['general', 'string'],
            'currency_symbol' => ['general', 'string'],
            'currency_code' => ['general', 'string'],
            'footer_text' => ['general', 'text'],
            'copyright_text' => ['general', 'string'],

            // Announcement
            'announcement_text' => ['announcement', 'string'],
            'announcement_link' => ['announcement', 'string'],
            'announcement_bg_color' => ['announcement', 'string'],
            'announcement_text_color' => ['announcement', 'string'],

            // Shipping
            'free_shipping_threshold' => ['shipping', 'number'],
            'default_shipping_fee' => ['shipping', 'number'],
            'thermal_packaging_fee' => ['shipping', 'number'],
            'weather_alert_message' => ['shipping', 'string'],
            'guarantee_days' => ['shipping', 'number'],
            'guarantee_text' => ['shipping', 'string'],

            // Contact
            'contact_email' => ['contact', 'string'],
            'contact_phone' => ['contact', 'string'],
            'contact_whatsapp' => ['contact', 'string'],
            'nursery_address' => ['contact', 'text'],
            'operating_hours' => ['contact', 'string'],
            'maps_url' => ['contact', 'string'],

            // Social
            'social_instagram' => ['social', 'string'],
            'social_facebook' => ['social', 'string'],
            'social_pinterest' => ['social', 'string'],
            'social_youtube' => ['social', 'string'],
            'social_tiktok' => ['social', 'string'],

            // SEO
            'meta_title' => ['seo', 'string'],
            'meta_description' => ['seo', 'text'],
            'meta_keywords' => ['seo', 'string'],

            // Status
            'maintenance_message' => ['status', 'text'],

            // OAuth — public client IDs only (secrets handled separately below)
            'google_client_id' => ['oauth', 'string'],
            'github_client_id' => ['oauth', 'string'],
            'facebook_app_id' => ['oauth', 'string'],
        ];

        foreach ($fields as $key => [$group, $type]) {
            if ($request->has($key)) {
                SiteSetting::set($key, $request->input($key), $group, $type);
            }
        }

        // 5. Update Booleans
        if ($request->has('has_announcement_form')) {
            SiteSetting::set('announcement_active', $request->boolean('announcement_active'), 'announcement', 'boolean');
        }

        if ($request->has('has_shipping_form')) {
            SiteSetting::set('weather_alert_active', $request->boolean('weather_alert_active'), 'shipping', 'boolean');
        }

        if ($request->has('has_status_form')) {
            SiteSetting::set('maintenance_mode', $request->boolean('maintenance_mode'), 'status', 'boolean');
        }

        // 6. OAuth Secrets — stored as PRIVATE (is_public = false)
        if ($request->has('has_oauth_form')) {
            // Enabled toggles (public)
            SiteSetting::set('google_oauth_enabled', $request->boolean('google_oauth_enabled'), 'oauth', 'boolean', true);
            SiteSetting::set('github_oauth_enabled', $request->boolean('github_oauth_enabled'), 'oauth', 'boolean', true);
            SiteSetting::set('facebook_oauth_enabled', $request->boolean('facebook_oauth_enabled'), 'oauth', 'boolean', true);

            // Secrets — is_public = FALSE (never exposed via API)
            if ($request->filled('google_client_secret')) {
                SiteSetting::setPrivate('google_client_secret', $request->input('google_client_secret'), 'oauth');
            }
            if ($request->filled('github_client_secret')) {
                SiteSetting::setPrivate('github_client_secret', $request->input('github_client_secret'), 'oauth');
            }
            if ($request->filled('facebook_app_secret')) {
                SiteSetting::setPrivate('facebook_app_secret', $request->input('facebook_app_secret'), 'oauth');
            }
        }

        $activeTab = $request->input('active_tab', 'general');

        return redirect()->route('admin.settings.index', ['tab' => $activeTab])
            ->with('success', 'Site configuration and botanical preferences updated successfully.');
    }
}
