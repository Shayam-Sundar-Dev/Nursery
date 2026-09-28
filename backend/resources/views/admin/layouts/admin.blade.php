<!DOCTYPE html>
<html lang="en" class="h-full bg-stone-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') | {{ $siteName }}</title>

    @if(!empty($siteFaviconUrl))
        <link rel="icon" type="image/x-icon" href="{{ $siteFaviconUrl }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        botanical: {
                            50: '#F4F7F4',
                            100: '#E5EDE6',
                            200: '#C7DBC9',
                            300: '#9EBEA1',
                            400: '#6E9C73',
                            500: '#467B4C',
                            600: '#34613A',
                            700: '#2A4E2F',
                            800: '#1F3C24',
                            900: '#152919',
                            950: '#0B170E',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full font-sans antialiased text-stone-800" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex flex-col md:flex-row">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen"
             x-cloak
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-stone-900/60 backdrop-blur-sm md:hidden"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
               class="fixed md:static inset-y-0 left-0 z-50 w-72 bg-botanical-950 text-stone-200 flex flex-col transition-transform duration-200 ease-in-out shadow-2xl md:shadow-none flex-shrink-0">

            <!-- Nursery Brand Header -->
            <div class="h-20 px-6 flex items-center justify-between border-b border-botanical-900/80 bg-botanical-950">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-botanical-600 flex items-center justify-center text-white shadow-lg shadow-botanical-900/50 group-hover:bg-botanical-500 transition flex-shrink-0 overflow-hidden">
                        @if(!empty($siteLogoUrl))
                            <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="w-full h-full object-contain p-1">
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-white text-sm tracking-wide flex items-center gap-1.5 truncate" title="{{ $siteName }}">
                            <span class="truncate">{{ $siteName }}</span>
                            <span class="text-[10px] uppercase font-semibold tracking-wider px-1.5 py-0.5 rounded bg-botanical-700 text-botanical-100 flex-shrink-0">Admin</span>
                        </div>
                        <div class="text-[11px] text-botanical-300 truncate" title="{{ $siteTagline }}">Verdant Flora &bull; {{ $siteTagline }}</div>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="md:hidden text-stone-400 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-botanical-400">
                    Nursery Operations
                </div>

                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-botanical-800 text-white font-semibold' : 'text-stone-300 hover:bg-botanical-900 hover:text-white' }}">
                    <svg class="w-5 h-5 text-botanical-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard Overview
                </a>

                @if(auth()->user()->hasRole('super_admin', 'botanist'))
                    <!-- Products & Plants -->
                    <a href="{{ route('admin.products.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.products.*') ? 'bg-botanical-800 text-white font-semibold' : 'text-stone-300 hover:bg-botanical-900 hover:text-white' }}">
                        <svg class="w-5 h-5 text-botanical-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        Plants & Products
                    </a>

                    <!-- Categories -->
                    <a href="{{ route('admin.categories.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.categories.*') ? 'bg-botanical-800 text-white font-semibold' : 'text-stone-300 hover:bg-botanical-900 hover:text-white' }}">
                        <svg class="w-5 h-5 text-botanical-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Botanical Categories
                    </a>
                @endif

                @if(auth()->user()->hasRole('super_admin', 'fulfillment', 'support'))
                    <!-- Orders & Transit Tracking -->
                    <a href="{{ route('admin.orders.index') }}"
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.orders.*') ? 'bg-botanical-800 text-white font-semibold' : 'text-stone-300 hover:bg-botanical-900 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-botanical-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            Orders & Transit
                        </div>
                    </a>
                @endif

                @if(auth()->user()->hasRole('super_admin', 'botanist', 'fulfillment'))
                    <!-- Stock / Inventory -->
                    <a href="{{ route('admin.inventory.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.inventory.*') ? 'bg-botanical-800 text-white font-semibold' : 'text-stone-300 hover:bg-botanical-900 hover:text-white' }}">
                        <svg class="w-5 h-5 text-botanical-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Inventory & Stock
                    </a>
                @endif

                @if(auth()->user()->hasRole('super_admin', 'botanist'))
                    <div class="pt-4 px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-botanical-400">
                        Marketing & Storefront
                    </div>

                    <!-- Hero Sliders -->
                    <a href="{{ route('admin.sliders.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.sliders.*') ? 'bg-botanical-800 text-white font-semibold' : 'text-stone-300 hover:bg-botanical-900 hover:text-white' }}">
                        <svg class="w-5 h-5 text-botanical-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Hero Sliders
                    </a>

                    <!-- Coupons & Discounts -->
                    <a href="{{ route('admin.coupons.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.coupons.*') ? 'bg-botanical-800 text-white font-semibold' : 'text-stone-300 hover:bg-botanical-900 hover:text-white' }}">
                        <svg class="w-5 h-5 text-botanical-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Coupons & Discounts
                    </a>
                @endif

                @if(auth()->user()->hasRole('super_admin', 'support'))
                    <div class="pt-4 px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-botanical-400">
                        Community & Gardeners
                    </div>

                    <!-- Customers & Adopted Plants -->
                    <a href="{{ route('admin.customers.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.customers.*') ? 'bg-botanical-800 text-white font-semibold' : 'text-stone-300 hover:bg-botanical-900 hover:text-white' }}">
                        <svg class="w-5 h-5 text-botanical-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Customers & Gardeners
                    </a>
                @endif

                @if(auth()->user()->isSuperAdmin())
                    <div class="pt-4 px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-botanical-400">
                        System & Security
                    </div>

                    <!-- Admin Users & Roles -->
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-botanical-800 text-white font-semibold' : 'text-stone-300 hover:bg-botanical-900 hover:text-white' }}">
                        <svg class="w-5 h-5 text-botanical-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Admin Team & Roles
                    </a>

                    <!-- Site Management -->
                    <a href="{{ route('admin.settings.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.settings.*') ? 'bg-botanical-800 text-white font-semibold' : 'text-stone-300 hover:bg-botanical-900 hover:text-white' }}">
                        <svg class="w-5 h-5 text-botanical-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Site Management
                    </a>
                @endif

                <div class="pt-4 px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-botanical-400">
                    Ecosystem
                </div>

                <!-- API Docs shortcut -->
                <a href="/api/v1/products" target="_blank"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-stone-300 hover:bg-botanical-900 hover:text-white transition">
                    <svg class="w-5 h-5 text-botanical-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    Catalog API Feed
                    <svg class="w-3.5 h-3.5 ml-auto text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </nav>

            <!-- Admin Profile & Sign Out Footer -->
            <div class="p-4 border-t border-botanical-900 bg-botanical-950/80">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-botanical-700 text-botanical-100 flex items-center justify-center font-bold text-sm">
                            {{ substr(auth()->user()->name ?? 'Admin', 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-sm font-medium text-white truncate max-w-[120px]">{{ auth()->user()->name ?? 'Admin' }}</div>
                            <div class="text-[11px] text-botanical-400 truncate max-w-[120px] font-medium">{{ auth()->user()->roleLabel() }}</div>
                        </div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Sign Out" class="p-2 text-botanical-300 hover:text-white hover:bg-botanical-900 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Body Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-stone-50 overflow-y-auto">
            @if($maintenanceMode)
                <!-- Storefront Maintenance Mode Alert Banner -->
                <div class="bg-amber-500 text-stone-950 px-6 py-2.5 text-xs font-bold flex items-center justify-between shadow-xs border-b border-amber-600 sticky top-0 z-40">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-stone-950 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>STOREFRONT MAINTENANCE ACTIVE: Public visitor checkout & browsing are blocked. ({{ $maintenanceMessage }})</span>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.settings.index') }}" class="underline hover:text-white font-semibold flex-shrink-0 ml-3">Manage Settings &rarr;</a>
                    @endif
                </div>
            @endif

            @if($announcementActive && !empty($announcementText))
                <!-- Storefront Live Announcement Strip -->
                <div class="px-6 py-2 text-xs flex items-center justify-between border-b shadow-2xs z-30"
                     style="background-color: {{ $announcementBgColor }}; color: {{ $announcementTextColor }};">
                    <div class="flex items-center gap-2.5 truncate">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/20">
                            Live Storefront Banner
                        </span>
                        <span class="truncate font-medium">{{ $announcementText }}</span>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.settings.index') }}" class="text-[11px] font-semibold underline hover:opacity-80 flex-shrink-0 ml-3">
                            Configure &rarr;
                        </a>
                    @endif
                </div>
            @endif

            <!-- Top App Bar -->
            <header class="h-20 bg-white border-b border-stone-200 px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="md:hidden text-stone-600 hover:text-stone-900 p-2 rounded-lg hover:bg-stone-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-stone-900">@yield('page_title', 'Dashboard')</h1>
                        <p class="text-xs text-stone-500 hidden sm:block">@yield('page_subtitle', 'Live Nursery Operations, Climate-Controlled Transit & Plant Inventory')</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="/" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-stone-200 text-stone-600 hover:text-stone-900 hover:bg-stone-50 text-xs font-semibold transition" title="Visit customer-facing storefront">
                        <span class="w-2 h-2 rounded-full {{ $maintenanceMode ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                        <span class="hidden sm:inline">Storefront</span>
                        <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>

                    @if(auth()->user()->hasRole('super_admin', 'botanist'))
                        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-botanical-700 hover:bg-botanical-800 text-white text-sm font-semibold shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Add Plant / Product</span>
                        </a>
                    @endif
                </div>
            </header>

            <!-- Alerts Banner -->
            <div class="px-6 pt-6">
                @if(session('success'))
                    <div class="mb-4 flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-xs">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="text-sm font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 shadow-xs">
                        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="text-sm font-medium">{{ session('error') }}</div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-4 flex items-center gap-3 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 shadow-xs">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div class="text-sm font-medium">{{ session('warning') }}</div>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                @yield('content')
            </main>

            <!-- Admin Footer -->
            <footer class="py-4 px-6 border-t border-stone-200 text-center text-xs text-stone-400">
                {{ $copyrightText ?: ($siteName . ' © ' . date('Y')) }}
                &bull; {{ $footerText ?: 'Live-Plant Specialized Fulfillment' }}
                @if(!empty($contactEmail) || !empty($contactPhone))
                    <div class="mt-1 text-[11px] text-stone-400">
                        Nursery Care: {{ $contactEmail }} {{ !empty($contactPhone) ? '• ' . $contactPhone : '' }}
                    </div>
                @endif
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
