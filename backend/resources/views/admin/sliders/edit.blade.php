@extends('admin.layouts.admin')

@section('title', 'Edit Home Page Hero Banner: ' . $slider->title)
@section('page_title', 'Edit Hero Banner Slide')
@section('page_subtitle', 'Update banner imagery, call-to-action link, and carousel sort order for ' . $slider->title)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    title: '{{ old('title', $slider->title) }}',
    subtitle: '{{ old('subtitle', $slider->subtitle) }}',
    badgeText: '{{ old('badge_text', $slider->badge_text) }}',
    buttonText: '{{ old('button_text', $slider->button_text) }}',
    buttonLink: '{{ old('button_link', $slider->button_link) }}',
    textAlign: '{{ old('text_align', $slider->text_align) }}',
    theme: '{{ old('theme', $slider->theme) }}',
    currentImageUrl: '{{ $slider->image_url }}',
    imagePreview: null,
    fileName: '',
    fileSize: '',
    showUrlFallback: false,
    handleImage(e) {
        const file = e.target.files[0];
        if (file) {
            this.fileName = file.name;
            this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
            this.imagePreview = URL.createObjectURL(file);
        }
    },
    clearImage() {
        this.imagePreview = null;
        this.fileName = '';
        this.fileSize = '';
        if (this.$refs.imageInput) {
            this.$refs.imageInput.value = '';
        }
    }
}">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.sliders.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-600 hover:text-stone-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Hero Banners
        </a>

        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $slider->statusBadgeClasses() }}">
            Status: {{ ucfirst($slider->status) }}
        </span>
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

    <!-- Interactive Live Banner Mockup Card -->
    <div class="rounded-3xl overflow-hidden border border-stone-300 shadow-lg relative min-h-[300px] flex items-center bg-stone-900">
        <!-- Background Image / Preview -->
        <img :src="imagePreview || currentImageUrl" class="absolute inset-0 w-full h-full object-cover">

        <!-- Dynamic Overlay according to theme -->
        <div class="absolute inset-0"
             :class="{
                 'bg-black/60': theme === 'dark',
                 'bg-botanical-950/70': theme === 'botanical',
                 'bg-white/80': theme === 'light'
             }"></div>

        <!-- Content Overlay -->
        <div class="relative z-10 w-full p-8 md:p-12"
             :class="{
                 'text-left': textAlign === 'left',
                 'text-center mx-auto': textAlign === 'center',
                 'text-right ml-auto': textAlign === 'right'
             }">
            <div class="max-w-xl" :class="{ 'mx-auto': textAlign === 'center', 'ml-auto': textAlign === 'right' }">
                <span x-show="badgeText" x-text="badgeText"
                      class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-3 shadow-2xs"
                      :class="theme === 'light' ? 'bg-botanical-800 text-white' : 'bg-emerald-400/90 text-botanical-950'"></span>

                <h1 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight"
                    :class="theme === 'light' ? 'text-stone-900' : 'text-white'"
                    x-text="title || 'Banner Headline'"></h1>

                <p class="text-sm sm:text-base mt-2 line-clamp-2"
                   :class="theme === 'light' ? 'text-stone-600' : 'text-stone-200'"
                   x-text="subtitle || 'Specimen subtitle and promotional highlights.'"></p>

                <div x-show="buttonText" class="mt-5">
                    <span class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm shadow-md"
                          :class="theme === 'light' ? 'bg-botanical-800 text-white' : 'bg-white text-stone-900'">
                        <span x-text="buttonText"></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </div>
            </div>
        </div>

        <span class="absolute bottom-3 right-4 text-[10px] font-mono text-white/50 z-20">Live Store Hero Simulation</span>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('admin.sliders.update', $slider) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Banner Copy & Visuals -->
        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-5">
            <div class="border-b border-stone-100 pb-3 flex items-center justify-between">
                <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-botanical-100 text-botanical-800 flex items-center justify-center text-xs font-bold">1</span>
                    Banner Imagery & Text
                </h3>
                <span class="text-xs font-mono text-stone-400">ID: {{ $slider->id }}</span>
            </div>

            <!-- Desktop Banner Image with Local Replacement Upload -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider">
                        Desktop Hero Image *
                    </label>
                    <button type="button" @click="showUrlFallback = !showUrlFallback" class="text-xs text-botanical-700 hover:underline inline-flex items-center gap-1 font-medium">
                        <span x-show="!showUrlFallback">Or edit direct URL</span>
                        <span x-show="showUrlFallback">Upload from local computer</span>
                    </button>
                </div>

                <div x-show="!showUrlFallback" class="space-y-3">
                    <!-- Current Active Image Card -->
                    <div x-show="!imagePreview" class="p-3 bg-stone-50 border border-stone-200 rounded-2xl flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <img src="{{ $slider->image_url }}" alt="{{ $slider->title }}" class="w-24 h-14 rounded-xl object-cover border border-stone-200 shadow-2xs">
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 mb-1">
                                    Current Active Image
                                </span>
                                <div class="text-xs text-stone-500 truncate max-w-md font-mono">{{ $slider->image_url }}</div>
                            </div>
                        </div>
                        <button type="button" @click="$refs.imageInput.click()" class="px-3.5 py-1.5 text-xs font-semibold text-botanical-700 bg-white border border-botanical-300 rounded-xl hover:bg-botanical-50 transition shadow-2xs">
                            Upload Replacement
                        </button>
                    </div>

                    <input type="file" name="slide_image" x-ref="imageInput" @change="handleImage($event)" accept="image/*" class="hidden">

                    <!-- Selected New Replacement Preview Card -->
                    <div x-show="imagePreview" x-cloak class="p-4 bg-botanical-50/60 border border-botanical-200 rounded-2xl flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <img :src="imagePreview" alt="Replacement Banner" class="w-24 h-14 rounded-xl object-cover border border-botanical-300 shadow-xs">
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-botanical-100 text-botanical-800 mb-1">
                                    New Replacement Photo Selected
                                </span>
                                <div class="text-sm font-bold text-stone-900" x-text="fileName"></div>
                                <div class="text-xs text-stone-500" x-text="fileSize"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="$refs.imageInput.click()" class="px-3 py-1.5 text-xs font-semibold text-botanical-700 bg-white border border-botanical-300 rounded-lg hover:bg-botanical-50 transition shadow-2xs">
                                Choose Different File
                            </button>
                            <button type="button" @click="clearImage()" class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition shadow-2xs">
                                Cancel Replacement
                            </button>
                        </div>
                    </div>
                </div>

                <div x-show="showUrlFallback" x-cloak class="space-y-1">
                    <input type="url" name="image_url" value="{{ old('image_url', $slider->image_url) }}" placeholder="https://images.unsplash.com/photo-..."
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                    <p class="text-xs text-stone-400">Direct remote image URL link</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Banner Headline Title *</label>
                    <input type="text" name="title" x-model="title" required
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-bold focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Badge / Tagline Pill</label>
                    <input type="text" name="badge_text" x-model="badgeText"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Subtitle / Secondary Hook</label>
                <textarea name="subtitle" x-model="subtitle" rows="2"
                          class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none"></textarea>
            </div>

            <!-- Call to Action -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2 border-t border-stone-100">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Button Label (CTA)</label>
                    <input type="text" name="button_text" x-model="buttonText"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Button Destination Link</label>
                    <input type="text" name="button_link" x-model="buttonLink"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-mono text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- 2. Styling, Sequencing & Schedule -->
        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-5">
            <div class="border-b border-stone-100 pb-3">
                <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold">2</span>
                    Banner Design Theme & Scheduling
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Color Theme *</label>
                    <select name="theme" x-model="theme" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        <option value="dark">Dark Charcoal Overlay</option>
                        <option value="botanical">Botanical Emerald Rainforest</option>
                        <option value="light">Crisp Light Glass</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Text Alignment *</label>
                    <select name="text_align" x-model="textAlign" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        <option value="left">Left Aligned (Standard)</option>
                        <option value="center">Centered</option>
                        <option value="right">Right Aligned</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Display Sort Sequence *</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $slider->sort_order) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-mono focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-stone-100">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Publish Schedule Start</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $slider->starts_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Publish Schedule End</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $slider->ends_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
            </div>

            <div class="pt-3 border-t border-stone-100">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $slider->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded text-botanical-600 focus:ring-botanical-500">
                    <div>
                        <span class="text-xs font-bold text-stone-800">Publish to Live Home Carousel</span>
                        <div class="text-[11px] text-stone-500">Slide will immediately appear on the home page if within the scheduled time window.</div>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.sliders.index') }}" class="px-5 py-2.5 rounded-xl border border-stone-200 text-sm font-semibold text-stone-700 hover:bg-stone-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-botanical-700 text-white font-semibold text-sm hover:bg-botanical-800 transition shadow-xs">
                Update Hero Banner
            </button>
        </div>
    </form>
</div>
@endsection
