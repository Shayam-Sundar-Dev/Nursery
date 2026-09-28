@extends('admin.layouts.admin')

@section('title', 'Add New Administrator')
@section('page_title', 'Create Administrator Account')
@section('page_subtitle', 'Invite a new team member and configure role-based access permissions')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-600 hover:text-stone-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Admin Team
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
            <div class="font-bold mb-1">Please correct the following errors:</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Personal & Login Details -->
        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-4">
            <h3 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-3">Admin Account Details</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Liam Thornwood" required
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Work Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="e.g. liam@nursery.test" required
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Secure Password *</label>
                <input type="password" name="password" required placeholder="Minimum 8 characters..."
                       class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                <p class="text-xs text-stone-400 mt-1">Must be at least 8 characters long.</p>
            </div>

            <label class="flex items-center gap-2 cursor-pointer pt-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="w-4 h-4 rounded text-botanical-600 focus:ring-botanical-500">
                <span class="text-xs font-semibold text-stone-700">Account is Active and authorized to log in immediately</span>
            </label>
        </div>

        <!-- Role Assignment Cards -->
        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-4">
            <div class="border-b border-stone-100 pb-3">
                <h3 class="text-base font-bold text-stone-900">Assign Operational Role *</h3>
                <p class="text-xs text-stone-500">Select the permission tier governing what sections of the nursery panel this user can manage</p>
            </div>

            <div class="space-y-3">
                @foreach($roles as $role)
                    <label class="flex items-start gap-3.5 p-4 rounded-xl border border-stone-200 hover:border-botanical-500 hover:bg-stone-50/60 cursor-pointer transition">
                        <input type="radio" name="role" value="{{ $role->value }}"
                               {{ old('role', 'botanist') === $role->value ? 'checked' : '' }}
                               class="mt-1 w-4 h-4 text-botanical-600 focus:ring-botanical-500">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-stone-900">{{ $role->label() }}</span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $role->badgeClasses() }}">
                                    {{ $role->value }}
                                </span>
                            </div>
                            <p class="text-xs text-stone-500 mt-1">{{ $role->description() }}</p>
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($role->permissions() as $perm)
                                    <span class="px-1.5 py-0.5 rounded bg-stone-100 text-stone-600 text-[10px] font-mono">
                                        ✓ {{ str_replace('_', ' ', $perm) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-100 text-sm font-semibold transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-botanical-700 hover:bg-botanical-800 text-white text-sm font-semibold shadow-md transition">
                Create Administrator
            </button>
        </div>
    </form>
</div>
@endsection
