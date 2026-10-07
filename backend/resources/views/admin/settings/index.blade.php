@extends('admin.layouts.admin')

@section('page_title', 'Site & Storefront Management')
@section('page_subtitle', 'Global Branding, Live Announcement Banner, Shipping Rules, Nursery Location & SEO')

@section('content')
<div x-data="{
    activeTab: '{{ request('tab', 'general') }}',
    announcementActive: {{ ($grouped['announcement']['announcement_active'] ?? false) ? 'true' : 'false' }},
    announcementText: '{{ addslashes($grouped['announcement']['announcement_text'] ?? '') }}',
    announcementBg: '{{ $grouped['announcement']['announcement_bg_color'] ?? '#1b4332' }}',
    announcementColor: '{{ $grouped['announcement']['announcement_text_color'] ?? '#d8f3dc' }}',
    weatherActive: {{ ($grouped['shipping']['weather_alert_active'] ?? false) ? 'true' : 'false' }},
    maintenanceMode: {{ ($grouped['status']['maintenance_mode'] ?? false) ? 'true' : 'false' }},
    logoPreview: '{{ $grouped['general']['site_logo'] ?? '' }}',
    faviconPreview: '{{ $grouped['general']['site_favicon'] ?? '' }}',
    ogPreview: '{{ $grouped['seo']['meta_og_image'] ?? '' }}',
    enableStateShipping: {{ ($grouped['shipping']['enable_state_shipping'] ?? true) ? 'true' : 'false' }},
    stateRates: {{ json_encode($grouped['shipping']['state_shipping_rates'] ?? \App\Models\SiteSetting::defaultIndianStateRates()) }},
    stateSearch: '',
    newStateName: '',
    newStateFee: '',
    newStateDays: '2-3 days'
}" class="space-y-6">

    <!-- Top Info Bar / Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-botanical-100 text-botanical-800 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Store Brand</span>
                <h3 class="text-base font-bold text-stone-900 truncate max-w-[180px]">{{ $grouped['general']['site_name'] ?? 'Verdant Flora' }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ ($grouped['announcement']['announcement_active'] ?? false) ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-500' }} flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            </div>
            <div>
                <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Announcement</span>
                <div class="text-sm font-bold {{ ($grouped['announcement']['announcement_active'] ?? false) ? 'text-emerald-700' : 'text-stone-500' }}">
                    {{ ($grouped['announcement']['announcement_active'] ?? false) ? 'Live On Storefront' : 'Currently Hidden' }}
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Free Shipping Over</span>
                <div class="text-lg font-bold text-stone-900">₹{{ number_format((float)($grouped['shipping']['free_shipping_threshold'] ?? 75), 2) }}</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ ($grouped['status']['maintenance_mode'] ?? false) ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }} flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Store Status</span>
                <div class="text-sm font-bold {{ ($grouped['status']['maintenance_mode'] ?? false) ? 'text-red-700' : 'text-emerald-700' }}">
                    {{ ($grouped['status']['maintenance_mode'] ?? false) ? 'Maintenance Mode' : 'Online & Open' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Live Announcement Bar Preview Simulator -->
    <div x-show="announcementActive"
         x-transition
         :style="'background-color: ' + announcementBg + '; color: ' + announcementColor"
         class="rounded-xl px-4 py-3 flex items-center justify-between text-xs sm:text-sm font-medium shadow-sm transition-all duration-200">
        <div class="flex items-center gap-2 overflow-hidden truncate">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/20">Preview</span>
            <span x-text="announcementText || 'Your storefront announcement message will appear here.'"></span>
        </div>
        <span class="text-xs underline opacity-80 shrink-0 ml-4 hidden sm:inline">Storefront Bar Simulator</span>
    </div>

    <!-- Settings Container with Tabs -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
        <!-- Navigation Tab Headers -->
        <div class="border-b border-stone-200 bg-stone-50/75 px-6 flex items-center gap-2 overflow-x-auto scrollbar-none">
            <button @click="activeTab = 'general'"
                    :class="activeTab === 'general' ? 'border-botanical-700 text-botanical-900 font-bold bg-white' : 'border-transparent text-stone-600 hover:text-stone-900 font-medium'"
                    class="flex items-center gap-2 py-4 px-4 border-b-2 text-sm whitespace-nowrap transition">
                <svg class="w-4 h-4 text-botanical-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                General & Branding
            </button>

            <button @click="activeTab = 'announcement'"
                    :class="activeTab === 'announcement' ? 'border-botanical-700 text-botanical-900 font-bold bg-white' : 'border-transparent text-stone-600 hover:text-stone-900 font-medium'"
                    class="flex items-center gap-2 py-4 px-4 border-b-2 text-sm whitespace-nowrap transition">
                <svg class="w-4 h-4 text-botanical-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Announcement Bar
            </button>

            <button @click="activeTab = 'shipping'"
                    :class="activeTab === 'shipping' ? 'border-botanical-700 text-botanical-900 font-bold bg-white' : 'border-transparent text-stone-600 hover:text-stone-900 font-medium'"
                    class="flex items-center gap-2 py-4 px-4 border-b-2 text-sm whitespace-nowrap transition">
                <svg class="w-4 h-4 text-botanical-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Shipping & Transit
            </button>

            <button @click="activeTab = 'contact'"
                    :class="activeTab === 'contact' ? 'border-botanical-700 text-botanical-900 font-bold bg-white' : 'border-transparent text-stone-600 hover:text-stone-900 font-medium'"
                    class="flex items-center gap-2 py-4 px-4 border-b-2 text-sm whitespace-nowrap transition">
                <svg class="w-4 h-4 text-botanical-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Nursery Location & Care
            </button>

            <button @click="activeTab = 'social'"
                    :class="activeTab === 'social' ? 'border-botanical-700 text-botanical-900 font-bold bg-white' : 'border-transparent text-stone-600 hover:text-stone-900 font-medium'"
                    class="flex items-center gap-2 py-4 px-4 border-b-2 text-sm whitespace-nowrap transition">
                <svg class="w-4 h-4 text-botanical-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                Social Channels
            </button>

            <button @click="activeTab = 'seo'"
                    :class="activeTab === 'seo' ? 'border-botanical-700 text-botanical-900 font-bold bg-white' : 'border-transparent text-stone-600 hover:text-stone-900 font-medium'"
                    class="flex items-center gap-2 py-4 px-4 border-b-2 text-sm whitespace-nowrap transition">
                <svg class="w-4 h-4 text-botanical-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                SEO & Meta Cards
            </button>

            <button @click="activeTab = 'status'"
                    :class="activeTab === 'status' ? 'border-botanical-700 text-botanical-900 font-bold bg-white' : 'border-transparent text-stone-600 hover:text-stone-900 font-medium'"
                    class="flex items-center gap-2 py-4 px-4 border-b-2 text-sm whitespace-nowrap transition">
                <svg class="w-4 h-4 text-botanical-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Store Status
            </button>

            <button @click="activeTab = 'oauth'"
                    :class="activeTab === 'oauth' ? 'border-botanical-700 text-botanical-900 font-bold bg-white' : 'border-transparent text-stone-600 hover:text-stone-900 font-medium'"
                    class="flex items-center gap-2 py-4 px-4 border-b-2 text-sm whitespace-nowrap transition">
                <svg class="w-4 h-4 text-botanical-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                OAuth &amp; Social Login
            </button>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-8">
            @csrf
            <input type="hidden" name="active_tab" :value="activeTab">

            <!-- 1. General & Branding -->
            <div x-show="activeTab === 'general'" class="space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-stone-900">Brand Identity & Typography</h3>
                    <p class="text-xs text-stone-500">Configure your nursery trade name, primary logo, and browser favicon.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Nursery Store Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="site_name"
                               value="{{ old('site_name', $grouped['general']['site_name'] ?? '') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-medium text-stone-900 text-sm"
                               placeholder="e.g. Verdant Botanical Nursery & Garden" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Tagline / Subtitle
                        </label>
                        <input type="text"
                               name="site_tagline"
                               value="{{ old('site_tagline', $grouped['general']['site_tagline'] ?? '') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-medium text-stone-900 text-sm"
                               placeholder="e.g. Live-Plant Specialized Delivery & Rare Specimens">
                    </div>
                </div>

                <!-- Currency Configuration -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-stone-100">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Currency Symbol
                        </label>
                        <input type="text"
                               name="currency_symbol"
                               value="{{ old('currency_symbol', $grouped['general']['currency_symbol'] ?? '₹') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-bold text-stone-900 text-sm"
                               placeholder="₹">
                        <p class="text-[11px] text-stone-400 mt-1">Default currency glyph displayed on products, orders, and coupons (e.g. ₹).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Currency Code
                        </label>
                        <input type="text"
                               name="currency_code"
                               value="{{ old('currency_code', $grouped['general']['currency_code'] ?? 'INR') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-bold text-stone-900 text-sm"
                               placeholder="INR">
                        <p class="text-[11px] text-stone-400 mt-1">ISO 4217 Currency Code (e.g. INR).</p>
                    </div>
                </div>

                <!-- Logo Upload -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-stone-100">
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider">
                            Storefront Primary Logo
                        </label>
                        <p class="text-xs text-stone-500">Upload SVG, PNG, or WebP logo file from your local system (Max 5MB).</p>

                        <div class="flex items-center gap-4">
                            <template x-if="logoPreview">
                                <div class="w-20 h-20 rounded-xl bg-stone-100 border border-stone-200 flex items-center justify-center p-2 overflow-hidden shrink-0">
                                    <img :src="logoPreview" alt="Logo Preview" class="max-h-full max-w-full object-contain">
                                </div>
                            </template>
                            <input type="file"
                                   name="site_logo_file"
                                   accept="image/*"
                                   @change="const file = $event.target.files[0]; if (file) logoPreview = URL.createObjectURL(file)"
                                   class="text-xs text-stone-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-botanical-50 file:text-botanical-800 hover:file:bg-botanical-100 cursor-pointer">
                        </div>

                        <div class="pt-2">
                            <label class="block text-[11px] font-semibold text-stone-500 mb-1">Or External CDN Logo URL:</label>
                            <input type="url"
                                   name="site_logo_url"
                                   value="{{ old('site_logo_url', $grouped['general']['site_logo'] ?? '') }}"
                                   @input="logoPreview = $event.target.value"
                                   class="w-full px-3 py-2 rounded-lg border border-stone-200 text-xs focus:ring-2 focus:ring-botanical-500"
                                   placeholder="https://your-cdn.com/logo.png">
                        </div>
                    </div>

                    <!-- Favicon Upload -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider">
                            Browser Favicon
                        </label>
                        <p class="text-xs text-stone-500">Upload 32x32 or 64x64 PNG/ICO icon for browser tab (Max 2MB).</p>

                        <div class="flex items-center gap-4">
                            <template x-if="faviconPreview">
                                <div class="w-12 h-12 rounded-xl bg-stone-100 border border-stone-200 flex items-center justify-center p-2 overflow-hidden shrink-0">
                                    <img :src="faviconPreview" alt="Favicon Preview" class="max-h-full max-w-full object-contain">
                                </div>
                            </template>
                            <input type="file"
                                   name="site_favicon_file"
                                   accept="image/*"
                                   @change="const file = $event.target.files[0]; if (file) faviconPreview = URL.createObjectURL(file)"
                                   class="text-xs text-stone-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-botanical-50 file:text-botanical-800 hover:file:bg-botanical-100 cursor-pointer">
                        </div>

                        <div class="pt-2">
                            <label class="block text-[11px] font-semibold text-stone-500 mb-1">Or External Favicon URL:</label>
                            <input type="url"
                                   name="site_favicon_url"
                                   value="{{ old('site_favicon_url', $grouped['general']['site_favicon'] ?? '') }}"
                                   @input="faviconPreview = $event.target.value"
                                   class="w-full px-3 py-2 rounded-lg border border-stone-200 text-xs focus:ring-2 focus:ring-botanical-500"
                                   placeholder="https://your-cdn.com/favicon.ico">
                        </div>
                    </div>
                </div>

                <!-- Footer Text -->
                <div class="pt-4 border-t border-stone-100 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Footer Mission & Description
                        </label>
                        <textarea name="footer_text"
                                  rows="2"
                                  class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900"
                                  placeholder="Describe your botanical mission and greenhouses...">{{ old('footer_text', $grouped['general']['footer_text'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Copyright Notice
                        </label>
                        <input type="text"
                               name="copyright_text"
                               value="{{ old('copyright_text', $grouped['general']['copyright_text'] ?? '') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900"
                               placeholder="e.g. Verdant Botanical Nursery & Garden © 2026. All rights reserved.">
                    </div>
                </div>
            </div>

            <!-- 2. Announcement Bar -->
            <div x-show="activeTab === 'announcement'" class="space-y-6">
                <input type="hidden" name="has_announcement_form" value="1">
                <div>
                    <h3 class="text-lg font-bold text-stone-900">Storefront Announcement Bar</h3>
                    <p class="text-xs text-stone-500">Display promotional drop announcements, coupon codes, and shipping alerts at the very top of your site.</p>
                </div>

                <div class="flex items-center gap-3 p-4 rounded-xl bg-botanical-50 border border-botanical-200">
                    <input type="checkbox"
                           id="announcement_active"
                           name="announcement_active"
                           value="1"
                           x-model="announcementActive"
                           class="w-4 h-4 text-botanical-700 border-stone-300 rounded focus:ring-botanical-500">
                    <label for="announcement_active" class="text-sm font-bold text-botanical-950 cursor-pointer">
                        Enable Announcement Bar on Storefront
                    </label>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Banner Message Text
                        </label>
                        <input type="text"
                               name="announcement_text"
                               x-model="announcementText"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900"
                               placeholder="e.g. 🌿 Spring Botanical Drop Live! Enjoy 15% off orders over ₹40 with code SPRINGBLOOM">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Call-to-Action Link Destination
                        </label>
                        <input type="text"
                               name="announcement_link"
                               value="{{ old('announcement_link', $grouped['announcement']['announcement_link'] ?? '') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900"
                               placeholder="/catalog/indoor-plants or https://...">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                                Background Color
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="color"
                                       name="announcement_bg_color"
                                       x-model="announcementBg"
                                       class="w-10 h-10 rounded-lg border border-stone-200 cursor-pointer p-0.5">
                                <input type="text"
                                       x-model="announcementBg"
                                       class="w-32 px-3 py-2 rounded-lg border border-stone-200 text-xs font-mono">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                                Text Color
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="color"
                                       name="announcement_text_color"
                                       x-model="announcementColor"
                                       class="w-10 h-10 rounded-lg border border-stone-200 cursor-pointer p-0.5">
                                <input type="text"
                                       x-model="announcementColor"
                                       class="w-32 px-3 py-2 rounded-lg border border-stone-200 text-xs font-mono">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Shipping & Botanical Transit -->
            <div x-show="activeTab === 'shipping'" class="space-y-6">
                <input type="hidden" name="has_shipping_form" value="1">
                <div>
                    <h3 class="text-lg font-bold text-stone-900">Shipping Thresholds & Temperature Protection</h3>
                    <p class="text-xs text-stone-500">Set automatic checkout thresholds, delivery fees, and cold-zone winter pack fees.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Free Shipping Threshold (₹)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-stone-400 font-bold">₹</span>
                            <input type="number"
                                   step="0.01"
                                   name="free_shipping_threshold"
                                   value="{{ old('free_shipping_threshold', $grouped['shipping']['free_shipping_threshold'] ?? '75.00') }}"
                                   class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-bold text-stone-900 text-sm">
                        </div>
                        <p class="text-[11px] text-stone-400 mt-1">Orders above this cart subtotal receive zero shipping charge.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Standard Shipping Fee (₹)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-stone-400 font-bold">₹</span>
                            <input type="number"
                                   step="0.01"
                                   name="default_shipping_fee"
                                   value="{{ old('default_shipping_fee', $grouped['shipping']['default_shipping_fee'] ?? '9.99') }}"
                                   class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-bold text-stone-900 text-sm">
                        </div>
                        <p class="text-[11px] text-stone-400 mt-1">Default expedited carrier fee for botanical shipments.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Thermal Insulation Pack Fee (₹)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-stone-400 font-bold">₹</span>
                            <input type="number"
                                   step="0.01"
                                   name="thermal_packaging_fee"
                                   value="{{ old('thermal_packaging_fee', $grouped['shipping']['thermal_packaging_fee'] ?? '4.50') }}"
                                   class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-bold text-stone-900 text-sm">
                        </div>
                        <p class="text-[11px] text-stone-400 mt-1">Automatic insulation packaging fee for live plants in freeze zones.</p>
                    </div>
                </div>

                <!-- Weather Alert Advisory -->
                <div class="pt-4 border-t border-stone-100 space-y-4">
                    <div class="flex items-center gap-3 p-4 rounded-xl bg-blue-50 border border-blue-200">
                        <input type="checkbox"
                               id="weather_alert_active"
                               name="weather_alert_active"
                               value="1"
                               x-model="weatherActive"
                               class="w-4 h-4 text-blue-600 border-stone-300 rounded focus:ring-blue-500">
                        <label for="weather_alert_active" class="text-sm font-bold text-blue-900 cursor-pointer">
                            Activate Weather Alert & Cold-Zone Thermal Insulation Advisory
                        </label>
                    </div>

                    <div x-show="weatherActive">
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Weather Advisory Message
                        </label>
                        <textarea name="weather_alert_message"
                                  rows="2"
                                  class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900">{{ old('weather_alert_message', $grouped['shipping']['weather_alert_message'] ?? '') }}</textarea>
                    </div>
                </div>

                <!-- Plant Guarantee -->
                <div class="pt-4 border-t border-stone-100 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Guarantee Period (Days)
                        </label>
                        <input type="number"
                               name="guarantee_days"
                               value="{{ old('guarantee_days', $grouped['shipping']['guarantee_days'] ?? '30') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-bold text-stone-900">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Guarantee Statement Badge
                        </label>
                        <input type="text"
                               name="guarantee_text"
                               value="{{ old('guarantee_text', $grouped['shipping']['guarantee_text'] ?? '30-Day Healthy Plant Arrival Guarantee') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900">
                    </div>
                </div>

                <!-- State-Based Delivery Charges -->
                <div class="pt-6 border-t border-stone-100 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h4 class="text-base font-bold text-stone-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-botanical-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                State-Based Delivery Charges
                            </h4>
                            <p class="text-xs text-stone-500">Configure customized live-plant carrier fees and transit times per destination state or region.</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                       name="enable_state_shipping"
                                       value="1"
                                       x-model="enableStateShipping"
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-stone-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-botanical-700"></div>
                                <span class="ml-2 text-xs font-bold text-stone-700">Enable State-Based Rates</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="enableStateShipping" x-transition class="space-y-4 bg-stone-50/70 p-5 rounded-2xl border border-stone-200">
                        <!-- Top actions: search and reset button -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="relative w-full sm:w-72">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <input type="text"
                                       x-model="stateSearch"
                                       placeholder="Filter states (e.g. Karnataka, Delhi)..."
                                       class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-stone-200 bg-white focus:ring-2 focus:ring-botanical-500">
                            </div>

                            <button type="button"
                                    @click="stateRates = {{ json_encode(\App\Models\SiteSetting::defaultIndianStateRates()) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-stone-200 bg-white hover:bg-stone-50 text-xs font-semibold text-stone-700 shadow-2xs transition cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-botanical-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Reset to Default Indian State Rates
                            </button>
                        </div>

                        <!-- Hidden JSON input submitted with the form -->
                        <input type="hidden" name="state_shipping_rates" :value="JSON.stringify(stateRates)">

                        <!-- Rates Table -->
                        <div class="bg-white rounded-xl border border-stone-200 overflow-hidden shadow-2xs max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-stone-200 text-xs text-left">
                                <thead class="bg-stone-100 text-stone-600 font-bold uppercase tracking-wider sticky top-0 z-10">
                                    <tr>
                                        <th class="py-2.5 px-4">State / Union Territory</th>
                                        <th class="py-2.5 px-4">Delivery Charge (₹)</th>
                                        <th class="py-2.5 px-4">Estimated Transit</th>
                                        <th class="py-2.5 px-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100 text-stone-800">
                                    <template x-for="(item, index) in stateRates.filter(r => !stateSearch || r.state.toLowerCase().includes(stateSearch.toLowerCase()))" :key="item.state">
                                        <tr class="hover:bg-sand-50/50 transition">
                                            <td class="py-2 px-4 font-bold text-stone-900" x-text="item.state"></td>
                                            <td class="py-2 px-4">
                                                <div class="relative w-28">
                                                    <span class="absolute left-2.5 top-1.5 text-stone-400 font-bold">₹</span>
                                                    <input type="number"
                                                           step="0.01"
                                                           min="0"
                                                           x-model.number="item.fee"
                                                           class="w-full pl-6 pr-2 py-1 rounded-lg border border-stone-200 text-xs font-bold text-stone-900 focus:ring-1 focus:ring-botanical-500">
                                                </div>
                                            </td>
                                            <td class="py-2 px-4">
                                                <input type="text"
                                                       x-model="item.estimated_days"
                                                       class="w-32 px-2.5 py-1 rounded-lg border border-stone-200 text-xs text-stone-700 focus:ring-1 focus:ring-botanical-500"
                                                       placeholder="e.g. 2-3 days">
                                            </td>
                                            <td class="py-2 px-4 text-right">
                                                <button type="button"
                                                        @click="stateRates = stateRates.filter(r => r.state !== item.state)"
                                                        class="text-red-500 hover:text-red-700 font-semibold p-1 hover:bg-red-50 rounded transition cursor-pointer"
                                                        title="Remove rate">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Add new state rate bar -->
                        <div class="pt-2 flex flex-wrap items-center gap-2">
                            <input type="text"
                                   x-model="newStateName"
                                   placeholder="New state name (e.g. Haryana)"
                                   class="px-3 py-1.5 text-xs rounded-xl border border-stone-200 bg-white flex-1 min-w-[150px] focus:ring-2 focus:ring-botanical-500">

                            <div class="relative w-28">
                                <span class="absolute left-2.5 top-1.5 text-stone-400 font-bold text-xs">₹</span>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       x-model="newStateFee"
                                       placeholder="Charge"
                                       class="w-full pl-6 pr-2 py-1.5 text-xs rounded-xl border border-stone-200 bg-white font-bold focus:ring-2 focus:ring-botanical-500">
                            </div>

                            <input type="text"
                                   x-model="newStateDays"
                                   placeholder="Transit (e.g. 2-3 days)"
                                   class="px-3 py-1.5 text-xs rounded-xl border border-stone-200 bg-white w-32 focus:ring-2 focus:ring-botanical-500">

                            <button type="button"
                                    @click="if(newStateName.trim() && newStateFee !== '') {
                                        const exists = stateRates.find(r => r.state.toLowerCase() === newStateName.trim().toLowerCase());
                                        if (exists) {
                                            exists.fee = parseFloat(newStateFee) || 0;
                                            exists.estimated_days = newStateDays.trim() || '2-3 days';
                                        } else {
                                            stateRates.push({
                                                state: newStateName.trim(),
                                                fee: parseFloat(newStateFee) || 0,
                                                estimated_days: newStateDays.trim() || '2-3 days'
                                            });
                                        }
                                        newStateName = '';
                                        newStateFee = '';
                                    }"
                                    class="px-4 py-1.5 rounded-xl bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                + Add State Rate
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Nursery Location & Care -->
            <div x-show="activeTab === 'contact'" class="space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-stone-900">Physical Nursery & Customer Care Channels</h3>
                    <p class="text-xs text-stone-500">Provide direct contact info, visiting hours, and greenhouse directions for customers.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Care & Support Email
                        </label>
                        <input type="email"
                               name="contact_email"
                               value="{{ old('contact_email', $grouped['contact']['contact_email'] ?? '') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Phone Hotline
                        </label>
                        <input type="text"
                               name="contact_phone"
                               value="{{ old('contact_phone', $grouped['contact']['contact_phone'] ?? '') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            WhatsApp Advisor
                        </label>
                        <input type="text"
                               name="contact_whatsapp"
                               value="{{ old('contact_whatsapp', $grouped['contact']['contact_whatsapp'] ?? '') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900">
                    </div>
                </div>

                <div class="space-y-4 pt-4 border-t border-stone-100">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Greenhouse Physical Address
                        </label>
                        <textarea name="nursery_address"
                                  rows="2"
                                  class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900">{{ old('nursery_address', $grouped['contact']['nursery_address'] ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                                Visiting & Support Hours
                            </label>
                            <input type="text"
                                   name="operating_hours"
                                   value="{{ old('operating_hours', $grouped['contact']['operating_hours'] ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                                Google Maps Link
                            </label>
                            <input type="url"
                                   name="maps_url"
                                   value="{{ old('maps_url', $grouped['contact']['maps_url'] ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 text-sm font-medium text-stone-900">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Social Channels -->
            <div x-show="activeTab === 'social'" class="space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-stone-900">Social Media & Gardening Community</h3>
                    <p class="text-xs text-stone-500">Link your active botanical social channels for footer and mobile app displays.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Instagram</label>
                        <input type="text" name="social_instagram" value="{{ old('social_instagram', $grouped['social']['social_instagram'] ?? '') }}" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Facebook</label>
                        <input type="text" name="social_facebook" value="{{ old('social_facebook', $grouped['social']['social_facebook'] ?? '') }}" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Pinterest</label>
                        <input type="text" name="social_pinterest" value="{{ old('social_pinterest', $grouped['social']['social_pinterest'] ?? '') }}" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">YouTube</label>
                        <input type="text" name="social_youtube" value="{{ old('social_youtube', $grouped['social']['social_youtube'] ?? '') }}" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">TikTok</label>
                        <input type="text" name="social_tiktok" value="{{ old('social_tiktok', $grouped['social']['social_tiktok'] ?? '') }}" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-medium">
                    </div>
                </div>
            </div>

            <!-- 6. SEO & Meta Cards -->
            <div x-show="activeTab === 'seo'" class="space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-stone-900">Search Engine Optimization & Social Sharing</h3>
                    <p class="text-xs text-stone-500">Fine-tune Google search snippets and OpenGraph preview cards for Facebook, X, and messaging apps.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Default Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $grouped['seo']['meta_title'] ?? '') }}" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Meta Description</label>
                        <textarea name="meta_description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-medium">{{ old('meta_description', $grouped['seo']['meta_description'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Meta Keywords (Comma-separated)</label>
                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $grouped['seo']['meta_keywords'] ?? '') }}" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-medium">
                    </div>

                    <div class="pt-4 border-t border-stone-100 space-y-3">
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider">Social Share Image (OpenGraph 1200x630)</label>
                        <div class="flex items-center gap-4">
                            <template x-if="ogPreview">
                                <div class="w-32 h-18 rounded-xl bg-stone-100 border border-stone-200 flex items-center justify-center p-1 overflow-hidden shrink-0">
                                    <img :src="ogPreview" alt="OG Preview" class="max-h-full max-w-full object-cover rounded-lg">
                                </div>
                            </template>
                            <input type="file"
                                   name="meta_og_image_file"
                                   accept="image/*"
                                   @change="const file = $event.target.files[0]; if (file) ogPreview = URL.createObjectURL(file)"
                                   class="text-xs text-stone-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-botanical-50 file:text-botanical-800 hover:file:bg-botanical-100 cursor-pointer">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. Store Status & Maintenance -->
            <div x-show="activeTab === 'status'" class="space-y-6">
                <input type="hidden" name="has_status_form" value="1">
                <div>
                    <h3 class="text-lg font-bold text-stone-900">Greenhouse Restock & Maintenance Mode</h3>
                    <p class="text-xs text-stone-500">Temporarily pause storefront ordering during inventory stocktakes or greenhouse migrations.</p>
                </div>

                <div class="flex items-center gap-3 p-4 rounded-xl {{ ($grouped['status']['maintenance_mode'] ?? false) ? 'bg-red-50 border-red-200 text-red-900' : 'bg-stone-50 border-stone-200 text-stone-800' }} border">
                    <input type="checkbox"
                           id="maintenance_mode"
                           name="maintenance_mode"
                           value="1"
                           x-model="maintenanceMode"
                           class="w-4 h-4 text-red-600 border-stone-300 rounded focus:ring-red-500">
                    <label for="maintenance_mode" class="text-sm font-bold cursor-pointer">
                        Put Storefront in Maintenance / Greenhouse Restock Mode
                    </label>
                </div>

                <div x-show="maintenanceMode" class="space-y-2">
                    <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider">
                        Notice Message Displayed to Visitors
                    </label>
                    <textarea name="maintenance_message"
                              rows="3"
                              class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-red-500 text-sm font-medium text-stone-900">{{ old('maintenance_message', $grouped['status']['maintenance_message'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- 8. OAuth & Social Login -->
            <div x-show="activeTab === 'oauth'" class="space-y-6">
                <input type="hidden" name="has_oauth_form" value="1">
                <div>
                    <h3 class="text-lg font-bold text-stone-900">OAuth &amp; Social Login Providers</h3>
                    <p class="text-xs text-stone-500">Configure OAuth credentials for social login on the storefront. Client IDs are public; secrets are stored server-side only and never exposed via the API.</p>
                </div>

                <!-- Google OAuth -->
                <div class="rounded-2xl border border-stone-200 bg-stone-50/50 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white border border-stone-200 flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-stone-900">Google</h4>
                                <p class="text-[11px] text-stone-500">Sign in with Google OAuth 2.0</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="google_oauth_enabled"
                                   value="1"
                                   class="sr-only peer"
                                   {{ ($grouped['oauth']['google_oauth_enabled'] ?? false) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-botanical-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-botanical-600"></div>
                            <span class="ms-2 text-xs font-semibold text-stone-600">Enabled</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Client ID</label>
                            <input type="text"
                                   name="google_client_id"
                                   value="{{ old('google_client_id', $grouped['oauth']['google_client_id'] ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-mono text-stone-900 text-sm"
                                   placeholder="xxxxxx.apps.googleusercontent.com">
                            <p class="text-[11px] text-stone-400 mt-1">Public — exposed to the storefront frontend.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Client Secret</label>
                            <input type="password"
                                   name="google_client_secret"
                                   autocomplete="new-password"
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-mono text-stone-900 text-sm"
                                   placeholder="Leave blank to keep existing secret">
                            <p class="text-[11px] text-red-400 mt-1">🔒 Private — never exposed via API. Leave blank to keep existing value.</p>
                        </div>
                    </div>
                    <p class="text-[11px] text-stone-400">
                        Get credentials at <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener" class="text-botanical-600 underline hover:text-botanical-800">console.cloud.google.com/apis/credentials</a>.
                    </p>
                </div>

                <!-- GitHub OAuth -->
                <div class="rounded-2xl border border-stone-200 bg-stone-50/50 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white border border-stone-200 flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5 text-stone-900" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-stone-900">GitHub</h4>
                                <p class="text-[11px] text-stone-500">Sign in with GitHub OAuth App</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="github_oauth_enabled"
                                   value="1"
                                   class="sr-only peer"
                                   {{ ($grouped['oauth']['github_oauth_enabled'] ?? false) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-botanical-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-botanical-600"></div>
                            <span class="ms-2 text-xs font-semibold text-stone-600">Enabled</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Client ID</label>
                            <input type="text"
                                   name="github_client_id"
                                   value="{{ old('github_client_id', $grouped['oauth']['github_client_id'] ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-mono text-stone-900 text-sm"
                                   placeholder="Ov23liXXXXXXXXXXXXXX">
                            <p class="text-[11px] text-stone-400 mt-1">Public — exposed to the storefront frontend.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Client Secret</label>
                            <input type="password"
                                   name="github_client_secret"
                                   autocomplete="new-password"
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-mono text-stone-900 text-sm"
                                   placeholder="Leave blank to keep existing secret">
                            <p class="text-[11px] text-red-400 mt-1">🔒 Private — never exposed via API. Leave blank to keep existing value.</p>
                        </div>
                    </div>
                    <p class="text-[11px] text-stone-400">
                        Get credentials at <a href="https://github.com/settings/developers" target="_blank" rel="noopener" class="text-botanical-600 underline hover:text-botanical-800">github.com/settings/developers</a>.
                    </p>
                </div>

                <!-- Facebook OAuth -->
                <div class="rounded-2xl border border-stone-200 bg-stone-50/50 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white border border-stone-200 flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5 text-[#1877F2]" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-stone-900">Facebook</h4>
                                <p class="text-[11px] text-stone-500">Sign in with Facebook Login</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="facebook_oauth_enabled"
                                   value="1"
                                   class="sr-only peer"
                                   {{ ($grouped['oauth']['facebook_oauth_enabled'] ?? false) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-botanical-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-botanical-600"></div>
                            <span class="ms-2 text-xs font-semibold text-stone-600">Enabled</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">App ID</label>
                            <input type="text"
                                   name="facebook_app_id"
                                   value="{{ old('facebook_app_id', $grouped['oauth']['facebook_app_id'] ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-mono text-stone-900 text-sm"
                                   placeholder="1234567890123456">
                            <p class="text-[11px] text-stone-400 mt-1">Public — exposed to the storefront frontend.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">App Secret</label>
                            <input type="password"
                                   name="facebook_app_secret"
                                   autocomplete="new-password"
                                   class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-hidden focus:ring-2 focus:ring-botanical-500 font-mono text-stone-900 text-sm"
                                   placeholder="Leave blank to keep existing secret">
                            <p class="text-[11px] text-red-400 mt-1">🔒 Private — never exposed via API. Leave blank to keep existing value.</p>
                        </div>
                    </div>
                    <p class="text-[11px] text-stone-400">
                        Get credentials at <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener" class="text-botanical-600 underline hover:text-botanical-800">developers.facebook.com/apps</a>.
                    </p>
                </div>
            </div>

            <!-- Form Action Footer -->
            <div class="pt-6 border-t border-stone-200 flex items-center justify-between">
                <p class="text-xs text-stone-400">Settings are cached in-memory and applied instantly to storefront visitors & checkout APIs.</p>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-botanical-700 hover:bg-botanical-800 text-white font-semibold text-sm shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
