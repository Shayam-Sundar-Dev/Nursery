<!DOCTYPE html>
<html lang="en" class="h-full bg-stone-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Sign In | {{ $siteName }}</title>

    @if(!empty($siteFaviconUrl))
        <link rel="icon" type="image/x-icon" href="{{ $siteFaviconUrl }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        botanical: {
                            50: '#F4F7F4',
                            100: '#E5EDE6',
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
</head>
<body class="h-full flex items-center justify-center p-4 bg-gradient-to-br from-botanical-950 via-stone-900 to-botanical-900 antialiased">
    <div class="w-full max-w-md">
        <!-- Logo and Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-botanical-600 text-white shadow-2xl shadow-botanical-500/20 mb-4 ring-8 ring-botanical-900/50 overflow-hidden">
                @if(!empty($siteLogoUrl))
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="w-full h-full object-contain p-2">
                @else
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                @endif
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">{{ $siteName }} Admin Portal</h1>
            <p class="text-sm text-stone-400 mt-1">{{ $siteTagline }}</p>

            @if($maintenanceMode)
                <div class="mt-3 px-3 py-1.5 rounded-lg bg-amber-950/70 border border-amber-800/60 text-amber-300 text-xs inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    Storefront is in Maintenance Mode (Admin active)
                </div>
            @endif
        </div>

        <!-- Login Card -->
        <div class="bg-stone-800/90 border border-stone-700/60 rounded-2xl p-8 shadow-2xl backdrop-blur-md">
            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-xl bg-red-950/60 border border-red-800/60 text-red-300 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/60 border border-emerald-800/60 text-emerald-300 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-stone-300 mb-2">Botanist Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', 'admin@nursery.test') }}" required autofocus
                           class="w-full px-4 py-3 rounded-xl bg-stone-900/80 border border-stone-700 text-white placeholder-stone-500 focus:outline-none focus:ring-2 focus:ring-botanical-500 focus:border-transparent text-sm transition">
                    @error('email')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-stone-300">Password</label>
                    </div>
                    <input type="password" id="password" name="password" value="admin1234" required
                           class="w-full px-4 py-3 rounded-xl bg-stone-900/80 border border-stone-700 text-white placeholder-stone-500 focus:outline-none focus:ring-2 focus:ring-botanical-500 focus:border-transparent text-sm transition">
                    @error('password')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-stone-700 text-botanical-600 focus:ring-botanical-500 bg-stone-900">
                        <span class="text-xs text-stone-400">Remember session</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-4 rounded-xl bg-botanical-600 hover:bg-botanical-500 text-white font-semibold text-sm tracking-wide shadow-lg shadow-botanical-600/30 hover:shadow-botanical-500/40 transition active:scale-[0.99]">
                    Access Operations Dashboard
                </button>
            </form>

            <!-- Quick Demo Credential Helper -->
            <div class="mt-6 pt-6 border-t border-stone-700/60 text-center">
                <p class="text-xs text-stone-400 mb-2 font-medium">Default Botanical Admin Credentials:</p>
                <div class="inline-flex items-center gap-3 px-3 py-1.5 rounded-lg bg-stone-900/70 border border-stone-700 text-xs font-mono text-botanical-300">
                    <span>admin@nursery.test</span>
                    <span class="text-stone-500">&bull;</span>
                    <span>admin1234</span>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-stone-500 mt-6">
            Protected internal botanical management system &bull; Authorized personnel only
        </p>
    </div>
</body>
</html>
