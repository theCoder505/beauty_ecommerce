@extends('admin.layouts.app')
@section('title', 'Edit Product')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit Product</h1>
            <p class="text-sm text-gray-500 mt-1">Modify details for {{ $product->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.show', $product) }}" class="text-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 px-3 py-2 rounded-xl text-gray-700 dark:text-gray-300 font-medium transition">
                View Details
            </a>
            <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
                &larr; Back to Products
            </a>
        </div>
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

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- General Info Card -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Basic Information</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Title <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SKU</label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>
            </div>

            <div>
                <x-rich-editor
                    name="description"
                    id="product-description-editor"
                    :value="old('description', $product->description)"
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
                    <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $product->price) }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sale / Discount Price ($)</label>
                    <input type="number" step="0.01" name="sale_price" id="sale_price" value="{{ old('sale_price', $product->sale_price) }}" placeholder="Optional discount" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label for="cost_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cost / Buying Price ($)</label>
                    <input type="number" step="0.01" name="cost_price" id="cost_price" value="{{ old('cost_price', $product->cost_price ?? 0) }}" placeholder="Optional buying price" title="Used for Profit & Loss calculations" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <span class="text-[11px] text-gray-400">Used for P&L profit calculation</span>
                </div>

                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stock Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}" min="0" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>
            </div>

            <div class="flex flex-wrap gap-6 pt-2">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Published (Visible in store)</span>
                </label>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Featured on Homepage</span>
                </label>
            </div>
        </div>

        <!-- Product Options (Sizes, Colors, Shades) -->
        @include('admin.products.partials.options-form', ['product' => $product])

        <!-- Imagery -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Product Images</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Primary Thumbnail</label>
                <div class="flex items-center gap-4 mb-3">
                    <img id="product-thumb-preview" src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="w-20 h-20 rounded-xl object-cover border border-gray-200 dark:border-gray-700 preview-target">
                    <span class="text-xs text-gray-500">Current thumbnail. Choose a new file to replace it.</span>
                </div>
                <input type="file" name="thumbnail" accept="image/*" data-preview="#product-thumb-preview" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300 cursor-pointer">
            </div>

            <!-- Existing Gallery Images -->
            <div id="existing-gallery-section" class="{{ $product->productImages->isEmpty() ? 'hidden' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Existing Gallery Photos</label>
                    <span id="gallery-count-badge" class="text-xs text-gray-500 font-medium">{{ $product->productImages->count() }} photo{{ $product->productImages->count() === 1 ? '' : 's' }}</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3" id="existing-gallery-grid">
                    @foreach($product->productImages as $galleryImg)
                    <div id="gallery-item-{{ $galleryImg->id }}" class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 aspect-square bg-gray-50 dark:bg-gray-800 transition-all duration-200">
                        <img src="{{ $galleryImg->image_url }}" alt="Gallery image" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center p-2">
                            <button type="button"
                                    onclick="deleteGalleryPhoto('{{ route('admin.products.delete-image', ['product' => $product, 'image' => $galleryImg]) }}', {{ $galleryImg->id }})"
                                    class="bg-red-600 hover:bg-red-700 text-white rounded-lg p-2 text-xs transition shadow-md hover:scale-110 active:scale-95"
                                    title="Delete gallery photo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Add More Gallery Photos</label>
                <input type="file" name="gallery[]" accept="image/*" multiple data-preview-container="#gallery-preview-container" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300 cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">Select one or multiple images to append to the product gallery.</p>
                <div id="gallery-preview-container" class="image-preview-container flex flex-wrap gap-3 mt-3"></div>
            </div>
        </div>

        <!-- SEO -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-4 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">SEO Optimization</h3>
            
            <div>
                <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title</label>
                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $product->meta_title) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
            </div>

            <div>
                <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                <textarea name="meta_description" id="meta_description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">{{ old('meta_description', $product->meta_description) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold shadow-sm transition">Save Changes</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
window.deleteGalleryPhoto = function(url, imageId) {
    const isDark = document.documentElement.classList.contains('dark');
    Swal.fire({
        title: 'Delete this gallery photo?',
        text: 'This image will be permanently removed from the product gallery.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete photo',
        cancelButtonText: 'Cancel',
        background: isDark ? '#111827' : '#ffffff',
        color: isDark ? '#f3f4f6' : '#111827',
        customClass: {
            popup: 'rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl',
            confirmButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm',
            cancelButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm'
        }
    }).then(async (result) => {
        if (!result.isConfirmed) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const itemEl = document.getElementById('gallery-item-' + imageId);
        if (itemEl) {
            itemEl.style.opacity = '0.4';
            itemEl.style.pointerEvents = 'none';
        }

        try {
            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok && data.success !== false) {
                if (itemEl) {
                    itemEl.classList.add('scale-75', 'opacity-0');
                    setTimeout(() => {
                        itemEl.remove();
                        const grid = document.getElementById('existing-gallery-grid');
                        const countBadge = document.getElementById('gallery-count-badge');
                        if (grid) {
                            const count = grid.children.length;
                            if (countBadge) {
                                countBadge.textContent = count + ' photo' + (count === 1 ? '' : 's');
                            }
                            if (count === 0) {
                                const section = document.getElementById('existing-gallery-section');
                                if (section) section.classList.add('hidden');
                            }
                        }
                    }, 200);
                }
                if (window.toast) {
                    toast(data.message || 'Gallery photo deleted successfully.', 'success');
                }
            } else {
                if (itemEl) {
                    itemEl.style.opacity = '1';
                    itemEl.style.pointerEvents = 'auto';
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to delete gallery image. Please try again.',
                    confirmButtonColor: '#dc2626',
                    background: isDark ? '#111827' : '#ffffff',
                    color: isDark ? '#f3f4f6' : '#111827'
                });
            }
        } catch (err) {
            console.error('Gallery image delete error:', err);
            if (itemEl) {
                itemEl.style.opacity = '1';
                itemEl.style.pointerEvents = 'auto';
            }
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Something went wrong while deleting the gallery image.',
                confirmButtonColor: '#dc2626',
                background: isDark ? '#111827' : '#ffffff',
                color: isDark ? '#f3f4f6' : '#111827'
            });
        }
    });
};
</script>
@endpush
