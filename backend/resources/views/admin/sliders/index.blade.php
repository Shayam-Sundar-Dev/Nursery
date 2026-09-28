@extends('admin.layouts.admin')

@section('title', 'Home Page Slider View Management')
@section('page_title', 'Home Hero Banners & Sliders')
@section('page_subtitle', 'Curate promotional carousel banners, seasonal drop announcements, and call-to-action buttons for the store home page')

@section('content')
<div class="space-y-6">
    <!-- Top Action & Overview Metrics -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="p-2.5 rounded-xl bg-botanical-100 text-botanical-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </span>
            <div>
                <h2 class="text-xl font-bold text-stone-900">Home Carousel Banners</h2>
                <p class="text-xs text-stone-500">Showcase featured plants, care accessories, and seasonal collections</p>
            </div>
        </div>

        <a href="{{ route('admin.sliders.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-botanical-700 text-white font-semibold text-sm hover:bg-botanical-800 transition shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New Hero Banner
        </a>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Total Configured Slides</div>
                <div class="text-2xl font-black text-stone-900 mt-1">{{ number_format($metrics['total_slides']) }}</div>
                <div class="text-xs text-stone-400 mt-0.5">Hero slides in database</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center text-stone-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Live Active Slides on Home</div>
                <div class="text-2xl font-black text-emerald-700 mt-1">{{ number_format($metrics['active_slides']) }}</div>
                <div class="text-xs text-emerald-600 mt-0.5">Currently rendered in store carousel</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Sliders List -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-stone-100 flex items-center justify-between">
            <span class="text-xs font-bold text-stone-700 uppercase tracking-wider">Slide Display Sequence (Ordered by Sort Order)</span>
            <span class="text-xs text-stone-400">Slides display in ascending sort order (0, 1, 2...)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Banner Preview</th>
                        <th class="py-3.5 px-4">Headline & Content</th>
                        <th class="py-3.5 px-4">Call to Action</th>
                        <th class="py-3.5 px-4">Theme / Alignment</th>
                        <th class="py-3.5 px-4">Sort Order</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-sm">
                    @forelse($sliders as $slider)
                        <tr class="hover:bg-stone-50/60 transition">
                            <td class="py-3.5 px-4">
                                <div class="relative w-36 h-20 rounded-xl overflow-hidden border border-stone-200 bg-stone-100 shadow-2xs">
                                    <img src="{{ $slider->image_url }}" alt="{{ $slider->title }}" class="w-full h-full object-cover">
                                    @if($slider->badge_text)
                                        <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-black/70 text-white backdrop-blur-xs">
                                            {{ $slider->badge_text }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-bold text-stone-900 leading-snug">{{ $slider->title }}</div>
                                @if($slider->subtitle)
                                    <div class="text-xs text-stone-500 mt-1 line-clamp-2">{{ $slider->subtitle }}</div>
                                @endif
                                @if($slider->starts_at || $slider->ends_at)
                                    <div class="text-[11px] text-stone-400 mt-1">
                                        {{ $slider->starts_at ? $slider->starts_at->format('M d') : 'Now' }} -
                                        {{ $slider->ends_at ? $slider->ends_at->format('M d, Y') : 'Ongoing' }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4">
                                @if($slider->button_text)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-stone-100 text-stone-800 border border-stone-200">
                                        {{ $slider->button_text }}
                                        <svg class="w-3 h-3 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </span>
                                    @if($slider->button_link)
                                        <div class="text-[11px] font-mono text-stone-400 mt-1 truncate max-w-xs">{{ $slider->button_link }}</div>
                                    @endif
                                @else
                                    <span class="text-xs text-stone-400">No CTA button</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-xs">
                                <div class="capitalize font-medium text-stone-800">{{ $slider->theme }} Theme</div>
                                <div class="text-[11px] text-stone-500 capitalize mt-0.5">{{ $slider->text_align }} Aligned</div>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-stone-100 text-xs font-bold text-stone-800 font-mono">
                                    {{ $slider->sort_order }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $slider->statusBadgeClasses() }}">
                                    {{ ucfirst($slider->status) }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Toggle Active -->
                                    <form action="{{ route('admin.sliders.toggle-active', $slider) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-1.5 rounded-lg border border-stone-200 hover:bg-stone-50 text-stone-600 transition"
                                                title="{{ $slider->is_active ? 'Deactivate Slide' : 'Activate Slide' }}">
                                            @if($slider->is_active)
                                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            @else
                                                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @endif
                                        </button>
                                    </form>

                                    <!-- Edit -->
                                    <a href="{{ route('admin.sliders.edit', $slider) }}" class="p-1.5 rounded-lg border border-stone-200 hover:bg-stone-50 text-stone-600 transition" title="Edit Hero Banner">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete banner \'{{ $slider->title }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg border border-red-100 hover:bg-red-50 text-red-600 transition" title="Delete Banner">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-stone-500">
                                <svg class="w-12 h-12 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <div class="text-sm font-semibold text-stone-700">No home page banners created yet</div>
                                <p class="text-xs text-stone-400 mt-1">Upload high-resolution photography to craft your store's front carousel</p>
                                <a href="{{ route('admin.sliders.create') }}" class="inline-flex items-center gap-1.5 mt-3 text-xs font-semibold text-botanical-700 hover:underline">
                                    + Add First Hero Banner
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sliders->hasPages())
            <div class="p-4 border-t border-stone-100">
                {{ $sliders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
