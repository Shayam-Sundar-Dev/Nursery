@extends('admin.layouts.admin')

@section('title', 'Registered Gardeners & Customers')
@section('page_title', 'Customers & Digital Gardeners')
@section('page_subtitle', 'Browse customer orders and monitor adopted digital companion plants')

@section('content')
<div class="space-y-6">

    <!-- Search Header -->
    <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-xs">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex flex-col sm:flex-row items-center gap-4">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customers by name or email..."
                       class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-botanical-500">
                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-botanical-800 hover:bg-botanical-900 text-white font-medium text-sm transition">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.customers.index') }}" class="text-xs text-stone-500 hover:text-stone-800 font-medium">Clear</a>
            @endif
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-600">
                <thead class="bg-stone-50 text-[11px] uppercase tracking-wider font-semibold text-stone-500 border-b border-stone-100">
                    <tr>
                        <th class="px-5 py-3">Gardener Name</th>
                        <th class="px-5 py-3">Email Contact</th>
                        <th class="px-5 py-3">Orders Placed</th>
                        <th class="px-5 py-3">Lifetime Spend</th>
                        <th class="px-5 py-3">Adopted Plants</th>
                        <th class="px-5 py-3">Joined</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="px-5 py-4 font-bold text-stone-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-botanical-100 text-botanical-800 flex items-center justify-center font-bold text-xs">
                                        {{ substr($customer->name, 0, 1) }}
                                    </div>
                                    <span>{{ $customer->name }}</span>
                                </div>
                            </td>

                            <td class="px-5 py-4 font-mono text-xs text-stone-600">
                                {{ $customer->email }}
                            </td>

                            <td class="px-5 py-4 text-xs font-semibold text-stone-800">
                                {{ $customer->orders_count }} order(s)
                            </td>

                            <td class="px-5 py-4 font-bold text-stone-900 text-xs">
                                ₹{{ number_format($customer->orders_sum_total_amount ?? 0, 2) }}
                            </td>

                            <td class="px-5 py-4 text-xs">
                                @if($customer->user_plants_count > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        🌿 {{ $customer->user_plants_count }} companion(s)
                                    </span>
                                @else
                                    <span class="text-stone-400">0 adopted</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-xs text-stone-500">
                                {{ $customer->created_at->format('M d, Y') }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-stone-100 hover:bg-botanical-50 text-stone-700 hover:text-botanical-800 text-xs font-semibold transition">
                                    View Digital Garden &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-stone-400">
                                No registered shoppers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-stone-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
