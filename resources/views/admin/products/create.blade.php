@extends('admin.layouts.app')
@section('title', 'Add New Product')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Add New Product</h1>
            <p class="text-sm text-gray-500 mt-1">Create a new beauty/cosmetics item in the catalog</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            &larr; Back to Products
        </a>
    </div>

    @if(isset($errors) && $errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl p-4 text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- General Info Card -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Basic Information</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Title <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. Amber Dusk Luxury Parfum" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SKU (Stock Keeping Unit)</label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku') }}" placeholder="e.g. TRF-AMB-01" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>
            </div>

            <div>
                <x-rich-editor
                    name="description"
                    id="product-description-editor"
                    :value="old('description')"
                    label="Editorial Description"
                    placeholder="Describe scent notes, ingredients, mood, and application details..."
                    height="320px"
                />
            </div>
        </div>

        <!-- Pricing & Inventory -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Pricing & Stock</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Regular Price ($) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="price" id="price" value="{{ old('price') }}" required placeholder="89.00" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sale / Discount Price ($)</label>
                    <input type="number" step="0.01" name="sale_price" id="sale_price" value="{{ old('sale_price') }}" placeholder="75.00" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label for="cost_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cost / Buying Price ($)</label>
                    <input type="number" step="0.01" name="cost_price" id="cost_price" value="{{ old('cost_price', 0) }}" placeholder="45.00" title="Used for Profit & Loss calculations" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <span class="text-[11px] text-gray-400">Used for P&L profit calculation</span>
                </div>

                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stock Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', 25) }}" min="0" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>
            </div>

            <div class="flex flex-wrap gap-6 pt-2">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Published (Visible in store)</span>
                </label>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Featured on Homepage</span>
                </label>
            </div>
        </div>

        <!-- Product Options (Sizes, Colors, Shades) -->
        @include('admin.products.partials.options-form')

        <!-- Imagery -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Product Images</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Primary Thumbnail <span class="text-red-500">*</span></label>
                <div class="mb-3">
                    <img id="product-thumb-preview" class="hidden w-24 h-24 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shadow-sm preview-target mb-2" alt="Thumbnail Preview">
                </div>
                <input type="file" name="thumbnail" accept=".jpeg,.jpg,.png,.webp,.gif,.avif,image/jpeg,image/png,image/webp,image/gif,image/avif" required data-preview="#product-thumb-preview" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300 cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">Hero image shown in listings and cards. Supported: JPEG, JPG, PNG, WEBP, AVIF, GIF (Max 2MB).</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gallery Additional Photos</label>
                <input type="file" name="gallery[]" accept=".jpeg,.jpg,.png,.webp,.gif,.avif,image/jpeg,image/png,image/webp,image/gif,image/avif" multiple data-preview-container="#gallery-preview-container" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300 cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">Select multiple images for the product carousel. Supported: JPEG, JPG, PNG, WEBP, AVIF, GIF (Max 2MB).</p>
                <div id="gallery-preview-container" class="image-preview-container flex flex-wrap gap-3 mt-3"></div>
            </div>
        </div>

        <!-- SEO -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-4 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">SEO Optimization</h3>
            
            <div>
                <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title</label>
                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" placeholder="Custom meta title (defaults to product title)" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
            </div>

            <div>
                <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                <textarea name="meta_description" id="meta_description" rows="2" placeholder="Custom meta description for search engines..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">{{ old('meta_description') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold shadow-sm transition">Publish Product</button>
        </div>
    </form>
</div>
@endsection
