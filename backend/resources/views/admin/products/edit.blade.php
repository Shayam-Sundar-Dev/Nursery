@extends('admin.layouts.admin')

@section('title', 'Edit ' . $product->name)
@section('page_title', 'Edit Plant / Product')
@section('page_subtitle', 'Update botanical taxonomy, care instructions, and inventory variant quantities')

@section('content')
@php
    $care = $product->plantAttributes?->care_instructions ?? [];
    $existingVariants = $product->variants->map(function($v) {
        return [
            'id' => $v->id,
            'sku' => $v->sku,
            'title' => $v->title,
            'price' => (string) $v->price,
            'compare_at_price' => (string) ($v->compare_at_price ?? ''),
            'stock_quantity' => (string) $v->stock_quantity,
            'low_stock_threshold' => (string) $v->low_stock_threshold,
            'weight_grams' => (string) $v->weight_grams,
        ];
    })->values();
@endphp

<div class="max-w-5xl mx-auto space-y-6" x-data="{
    productType: '{{ old('type', $product->type) }}',
    variants: {{ json_encode(old('variants', $existingVariants)) }},
    addVariant() {
        this.variants.push({ id: null, sku: '', title: '', price: '{{ $product->base_price }}', compare_at_price: '', stock_quantity: '10', low_stock_threshold: '5', weight_grams: '1000' });
    },
    removeVariant(index) {
        if (this.variants.length > 1) {
            this.variants.splice(index, 1);
        }
    }
}">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-600 hover:text-stone-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Catalog
        </a>
        <a href="/api/v1/products/{{ $product->slug }}" target="_blank" class="text-xs text-botanical-700 hover:underline flex items-center gap-1">
            View Public API Record
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
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

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Specimen Information -->
        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-5">
            <div class="border-b border-stone-100 pb-3 flex items-center justify-between">
                <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-botanical-100 text-botanical-800 flex items-center justify-center text-xs font-bold">1</span>
                    Specimen Information
                </h3>
                <span class="text-xs font-mono text-stone-400">ID: {{ $product->id }} &bull; {{ $product->slug }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Common Plant / Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Botanical Scientific Name</label>
                    <input type="text" name="botanical_name" value="{{ old('botanical_name', $product->botanical_name) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm font-serif italic focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Category *</label>
                    <select name="category_id" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Product Type *</label>
                    <select name="type" x-model="productType" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        <option value="plant">Live Houseplant / Botanical</option>
                        <option value="planter">Pot / Ceramic Planter</option>
                        <option value="soil_fertilizer">Soil & Plant Nutrition</option>
                        <option value="accessory">Garden Tool & Accessory</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Base Starting Price (₹) *</label>
                    <input type="number" step="0.01" min="0" name="base_price" value="{{ old('base_price', $product->base_price) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Short Highlights / Hook</label>
                    <input type="text" name="short_description" value="{{ old('short_description', $product->short_description) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
            </div>

            <!-- Primary Botanical Image (Upload from Local System) -->
            <div x-data="{
                primaryPreview: null,
                primaryFileName: '',
                primaryFileSize: '',
                showUrlFallback: false,
                handlePrimary(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.primaryFileName = file.name;
                        this.primaryFileSize = (file.size / 1024).toFixed(1) + ' KB';
                        this.primaryPreview = URL.createObjectURL(file);
                    }
                },
                clearPrimary() {
                    this.primaryPreview = null;
                    this.primaryFileName = '';
                    this.primaryFileSize = '';
                    if (this.$refs.primaryInput) {
                        this.$refs.primaryInput.value = '';
                    }
                }
            }" class="space-y-2 pt-2 border-t border-stone-100">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider">
                        Primary Botanical Image *
                    </label>
                    <button type="button" @click="showUrlFallback = !showUrlFallback" class="text-xs text-botanical-700 hover:underline inline-flex items-center gap-1 font-medium">
                        <span x-show="!showUrlFallback">Or edit image URL</span>
                        <span x-show="showUrlFallback">Upload from local system</span>
                    </button>
                </div>

                <!-- Local File Upload Box & Current Image Preview -->
                <div x-show="!showUrlFallback" class="space-y-3">
                    @if($product->primary_image_url)
                        <div x-show="!primaryPreview" class="p-3 bg-stone-50 border border-stone-200 rounded-2xl flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-xl object-cover border border-stone-200 shadow-2xs">
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 mb-1">
                                        Current Primary Image Active
                                    </span>
                                    <div class="text-xs text-stone-500 truncate max-w-md font-mono">{{ $product->primary_image_url }}</div>
                                </div>
                            </div>
                            <button type="button" @click="$refs.primaryInput.click()" class="px-3.5 py-1.5 text-xs font-semibold text-botanical-700 bg-white border border-botanical-300 rounded-xl hover:bg-botanical-50 transition shadow-2xs">
                                Upload Replacement
                            </button>
                        </div>
                    @endif

                    <div x-show="!primaryPreview && !'{{ $product->primary_image_url }}'"
                         @click="$refs.primaryInput.click()"
                         class="border-2 border-dashed border-stone-300 hover:border-botanical-500 rounded-2xl p-6 text-center cursor-pointer transition bg-stone-50/60 hover:bg-botanical-50/20 group">
                        <div class="w-12 h-12 rounded-xl bg-white shadow-xs border border-stone-200 text-botanical-600 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="text-sm font-semibold text-stone-800">
                            Click to upload new specimen image from your local system
                        </div>
                        <p class="text-xs text-stone-500 mt-1">
                            PNG, JPG, WEBP, or AVIF (Up to 10MB)
                        </p>
                    </div>

                    <input type="file" name="primary_image" x-ref="primaryInput" @change="handlePrimary($event)" accept="image/*" class="hidden">

                    <!-- New Replacement Preview Card -->
                    <div x-show="primaryPreview" x-cloak class="p-4 bg-botanical-50/60 border border-botanical-200 rounded-2xl flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <img :src="primaryPreview" alt="Replacement Preview" class="w-20 h-20 rounded-xl object-cover border border-botanical-300 shadow-xs">
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-botanical-100 text-botanical-800 mb-1">
                                    New Replacement Image Selected
                                </span>
                                <div class="text-sm font-bold text-stone-900" x-text="primaryFileName"></div>
                                <div class="text-xs text-stone-500" x-text="primaryFileSize"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="$refs.primaryInput.click()" class="px-3 py-1.5 text-xs font-semibold text-botanical-700 bg-white border border-botanical-300 rounded-lg hover:bg-botanical-50 transition shadow-2xs">
                                Choose Different File
                            </button>
                            <button type="button" @click="clearPrimary()" class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition shadow-2xs">
                                Cancel Replacement
                            </button>
                        </div>
                    </div>
                </div>

                <!-- URL Fallback -->
                <div x-show="showUrlFallback" x-cloak class="space-y-1">
                    <input type="url" name="primary_image_url" value="{{ old('primary_image_url', $product->primary_image_url) }}" placeholder="https://images.unsplash.com/photo-..."
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                    <p class="text-xs text-stone-400">Direct remote image URL link</p>
                </div>
                @error('primary_image')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Comprehensive Botanical Description *</label>
                <textarea name="description" rows="4" required
                          class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">{{ old('description', $product->description) }}</textarea>
            </div>

            <!-- Botanical Gallery Images (Upload from Local System) -->
            <div x-data="{
                galleryFiles: [],
                showUrlFallback: false,
                handleGallery(e) {
                    const files = Array.from(e.target.files);
                    this.galleryFiles = files.map(file => ({
                        name: file.name,
                        size: (file.size / 1024).toFixed(1) + ' KB',
                        url: URL.createObjectURL(file)
                    }));
                },
                clearGallery() {
                    this.galleryFiles = [];
                    if (this.$refs.galleryInput) {
                        this.$refs.galleryInput.value = '';
                    }
                }
            }" class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider">
                        Botanical Gallery Images (Upload from Local System)
                    </label>
                    <button type="button" @click="showUrlFallback = !showUrlFallback" class="text-xs text-botanical-700 hover:underline inline-flex items-center gap-1 font-medium">
                        <span x-show="!showUrlFallback">Or edit URLs manually</span>
                        <span x-show="showUrlFallback">Upload from local system</span>
                    </button>
                </div>

                <div x-show="!showUrlFallback" class="space-y-3">
                    @if(is_array($product->gallery_images) && count($product->gallery_images) > 0)
                        <div class="p-4 bg-stone-50 rounded-2xl border border-stone-200 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-stone-700 uppercase tracking-wider">Existing Gallery Photos (Uncheck to Remove)</span>
                                <span class="text-xs text-stone-500">{{ count($product->gallery_images) }} saved photo(s)</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 pt-1">
                                @foreach($product->gallery_images as $idx => $img)
                                    <label class="relative block rounded-xl overflow-hidden border-2 border-stone-200 hover:border-botanical-500 cursor-pointer p-1 bg-white transition group shadow-2xs">
                                        <img src="{{ $img }}" class="w-full h-24 object-cover rounded-lg">
                                        <div class="mt-1 flex items-center justify-between px-1">
                                            <input type="checkbox" name="existing_gallery_images[]" value="{{ $img }}" checked class="w-4 h-4 rounded text-botanical-600 focus:ring-botanical-500">
                                            <span class="text-[10px] font-medium text-stone-600">Keep</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Local File Upload Box -->
                    <div @click="$refs.galleryInput.click()"
                         class="border-2 border-dashed border-stone-300 hover:border-botanical-500 rounded-2xl p-6 text-center cursor-pointer transition bg-stone-50/60 hover:bg-botanical-50/20 group">
                        <div class="w-12 h-12 rounded-xl bg-white shadow-xs border border-stone-200 text-botanical-600 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <div class="text-sm font-semibold text-stone-800">
                            Click to upload additional gallery photos from your local system
                        </div>
                        <p class="text-xs text-stone-500 mt-1">
                            Select multiple photos (leaves, stems, pot angles, growth progression)
                        </p>
                    </div>

                    <input type="file" name="gallery_images[]" x-ref="galleryInput" multiple @change="handleGallery($event)" accept="image/*" class="hidden">

                    <div x-show="galleryFiles.length > 0" x-cloak class="p-4 bg-stone-50 rounded-2xl border border-stone-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-stone-800" x-text="galleryFiles.length + ' new photo(s) selected from your local system'"></span>
                            <button type="button" @click="clearGallery()" class="text-xs text-red-600 hover:underline font-semibold">Clear new files</button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                            <template x-for="(item, idx) in galleryFiles" :key="idx">
                                <div class="rounded-xl overflow-hidden border border-stone-200 bg-white shadow-2xs">
                                    <img :src="item.url" class="w-full h-24 object-cover">
                                    <div class="p-1.5 text-[10px] font-mono text-stone-600 truncate" x-text="item.name"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div x-show="showUrlFallback" x-cloak class="space-y-1">
                    <textarea name="gallery_images_urls" rows="2" placeholder="https://example.com/leaf-detail.jpg&#10;https://example.com/in-pot.jpg"
                              class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none font-mono text-xs">{{ old('gallery_images_urls') }}</textarea>
                    <p class="text-xs text-stone-400">One URL per line</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-stone-100">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $product->is_published) ? 'checked' : '' }} class="w-4 h-4 rounded text-botanical-600 focus:ring-botanical-500">
                    <span class="text-xs font-semibold text-stone-700">Published to Live Catalog</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="w-4 h-4 rounded text-botanical-600 focus:ring-botanical-500">
                    <span class="text-xs font-semibold text-stone-700">Featured in Highlights</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="requires_special_shipping" value="1" {{ old('requires_special_shipping', $product->requires_special_shipping) ? 'checked' : '' }} class="w-4 h-4 rounded text-botanical-600 focus:ring-botanical-500">
                    <span class="text-xs font-semibold text-stone-700">Requires Climate Transit Box</span>
                </label>
            </div>
        </div>

        <!-- 2. Botanical Plant Attributes & Care Guide -->
        <div x-show="productType === 'plant'" x-cloak class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-5">
            <div class="border-b border-stone-100 pb-3">
                <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold">2</span>
                    Botanical Specs & Care Requirements
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Light Requirement</label>
                    <select name="light_requirement" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        @php $currentLight = old('light_requirement', $product->plantAttributes?->light_requirement); @endphp
                        <option value="Bright Indirect" {{ $currentLight == 'Bright Indirect' ? 'selected' : '' }}>Bright Indirect</option>
                        <option value="Low to Medium Light" {{ $currentLight == 'Low to Medium Light' ? 'selected' : '' }}>Low to Medium Light</option>
                        <option value="Full Sun" {{ $currentLight == 'Full Sun' ? 'selected' : '' }}>Full Sun</option>
                        <option value="Medium Indirect" {{ $currentLight == 'Medium Indirect' ? 'selected' : '' }}>Medium Indirect</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Watering Frequency</label>
                    <select name="watering_frequency" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        @php $currentWater = old('watering_frequency', $product->plantAttributes?->watering_frequency); @endphp
                        <option value="Weekly" {{ $currentWater == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="Bi-weekly" {{ $currentWater == 'Bi-weekly' ? 'selected' : '' }}>Bi-weekly</option>
                        <option value="Every 2-3 Weeks" {{ $currentWater == 'Every 2-3 Weeks' ? 'selected' : '' }}>Every 2-3 Weeks</option>
                        <option value="Monthly" {{ $currentWater == 'Monthly' ? 'selected' : '' }}>Monthly (Drought tolerant)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Difficulty Level</label>
                    <select name="difficulty_level" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                        @php $currentDiff = old('difficulty_level', $product->plantAttributes?->difficulty_level); @endphp
                        <option value="Beginner Friendly" {{ $currentDiff == 'Beginner Friendly' ? 'selected' : '' }}>Beginner Friendly</option>
                        <option value="Moderate" {{ $currentDiff == 'Moderate' ? 'selected' : '' }}>Moderate</option>
                        <option value="Expert Collector" {{ $currentDiff == 'Expert Collector' ? 'selected' : '' }}>Expert Collector</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Pot Diameter (Inches)</label>
                    <input type="number" step="0.5" name="pot_diameter_inches" value="{{ old('pot_diameter_inches', $product->plantAttributes?->pot_diameter_inches ?? 6.0) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Mature Size</label>
                    <input type="text" name="mature_size" value="{{ old('mature_size', $product->plantAttributes?->mature_size) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Growth Rate</label>
                    <input type="text" name="growth_rate" value="{{ old('growth_rate', $product->plantAttributes?->growth_rate ?? 'Moderate') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="flex items-center gap-2 p-3 rounded-xl border border-stone-200 hover:bg-stone-50 cursor-pointer">
                    <input type="checkbox" name="pet_friendly" value="1" {{ old('pet_friendly', $product->plantAttributes?->pet_friendly) ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <span class="text-xs font-bold text-stone-800">Pet Friendly Non-Toxic</span>
                        <p class="text-[11px] text-stone-500">Safe around curious cats and dogs</p>
                    </div>
                </label>

                <label class="flex items-center gap-2 p-3 rounded-xl border border-stone-200 hover:bg-stone-50 cursor-pointer">
                    <input type="checkbox" name="air_purifying" value="1" {{ old('air_purifying', $product->plantAttributes?->air_purifying) ? 'checked' : '' }} class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500">
                    <div>
                        <span class="text-xs font-bold text-stone-800">NASA Air Purifying Specimen</span>
                        <p class="text-[11px] text-stone-500">Filters toxins and improves oxygenation</p>
                    </div>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-stone-100">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Watering Care Instructions</label>
                    <textarea name="care_instructions_watering" rows="2" class="w-full px-3 py-2 rounded-xl border border-stone-200 text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none">{{ old('care_instructions_watering', $care['watering'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Sunlight Instructions</label>
                    <textarea name="care_instructions_light" rows="2" class="w-full px-3 py-2 rounded-xl border border-stone-200 text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none">{{ old('care_instructions_light', $care['light'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Soil Recommendation</label>
                    <textarea name="care_instructions_soil" rows="2" class="w-full px-3 py-2 rounded-xl border border-stone-200 text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none">{{ old('care_instructions_soil', $care['soil'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Botanist Pro-Tip</label>
                    <textarea name="care_instructions_pro_tip" rows="2" class="w-full px-3 py-2 rounded-xl border border-stone-200 text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none">{{ old('care_instructions_pro_tip', $care['pro_tip'] ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 3. Variants & Inventory Stock Management -->
        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center text-xs font-bold">3</span>
                    Inventory Variants & SKUs
                </h3>
                <button type="button" @click="addVariant()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-botanical-50 text-botanical-800 hover:bg-botanical-100 text-xs font-semibold transition">
                    + Add Another Variant
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(variant, index) in variants" :key="index">
                    <div class="p-4 rounded-xl border border-stone-200 bg-stone-50/50 space-y-3 relative">
                        <input type="hidden" :name="'variants[' + index + '][id]'" x-model="variant.id">

                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-stone-700 uppercase" x-text="'Variant #' + (index + 1) + (variant.id ? ' (ID: ' + variant.id + ')' : ' (New)')"></span>
                            <button type="button" @click="removeVariant(index)" x-show="variants.length > 1" class="text-xs text-red-600 hover:text-red-800 font-semibold">
                                Remove Variant
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-600 mb-1">Variant Title *</label>
                                <input type="text" :name="'variants[' + index + '][title]'" x-model="variant.title" required
                                       class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-600 mb-1">Unique SKU *</label>
                                <input type="text" :name="'variants[' + index + '][sku]'" x-model="variant.sku" required
                                       class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-white text-xs font-mono focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-600 mb-1">Variant Price (₹) *</label>
                                <input type="number" step="0.01" min="0" :name="'variants[' + index + '][price]'" x-model="variant.price" required
                                       class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-600 mb-1">Compare-at Price (₹)</label>
                                <input type="number" step="0.01" min="0" :name="'variants[' + index + '][compare_at_price]'" x-model="variant.compare_at_price"
                                       class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-600 mb-1">Stock Quantity *</label>
                                <input type="number" min="0" :name="'variants[' + index + '][stock_quantity]'" x-model="variant.stock_quantity" required
                                       class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-white text-xs font-bold focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-600 mb-1">Low Stock Threshold</label>
                                <input type="number" min="0" :name="'variants[' + index + '][low_stock_threshold]'" x-model="variant.low_stock_threshold"
                                       class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-600 mb-1">Weight (Grams)</label>
                                <input type="number" min="0" :name="'variants[' + index + '][weight_grams]'" x-model="variant.weight_grams"
                                       class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-100 text-sm font-semibold transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-botanical-700 hover:bg-botanical-800 text-white text-sm font-semibold shadow-md transition">
                Update Specimen & Variants
            </button>
        </div>
    </form>
</div>
@endsection
