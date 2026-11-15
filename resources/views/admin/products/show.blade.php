@extends('admin.layouts.app')
@section('title', $product->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.products.index') }}" class="hover:underline">Products</a>
                <span>/</span>
                <span>{{ $product->category?->name }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $product->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center gap-1.5 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium px-4 py-2 rounded-xl transition">
                View in Store ↗
            </a>
        </div>
    </div>

    <!-- Product Summary Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Images & Gallery -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 space-y-4 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm border-b border-gray-100 dark:border-gray-800 pb-3">Imagery</h3>
            <div class="rounded-xl overflow-hidden border border-gray-100 dark:border-gray-800 aspect-square">
                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            </div>
            @if($product->productImages->isNotEmpty())
            <div class="grid grid-cols-4 gap-2 pt-2">
                @foreach($product->productImages as $gImg)
                <div class="rounded-lg overflow-hidden border border-gray-100 dark:border-gray-800 aspect-square">
                    <img src="{{ $gImg->image_url }}" alt="Gallery" class="w-full h-full object-cover">
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Details & Stats -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">Product Details</h3>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl">
                        <span class="text-xs text-gray-500 block">Selling Price</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">${{ number_format($product->price, 2) }}</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl">
                        <span class="text-xs text-gray-500 block">Current Stock</span>
                        <span class="text-lg font-bold {{ $product->stock <= 0 ? 'text-red-500' : ($product->stock <= 10 ? 'text-amber-500' : 'text-gray-900 dark:text-white') }}">{{ $product->stock }} units</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl">
                        <span class="text-xs text-gray-500 block">Status</span>
                        <span class="text-sm font-semibold {{ $product->is_active ? 'text-green-600' : 'text-gray-400' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl">
                        <span class="text-xs text-gray-500 block">SKU</span>
                        <span class="text-sm font-mono text-gray-700 dark:text-gray-300">{{ $product->sku ?? '—' }}</span>
                    </div>
                </div>

                @if($product->short_description)
                <div class="mb-4">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Short Description</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $product->short_description }}</p>
                </div>
                @endif

                @if($product->description)
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Description</h4>
                    <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                        {!! $product->description !!}
                    </div>
                </div>
                @endif
            </div>

            <!-- Customer Reviews -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                    Customer Reviews ({{ $product->reviews->count() }})
                </h3>

                @forelse($product->reviews as $rev)
                <div class="border-b border-gray-100 dark:border-gray-800 py-3 last:border-0">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-sm text-gray-900 dark:text-white">{{ $rev->user?->name ?? 'Guest' }}</span>
                            <span class="text-xs text-amber-500 font-bold">★ {{ $rev->rating }}/5</span>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $rev->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($rev->status) }}</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ $rev->body }}</p>
                </div>
                @empty
                <p class="text-xs text-gray-500">No reviews yet for this product.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

