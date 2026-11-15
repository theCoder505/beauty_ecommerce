@extends('users.layouts.app')
@php
    $settings = app(\App\Services\SettingService::class)->all();
    $currency = $settings['currency_symbol'] ?? '৳';
    $productUrl = route('products.show', $product->slug);
@endphp
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
        <a href="{{ route('home') }}" class="hover:text-brand-700 transition">Home</a>
        <span>/</span>
        <a href="{{ route('shop.index') }}" class="hover:text-brand-700 transition">Shop</a>
        <span>/</span>
        <a href="{{ route('shop.category', $product->category->slug) }}" class="hover:text-brand-700 transition">{{ $product->category->name }}</a>
        <span>/</span>
        <span class="text-gray-900 dark:text-white font-medium truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        {{-- Gallery --}}
        <div x-data="{ active: '{{ $product->thumbnail_url }}' }">
            <div class="aspect-square rounded-2xl overflow-hidden bg-cream-100 dark:bg-gray-800 shadow-lg mb-4">
                <img :src="active" alt="{{ $product->name }}" class="w-full h-full object-cover">
            </div>
            @if($product->images->isNotEmpty())
            <div class="flex gap-3 overflow-x-auto pb-2">
                <button @click="active='{{ $product->thumbnail_url }}'" class="shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 border-transparent hover:border-brand-700 transition">
                    <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover">
                </button>
                @foreach($product->images as $img)
                <button @click="active='{{ $img->image_url }}'" class="shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 border-transparent hover:border-brand-700 transition">
                    <img src="{{ $img->image_url }}" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Product Info --}}
        <div class="space-y-6">
            <div>
                <span class="text-xs font-semibold uppercase tracking-widest text-brand-700 dark:text-brand-400">{{ $product->category->name }}</span>
                <h1 class="font-serif text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mt-2 leading-tight">{{ $product->name }}</h1>
                <p class="text-sm text-gray-400 mt-1">SKU: {{ $product->sku }}</p>
            </div>

            {{-- Rating --}}
            @php $avg = $product->average_rating; $count = $product->approvedReviews->count(); @endphp
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1">
                    @for($i = 1; $i <= 5; $i++)<svg class="w-4 h-4 {{ $i <= round($avg) ? 'text-amber-400' : 'text-gray-200 dark:text-gray-600' }} fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                </div>
                <span class="text-sm text-gray-500">{{ $avg }} ({{ $count }} reviews)</span>
                <span class="text-gray-300">|</span>
                <span class="text-sm {{ $product->stock > 0 ? 'text-green-600' : 'text-red-500' }} font-semibold">
                    {{ $product->stock > 0 ? 'In Stock (' . $product->stock . ')' : 'Out of Stock' }}
                </span>
            </div>

            {{-- Price --}}
            <div class="flex items-baseline gap-3">
                <span class="font-serif text-4xl font-bold text-brand-700 dark:text-brand-400">{{ $currency }}{{ number_format($product->current_price) }}</span>
                @if($product->is_on_sale)
                <span class="text-xl text-gray-400 line-through">{{ $currency }}{{ number_format($product->price) }}</span>
                <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-lg">Save {{ $product->discount_percent }}%</span>
                @endif
            </div>

            {{-- Add to Cart --}}
            @if($product->stock > 0)
            <form action="{{ route('cart.add') }}" method="POST" class="flex gap-3 items-center" x-data="{ qty: 1 }">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="flex items-center border border-cream-200 dark:border-gray-600 rounded-xl overflow-hidden">
                    <button type="button" @click="qty = Math.max(1, qty - 1)" class="px-4 py-3 text-gray-600 dark:text-gray-300 hover:bg-cream-100 dark:hover:bg-gray-700 transition text-lg">−</button>
                    <input type="number" name="quantity" :value="qty" min="1" max="{{ $product->stock }}"
                        class="w-14 text-center py-3 border-0 bg-transparent text-gray-900 dark:text-white font-semibold focus:outline-none">
                    <button type="button" @click="qty = Math.min({{ $product->stock }}, qty + 1)" class="px-4 py-3 text-gray-600 dark:text-gray-300 hover:bg-cream-100 dark:hover:bg-gray-700 transition text-lg">+</button>
                </div>
                @auth
                <button type="submit" class="flex-1 bg-brand-700 text-white font-bold py-3.5 rounded-xl hover:bg-brand-800 transition shadow-lg shadow-brand-700/25">
                    Add to Cart
                </button>
                @else
                <a href="{{ route('login') }}" class="flex-1 bg-brand-700 text-white font-bold py-3.5 rounded-xl hover:bg-brand-800 transition text-center shadow-lg shadow-brand-700/25">Add to Cart</a>
                @endauth
            </form>
            @else
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-5 py-4 text-red-600 dark:text-red-400 font-semibold text-sm">
                This product is currently out of stock.
            </div>
            @endif

            {{-- Social Share --}}
            <div class="border-t border-cream-200 dark:border-gray-700 pt-5">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Share this product:</p>
                <div class="flex gap-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($productUrl) }}" target="_blank" class="bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700 transition" aria-label="Share on Facebook">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($productUrl) }}&text={{ urlencode($product->name) }}" target="_blank" class="bg-sky-500 text-white p-2 rounded-lg hover:bg-sky-600 transition" aria-label="Share on Twitter">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/></svg>
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($product->name . ' ' . $productUrl) }}" target="_blank" class="bg-green-500 text-white p-2 rounded-lg hover:bg-green-600 transition" aria-label="Share on WhatsApp">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                    <button onclick="navigator.clipboard.writeText('{{ $productUrl }}').then(() => alert('Link copied!'))"
                        class="bg-gray-600 text-white p-2 rounded-lg hover:bg-gray-700 transition" aria-label="Copy link">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Description --}}
    <div class="mt-16 grid grid-cols-1 lg:grid-cols-3 gap-10">
        <div class="lg:col-span-2">
            <h2 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-5">Product Description</h2>
            <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                {!! $product->description !!}
            </div>
        </div>
    </div>

    {{-- Reviews --}}
    <div class="mt-16">
        <h2 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-8">Customer Reviews ({{ $count }})</h2>

        {{-- Write Review Form --}}
        @if($canReview)
        <div class="bg-cream-50 dark:bg-gray-800 rounded-2xl p-6 border border-cream-200 dark:border-gray-700 mb-8">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Write a Review</h3>
            <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="order_id" value="{{ $reviewableOrderId }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rating *</label>
                    <div class="flex items-center gap-1 star-rating flex-row-reverse justify-end">
                        @for($i = 5; $i >= 1; $i--)
                        <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" required>
                        <label for="star{{ $i }}" title="{{ $i }} stars">★</label>
                        @endfor
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Review (optional)</label>
                    <textarea name="body" rows="4" placeholder="Share your experience…"
                        class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Photos (optional)</label>
                    <input type="file" name="images[]" multiple accept="image/*" class="text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-700 file:text-white hover:file:bg-brand-800">
                </div>
                <button type="submit" class="bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-brand-800 transition">Submit Review</button>
            </form>
        </div>
        @endif

        {{-- Reviews list --}}
        @if($product->approvedReviews->isEmpty())
        <p class="text-gray-500 text-sm">No reviews yet. Be the first to review this product!</p>
        @else
        <div class="space-y-5">
            @foreach($product->approvedReviews as $review)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ $review->user->avatar_url }}" class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ $review->user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $review->created_at->format('M j, Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-0.5">
                        @for($i = 1; $i <= 5; $i++)<svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200 dark:text-gray-600' }} fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                    </div>
                </div>
                @if($review->body)
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-3 leading-relaxed">{{ $review->body }}</p>
                @endif
                @if($review->images->isNotEmpty())
                <div class="flex gap-2 mt-3">
                    @foreach($review->images as $img)
                    <img src="{{ $img->image_url }}" class="w-16 h-16 rounded-lg object-cover cursor-pointer">
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Related Products --}}
    @if($relatedProducts->isNotEmpty())
    <div class="mt-16">
        <h2 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-8">You may also like</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            @foreach($relatedProducts as $rp)
            @include('users.partials.product-card', ['product' => $rp, 'currency' => $currency])
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

