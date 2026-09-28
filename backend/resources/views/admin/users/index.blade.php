@extends('admin.layouts.admin')

@section('title', 'Admin Team & Roles')
@section('page_title', 'Admin Team & Role Access')
@section('page_subtitle', 'Manage administrative accounts, role-based authorization, and nursery operations permissions')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Search -->
    <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-1 items-center gap-3 w-full">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search admins by name or email..."
                       class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-botanical-500">
                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <select name="role" class="py-2 px-3 text-sm rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-botanical-500">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>
                        {{ $role->label() }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-botanical-800 hover:bg-botanical-900 text-white font-medium text-sm transition">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'role']))
                <a href="{{ route('admin.users.index') }}" class="text-xs text-stone-500 hover:text-stone-800 font-medium">Clear</a>
            @endif
        </form>

        <a href="{{ route('admin.users.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-botanical-700 hover:bg-botanical-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            <span>+ Add Admin User</span>
        </a>
    </div>

    <!-- Admin Users Table -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-600">
                <thead class="bg-stone-50 text-[11px] uppercase tracking-wider font-semibold text-stone-500 border-b border-stone-100">
                    <tr>
                        <th class="px-5 py-3">Administrator</th>
                        <th class="px-5 py-3">Assigned Role</th>
                        <th class="px-5 py-3">Account Status</th>
                        <th class="px-5 py-3">Created Date</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($admins as $adminUser)
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-botanical-100 text-botanical-900 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                        {{ substr($adminUser->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-stone-900 flex items-center gap-2">
                                            {{ $adminUser->name }}
                                            @if($adminUser->id === auth()->id())
                                                <span class="px-2 py-0.2 rounded text-[10px] font-bold bg-stone-100 text-stone-600">You</span>
                                            @endif
                                        </div>
                                        <div class="text-xs font-mono text-stone-500">{{ $adminUser->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $adminUser->roleBadge() }}">
                                    {{ $adminUser->roleLabel() }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                @if($adminUser->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-800 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Suspended
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-xs text-stone-500">
                                {{ $adminUser->created_at->format('M d, Y') }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $adminUser) }}"
                                       class="p-2 text-stone-600 hover:text-botanical-700 hover:bg-stone-100 rounded-lg transition"
                                       title="Edit Role & Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    @if($adminUser->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $adminUser) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to remove administrator {{ addslashes($adminUser->name) }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-400 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Delete Administrator">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-xs text-stone-400">
                                No administrators matching query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($admins->hasPages())
            <div class="p-4 border-t border-stone-100">
                {{ $admins->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
