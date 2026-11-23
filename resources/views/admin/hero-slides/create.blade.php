@extends('admin.layouts.app')
@section('title', 'Add Hero Carousel Slide')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Create Hero Carousel Slide</h1>
            <p class="text-sm text-gray-500 mt-1">Add a new slide to the landing page hero carousel banner.</p>
        </div>
        <a href="{{ route('admin.hero-slides.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium">
            &larr; Back to Carousel
        </a>
    </div>

    @if($errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl p-4 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.hero-slides.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 space-y-6 shadow-sm">
        @csrf

        {{-- Slide Badge & Title --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1.5 md:col-span-2">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Top Tag / Badge Text</label>
                <input type="text" name="badge_text" value="{{ old('badge_text', '✦ Radiate Confidence Every Day') }}" placeholder="e.g. ✦ Radiate Confidence Every Day" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                <p class="text-[11px] text-gray-400">Small pill badge appearing right above the headline.</p>
            </div>

            <div class="space-y-1.5 md:col-span-2">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Headline / Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Beauty & Wellness for a Better You" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
            </div>

            <div class="space-y-1.5 md:col-span-2">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Subheading / Description</label>
                <textarea name="subtitle" rows="3" placeholder="Discover premium health & beauty products for healthy skin, hair & body." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">{{ old('subtitle') }}</textarea>
            </div>
        </div>

        {{-- CTA Buttons --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100 dark:border-gray-800">
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span> Primary Action Button
                </h3>
                <div class="space-y-1.5">
                    <label class="block text-xs text-gray-500">Button Label</label>
                    <input type="text" name="primary_button_text" value="{{ old('primary_button_text', 'Shop Now') }}" placeholder="Shop Now" class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs text-gray-500">Button URL / Link</label>
                    <input type="text" name="primary_button_url" value="{{ old('primary_button_url', '/shop') }}" placeholder="/shop" class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span> Secondary Action Button
                </h3>
                <div class="space-y-1.5">
                    <label class="block text-xs text-gray-500">Button Label</label>
                    <input type="text" name="secondary_button_text" value="{{ old('secondary_button_text', 'Explore Categories') }}" placeholder="Explore Categories" class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs text-gray-500">Button URL / Link</label>
                    <input type="text" name="secondary_button_url" value="{{ old('secondary_button_url', '#categories') }}" placeholder="#categories or /shop" class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
            </div>
        </div>

        {{-- Image & Floating Badge --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100 dark:border-gray-800">
            <div class="space-y-3">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Slide Model Image</label>
                <div class="space-y-2">
                    <input type="file" name="image_file" accept=".jpeg,.jpg,.png,.webp,.svg,.gif,.avif,image/jpeg,image/png,image/webp,image/svg+xml,image/gif,image/avif" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 dark:file:bg-pink-950 dark:file:text-pink-300">
                    <p class="text-[11px] text-gray-400">Supported: JPEG, JPG, PNG, WEBP, AVIF, SVG, GIF (Max 5MB)</p>
                    <p class="text-[11px] text-gray-400">Or enter an image URL below:</p>
                    <input type="text" name="image_url" value="{{ old('image_url') }}" placeholder="https://..." class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-xs">
                </div>
            </div>

            <div class="space-y-3">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Circular Floating Badge</label>
                <input type="text" name="floating_badge_text" value="{{ old('floating_badge_text', '100% Original Products') }}" placeholder="e.g. 100% Original Products" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                <p class="text-[11px] text-gray-400">Badge that floats on the right model image (e.g. 100% Original Products).</p>
            </div>
        </div>

        {{-- Order & Status --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-gray-100 dark:border-gray-800">
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Display Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
            </div>

            <div class="flex items-center gap-3 pt-6">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="w-5 h-5 text-pink-600 rounded border-gray-300 focus:ring-pink-500">
                <label for="is_active" class="text-sm font-medium text-gray-800 dark:text-gray-200">Set active in carousel</label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ route('admin.hero-slides.index') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-gray-800">Cancel</a>
            <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm shadow-sm transition">
                Create Slide
            </button>
        </div>
    </form>
</div>
@endsection
