@extends('users.layouts.app')
@php
    $settings = app(\App\Services\SettingService::class)->all();
    $currency = $settings['currency_symbol'] ?? '৳';
    $productUrl = route('products.show', $product->slug);
    $canReview = $canReview ?? false;
    $relatedProducts = $relatedProducts ?? collect();
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
        {{-- Gallery with Right-Side Zoom Preview (Amazon/E-commerce style) --}}
        <div x-data="productGallery('{{ $product->thumbnail_url }}')" class="relative">
            {{-- Main Image with Zoom Lens --}}
            <div class="aspect-square rounded-2xl overflow-hidden bg-cream-100 dark:bg-gray-800 shadow-lg mb-4 relative cursor-crosshair select-none border border-cream-200 dark:border-gray-700"
                 @mouseenter="zoomed = true"
                 @mouseleave="zoomed = false"
                 @mousemove="onMouseMove($event)">
                <img :src="active"
                     alt="{{ $product->name }}"
                     class="w-full h-full object-cover pointer-events-none select-none">

                {{-- Movable Zoom Lens over image on desktop --}}
                <div x-show="zoomed"
                     x-cloak
                     class="hidden lg:block absolute w-[140px] h-[140px] bg-brand-500/20 border-2 border-brand-500/70 rounded-xl pointer-events-none shadow-md backdrop-blur-[1px]"
                     :style="'left: ' + lensX + 'px; top: ' + lensY + 'px;' + (zoomed ? '' : ' display: none;')">
                </div>

                {{-- Hover zoom badge --}}
                <div x-show="!zoomed" x-transition.opacity
                     class="absolute bottom-3 right-3 bg-black/60 backdrop-blur-md text-white text-xs px-3 py-1.5 rounded-full flex items-center gap-1.5 pointer-events-none shadow-md">
                    <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                    </svg>
                    <span class="font-medium">Hover to zoom</span>
                </div>
            </div>

            {{-- Right-Side Zoom Preview Window (shows magnified view on the right) --}}
            <div x-show="zoomed"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="hidden lg:block absolute left-[calc(100%+3rem)] top-0 w-full aspect-square z-40 rounded-2xl shadow-2xl border-2 border-cream-300 dark:border-gray-700 bg-white dark:bg-gray-900 pointer-events-none overflow-hidden">
                <div class="w-full h-full"
                     :style="'background-image: url(' + active + '); background-repeat: no-repeat; background-size: 300% 300%; background-position: ' + bgX + '% ' + bgY + '%;'">
                </div>
                <div class="absolute top-3 left-3 bg-black/70 backdrop-blur-md text-white text-[11px] font-semibold px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1.5 shadow">
                    <span class="text-brand-400">✦</span> 3x Zoom View
                </div>
            </div>

            @if($product->images->isNotEmpty())
            <div class="flex gap-3 overflow-x-auto pb-2 custom-scrollbar">
                <button type="button"
                        @click="selectImage('{{ $product->thumbnail_url }}')"
                        :class="active === '{{ $product->thumbnail_url }}' ? 'border-brand-700 ring-2 ring-brand-700/30' : 'border-transparent hover:border-brand-700'"
                        class="shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 transition">
                    <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover">
                </button>
                @foreach($product->images as $img)
                <button type="button"
                        @click="selectImage('{{ $img->image_url }}')"
                        :class="active === '{{ $img->image_url }}' ? 'border-brand-700 ring-2 ring-brand-700/30' : 'border-transparent hover:border-brand-700'"
                        class="shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 transition">
                    <img src="{{ $img->image_url }}" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Product Info --}}
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-widest text-brand-700 dark:text-brand-400">{{ $product->category->name }}</span>
                    <h1 class="font-serif text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mt-2 leading-tight">{{ $product->name }}</h1>
                    <p class="text-sm text-gray-400 mt-1">SKU: {{ $product->sku }}</p>
                </div>
                @php $isWl = auth()->check() ? app(\App\Services\WishlistService::class)->isWishlisted($product->id) : false; @endphp
                <button
                    type="button"
                    onclick="event.stopPropagation(); toggleWishlist({{ $product->id }}, this)"
                    class="p-3 rounded-2xl border border-cream-300 dark:border-gray-700 hover:border-red-300 dark:hover:border-red-900/40 bg-white dark:bg-gray-800 shadow-sm transition {{ $isWl ? 'text-red-500' : 'text-gray-400 hover:text-red-500' }}"
                    title="{{ $isWl ? 'Remove from wishlist' : 'Add to wishlist' }}"
                    aria-label="Wishlist">
                    <svg class="w-6 h-6 {{ $isWl ? 'fill-current' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>
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
                <span class="text-xl text-red-500 font-medium line-through">{{ $currency }}{{ number_format($product->price) }}</span>
                <span class="bg-red-100 text-red-600 text-sm font-bold px-3 py-1 rounded-lg">Save {{ $product->discount_percent }}%</span>
                @endif
            </div>

            {{-- Add to Cart & Buy Now (JS Ajax with + - quantity div and Swal alert) --}}
            @if($product->stock > 0)
            @php
                $productSizes  = $product->sizes;
                $productColors = $product->colors;
                $productShades = $product->shades;
                $hasSizes  = $productSizes->isNotEmpty();
                $hasColors = $productColors->isNotEmpty();
                $hasShades = $productShades->isNotEmpty();
            @endphp
            <div x-data="productCart({
                productId: {{ $product->id }},
                maxStock: {{ (int) $product->stock }},
                productName: @js($product->name),
                addUrl: @js(route('cart.add')),
                cartUrl: @js(route('cart.index')),
                checkoutUrl: @js(route('checkout.index')),
                csrfToken: @js(csrf_token()),
                hasSizes: @js($hasSizes),
                hasColors: @js($hasColors),
                hasShades: @js($hasShades)
            })" class="space-y-5">

                {{-- VARIANT SELECTORS --}}
                @if($hasSizes || $hasColors || $hasShades)
                <div class="space-y-4 pt-1 border-t border-cream-200 dark:border-gray-800">
                    {{-- SIZES SELECTOR --}}
                    @if($hasSizes)
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Size:
                                <span class="text-brand-700 dark:text-brand-400 normal-case font-semibold ml-1"
                                      x-text="selectedSize ? selectedSize : 'Select a size'"></span>
                            </label>
                            <span class="text-[11px] text-red-500 font-medium">* Required</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($productSizes as $size)
                                @if($size->is_available)
                                    <button type="button"
                                            @click="selectedSize = '{{ $size->name }}'; sizeError = false"
                                            :class="selectedSize === '{{ $size->name }}' ? 'bg-brand-700 text-white border-brand-700 shadow-sm ring-2 ring-brand-500/30' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-cream-300 dark:border-gray-700 hover:border-brand-500'"
                                            class="px-3.5 py-2 rounded-xl text-xs font-semibold border transition active:scale-95">
                                        {{ $size->name }}
                                    </button>
                                @else
                                    <button type="button"
                                            disabled
                                            class="px-3.5 py-2 rounded-xl text-xs font-semibold border border-dashed border-gray-300 dark:border-gray-700 text-gray-400 dark:text-gray-500 bg-gray-100/70 dark:bg-gray-800/50 cursor-not-allowed line-through relative"
                                            title="Currently unavailable">
                                        {{ $size->name }} <span class="no-underline text-[10px] text-rose-500 ml-1 font-normal">(Unavailable)</span>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                        <p x-show="sizeError" x-cloak class="text-xs text-rose-600 font-semibold mt-1">Please select an available size to proceed.</p>
                    </div>
                    @endif

                    {{-- COLORS SELECTOR --}}
                    @if($hasColors)
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Color:
                                <span class="text-brand-700 dark:text-brand-400 normal-case font-semibold ml-1"
                                      x-text="selectedColor ? selectedColor : 'Select a color'"></span>
                            </label>
                            <span class="text-[11px] text-red-500 font-medium">* Required</span>
                        </div>
                        <div class="flex flex-wrap gap-2.5">
                            @foreach($productColors as $color)
                                @if($color->is_available)
                                    <button type="button"
                                            @click="selectedColor = '{{ $color->name }}'; colorError = false"
                                            :class="selectedColor === '{{ $color->name }}' ? 'ring-2 ring-brand-700 dark:ring-brand-400 border-brand-700 bg-brand-50/50 dark:bg-brand-950/40 shadow-xs scale-105' : 'border-cream-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-brand-500'"
                                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl border transition active:scale-95 text-xs font-medium text-gray-900 dark:text-gray-100">
                                        <span class="w-4 h-4 rounded-full border border-black/10 shrink-0 shadow-xs" style="background-color: {{ $color->code ?? '#e11d48' }};"></span>
                                        <span>{{ $color->name }}</span>
                                    </button>
                                @else
                                    <button type="button"
                                            disabled
                                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-100/70 dark:bg-gray-800/50 text-gray-400 dark:text-gray-500 cursor-not-allowed text-xs line-through"
                                            title="Currently unavailable">
                                        <span class="w-4 h-4 rounded-full border border-black/10 shrink-0 opacity-40" style="background-color: {{ $color->code ?? '#e11d48' }};"></span>
                                        <span>{{ $color->name }} <span class="no-underline text-[10px] text-rose-500 font-normal">(Unavailable)</span></span>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                        <p x-show="colorError" x-cloak class="text-xs text-rose-600 font-semibold mt-1">Please select an available color to proceed.</p>
                    </div>
                    @endif

                    {{-- SHADES SELECTOR --}}
                    @if($hasShades)
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Shade:
                                <span class="text-brand-700 dark:text-brand-400 normal-case font-semibold ml-1"
                                      x-text="selectedShade ? selectedShade : 'Select a shade'"></span>
                            </label>
                            <span class="text-[11px] text-red-500 font-medium">* Required</span>
                        </div>
                        <div class="flex flex-wrap gap-2.5">
                            @foreach($productShades as $shade)
                                @if($shade->is_available)
                                    <button type="button"
                                            @click="selectedShade = '{{ $shade->name }}'; shadeError = false"
                                            :class="selectedShade === '{{ $shade->name }}' ? 'ring-2 ring-brand-700 dark:ring-brand-400 border-brand-700 bg-brand-50/50 dark:bg-brand-950/40 shadow-xs scale-105' : 'border-cream-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-brand-500'"
                                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl border transition active:scale-95 text-xs font-medium text-gray-900 dark:text-gray-100">
                                        <span class="w-4 h-4 rounded-full border border-black/10 shrink-0 shadow-xs" style="background-color: {{ $shade->code ?? '#d4a373' }};"></span>
                                        <span>{{ $shade->name }}</span>
                                    </button>
                                @else
                                    <button type="button"
                                            disabled
                                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-100/70 dark:bg-gray-800/50 text-gray-400 dark:text-gray-500 cursor-not-allowed text-xs line-through"
                                            title="Currently unavailable">
                                        <span class="w-4 h-4 rounded-full border border-black/10 shrink-0 opacity-40" style="background-color: {{ $shade->code ?? '#d4a373' }};"></span>
                                        <span>{{ $shade->name }} <span class="no-underline text-[10px] text-rose-500 font-normal">(Unavailable)</span></span>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                        <p x-show="shadeError" x-cloak class="text-xs text-rose-600 font-semibold mt-1">Please select an available shade to proceed.</p>
                    </div>
                    @endif
                </div>
                @endif

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
                    {{-- Quantity Stepper Div with + - --}}
                    <div class="inline-flex items-center justify-between sm:justify-start bg-cream-100 dark:bg-gray-800 border border-cream-300 dark:border-gray-700 rounded-2xl p-1 shadow-sm select-none shrink-0">
                        <button type="button"
                                @click="decrease()"
                                :disabled="qty <= 1 || loading || buyLoading"
                                class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-gray-700 hover:shadow-sm active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed transition text-base font-bold"
                                aria-label="Decrease quantity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                        </button>

                        {{-- Number displayed inside a div --}}
                        <div class="w-12 text-center font-bold text-gray-900 dark:text-white text-base tracking-wide"
                             x-text="qty">
                            1
                        </div>

                        <button type="button"
                                @click="increase()"
                                :disabled="qty >= maxStock || loading || buyLoading"
                                class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-gray-700 hover:shadow-sm active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed transition text-base font-bold"
                                aria-label="Increase quantity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>

                    {{-- Add to Cart Button with JS action and loader --}}
                    <button type="button"
                            @click="addToCart()"
                            :disabled="loading || buyLoading || maxStock <= 0"
                            class="flex-1 min-w-[150px] bg-brand-700 hover:bg-brand-800 text-white font-bold py-3.5 px-5 rounded-2xl transition shadow-lg shadow-brand-700/25 flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed active:scale-[0.99]">
                        <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" x-cloak>
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!loading" class="font-bold text-xl leading-none">+</span>
                        <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span x-text="loading ? 'Adding…' : 'Cart'">Cart</span>
                    </button>

                    {{-- Buy Now Button with instant checkout redirect --}}
                    <button type="button"
                            @click="buyNow()"
                            :disabled="loading || buyLoading || maxStock <= 0"
                            class="flex-1 min-w-[150px] bg-warm-900 hover:bg-black dark:bg-amber-600 dark:hover:bg-amber-700 text-white font-bold py-3.5 px-5 rounded-2xl transition shadow-lg shadow-warm-900/20 dark:shadow-amber-600/20 flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed active:scale-[0.99]">
                        <svg x-show="buyLoading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" x-cloak>
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!buyLoading" class="w-5 h-5 text-amber-400 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span x-text="buyLoading ? 'Proceeding…' : 'Buy Now'">Buy Now</span>
                    </button>
                </div>
            </div>
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
                    <button onclick="navigator.clipboard.writeText('{{ $productUrl }}').then(() => (window.toast ? toast('Link copied!') : Swal.fire({ text: 'Link copied!', icon: 'success' })))"
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
            <div class="rich-content prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                {!! $product->description !!}
            </div>
        </div>
    </div>

    {{-- Reviews --}}
    <div class="mt-16" id="reviews" x-data="reviewImageModal()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="font-serif text-2xl font-bold text-gray-900 dark:text-white">Customer Reviews ({{ $count }})</h2>
                <p class="text-xs text-gray-500 mt-1">Authentic reviews submitted by verified buyers from their completed orders.</p>
            </div>
            @if($avg > 0)
            <div class="flex items-center gap-2 bg-amber-50 dark:bg-amber-950/30 px-3 py-1.5 rounded-xl border border-amber-200 dark:border-amber-800/40">
                <span class="text-amber-500 font-bold text-base">★ {{ $avg }}</span>
                <span class="text-xs text-gray-500">out of 5.0 ({{ $count }} {{ Str::plural('review', $count) }})</span>
            </div>
            @endif
        </div>

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
                <div class="flex flex-wrap gap-2.5 mt-3">
                    @foreach($review->images as $img)
                    <button type="button" 
                        @click="open({
                            id: {{ $review->id }},
                            userName: {{ json_encode($review->user->name ?? 'Verified Customer') }},
                            userAvatar: {{ json_encode($review->user->avatar_url) }},
                            rating: {{ (int)$review->rating }},
                            date: {{ json_encode($review->created_at->format('M j, Y')) }},
                            body: {{ json_encode($review->body ?? '') }},
                            images: {{ json_encode($review->images->map(fn($i) => ['id' => $i->id, 'url' => $i->image_url])->values()) }}
                        }, {{ $loop->index }})"
                        class="group relative rounded-xl overflow-hidden border border-cream-200 dark:border-gray-700 hover:ring-2 hover:ring-brand-500 transition-all focus:outline-none"
                        title="Click to view image and review">
                        <img src="{{ $img->image_url }}" alt="Customer review photo" class="w-16 h-16 rounded-xl object-cover group-hover:scale-105 transition-transform duration-200">
                        <span class="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                        </span>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Review Image Slider & Details Modal --}}
        <div x-cloak x-show="isOpen" 
             @keydown.escape.window="close()"
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/85 backdrop-blur-md"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            {{-- Click backdrop to close --}}
            <div class="absolute inset-0" @click="close()"></div>

            {{-- Modal Content Window --}}
            <div class="relative w-full max-w-4xl bg-white dark:bg-gray-900 rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh] border border-gray-200 dark:border-gray-800 z-10"
                 @click.stop>
                
                {{-- Top: Image Viewer Stage with Controls --}}
                <div class="relative bg-neutral-950 flex items-center justify-center overflow-hidden select-none min-h-[300px] sm:min-h-[440px] max-h-[58vh]">
                    
                    {{-- Floating Top-Left Controls: Zoom buttons & Reset --}}
                    <div class="absolute top-3 left-3 z-20 flex items-center gap-1.5 bg-black/60 backdrop-blur-md text-white px-2.5 py-1.5 rounded-xl border border-white/10 text-xs">
                        <button type="button" @click="zoomOut()" title="Zoom Out" class="p-1 rounded-lg hover:bg-white/20 transition disabled:opacity-30" :disabled="zoom <= minZoom">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <span class="font-mono text-xs px-1 text-gray-200" x-text="Math.round(zoom * 100) + '%'"></span>
                        <button type="button" @click="zoomIn()" title="Zoom In" class="p-1 rounded-lg hover:bg-white/20 transition disabled:opacity-30" :disabled="zoom >= maxZoom">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                        <button type="button" @click="resetZoom()" title="Reset Zoom" class="p-1 rounded-lg hover:bg-white/20 transition ml-1 text-gray-300 hover:text-white" :disabled="zoom === 1 && panX === 0 && panY === 0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                        <template x-if="activeReview?.images && activeReview.images.length > 1">
                            <span class="border-l border-white/20 pl-2 ml-1 text-xs text-gray-300 font-mono" x-text="(activeIndex + 1) + '/' + activeReview.images.length"></span>
                        </template>
                    </div>

                    {{-- Floating Top-Right: Close Button --}}
                    <div class="absolute top-3 right-3 z-20">
                        <button type="button" @click="close()" class="p-2 rounded-full bg-black/60 hover:bg-black/80 text-white border border-white/10 backdrop-blur-md transition" title="Close">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Image Stage with Zoom and Drag Pan --}}
                    <div class="w-full h-full flex items-center justify-center p-4 overflow-hidden"
                         @wheel.prevent="onWheel($event)"
                         @mousedown="startPan($event)"
                         @mousemove="doPan($event)"
                         @mouseup="endPan()"
                         @mouseleave="endPan()"
                         @dblclick="toggleZoom()"
                         :class="zoom > 1 ? (isDragging ? 'cursor-grabbing' : 'cursor-grab') : 'cursor-zoom-in'">
                        <template x-if="activeReview && activeReview.images && activeReview.images[activeIndex]">
                            <img :src="activeReview.images[activeIndex].url"
                                 :alt="'Review photo ' + (activeIndex + 1)"
                                 class="max-w-full max-h-[52vh] object-contain select-none pointer-events-auto rounded-lg shadow-lg"
                                 :style="'transform: scale(' + zoom + ') translate(' + (panX / zoom) + 'px, ' + (panY / zoom) + 'px); transition: ' + (isDragging ? 'none' : 'transform 0.15s ease-out') + ';'"
                                 draggable="false">
                        </template>
                    </div>

                    {{-- Slider Nav: Previous Button --}}
                    <template x-if="activeReview?.images && activeReview.images.length > 1">
                        <button type="button" 
                                @click="prev()" 
                                class="absolute left-3 top-1/2 -translate-y-1/2 z-20 p-2.5 rounded-full bg-black/60 hover:bg-black/80 text-white border border-white/10 backdrop-blur-md transition"
                                title="Previous Image">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                    </template>

                    {{-- Slider Nav: Next Button --}}
                    <template x-if="activeReview?.images && activeReview.images.length > 1">
                        <button type="button" 
                                @click="next()" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 z-20 p-2.5 rounded-full bg-black/60 hover:bg-black/80 text-white border border-white/10 backdrop-blur-md transition"
                                title="Next Image">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </template>
                </div>

                {{-- Bottom: Review Info Stage --}}
                <div class="p-5 sm:p-6 bg-white dark:bg-gray-800 border-t border-cream-200 dark:border-gray-700 overflow-y-auto">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3">
                            <img :src="activeReview?.userAvatar" class="w-11 h-11 rounded-full object-cover border border-cream-200 dark:border-gray-700 shadow-sm">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-sm sm:text-base text-gray-900 dark:text-white" x-text="activeReview?.userName"></p>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/40">
                                        <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        Verified Buyer
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5" x-text="activeReview?.date"></p>
                            </div>
                        </div>

                        {{-- Rating Stars --}}
                        <div class="flex items-center gap-0.5">
                            <template x-for="star in 5" :key="star">
                                <svg class="w-4 h-4 fill-current" :class="star <= (activeReview?.rating || 0) ? 'text-amber-400' : 'text-gray-200 dark:text-gray-600'" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </template>
                        </div>
                    </div>

                    {{-- Review Text --}}
                    <div class="mt-3.5">
                        <p x-show="activeReview?.body" x-text="activeReview?.body" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed break-words"></p>
                        <p x-show="!activeReview?.body" class="text-xs italic text-gray-400">No additional comment provided with this rating.</p>
                    </div>

                    {{-- Thumbnail Strip if Multiple Images --}}
                    <template x-if="activeReview?.images && activeReview.images.length > 1">
                        <div class="mt-4 pt-3.5 border-t border-cream-200 dark:border-gray-700 flex items-center gap-2 overflow-x-auto pb-1">
                            <template x-for="(imgItem, imgIdx) in activeReview.images" :key="imgIdx">
                                <button type="button" 
                                        @click="selectImage(imgIdx)"
                                        class="relative rounded-xl overflow-hidden border-2 transition-all flex-shrink-0 w-14 h-14"
                                        :class="activeIndex === imgIdx ? 'border-brand-600 ring-2 ring-brand-500/30' : 'border-transparent opacity-60 hover:opacity-100'">
                                    <img :src="imgItem.url" class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
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

@push('scripts')
<script>
window.productGallery = function(initialImg) {
    return {
        active: initialImg,
        zoomed: false,
        lensX: 0,
        lensY: 0,
        bgX: 0,
        bgY: 0,
        selectImage(imgUrl) {
            this.active = imgUrl;
            this.zoomed = false;
        },
        onMouseMove(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            const lensW = 140;
            const lensH = 140;

            const mx = e.clientX - rect.left;
            const my = e.clientY - rect.top;

            let lx = mx - lensW / 2;
            let ly = my - lensH / 2;

            lx = Math.max(0, Math.min(rect.width - lensW, lx));
            ly = Math.max(0, Math.min(rect.height - lensH, ly));

            this.lensX = lx;
            this.lensY = ly;

            const maxX = rect.width - lensW;
            const maxY = rect.height - lensH;

            this.bgX = maxX > 0 ? (lx / maxX) * 100 : 0;
            this.bgY = maxY > 0 ? (ly / maxY) * 100 : 0;
        }
    };
};

window.productCart = function(config) {
    return {
        qty: 1,
        maxStock: config.maxStock,
        loading: false,
        buyLoading: false,
        selectedSize: null,
        selectedColor: null,
        selectedShade: null,
        sizeError: false,
        colorError: false,
        shadeError: false,
        decrease() {
            if (this.qty > 1) {
                this.qty--;
            }
        },
        increase() {
            if (this.qty < this.maxStock) {
                this.qty++;
            }
        },
        validateSelections() {
            let valid = true;
            if (config.hasSizes && !this.selectedSize) {
                this.sizeError = true;
                valid = false;
            }
            if (config.hasColors && !this.selectedColor) {
                this.colorError = true;
                valid = false;
            }
            if (config.hasShades && !this.selectedShade) {
                this.shadeError = true;
                valid = false;
            }

            if (!valid) {
                const missing = [];
                if (config.hasSizes && !this.selectedSize) missing.push('Size');
                if (config.hasColors && !this.selectedColor) missing.push('Color');
                if (config.hasShades && !this.selectedShade) missing.push('Shade');
                Swal.fire({
                    icon: 'warning',
                    title: 'Please Select Options',
                    text: 'Please select ' + missing.join(', ') + ' before proceeding.',
                    confirmButtonColor: '#15803d',
                    customClass: {
                        popup: 'rounded-2xl border border-cream-200 dark:border-gray-700 shadow-2xl',
                        confirmButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm'
                    }
                });
            }
            return valid;
        },
        async addToCart() {
            if (!this.validateSelections()) return;
            if (this.loading || this.buyLoading || this.maxStock <= 0) return;
            this.loading = true;

            try {
                const res = await fetch(config.addUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_id: config.productId,
                        quantity: this.qty,
                        size: this.selectedSize,
                        color: this.selectedColor,
                        shade: this.selectedShade
                    })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    const badge = document.getElementById('header-cart-count');
                    if (badge) {
                        badge.textContent = data.count;
                        badge.classList.remove('hidden');
                    }

                    const optSummary = [this.selectedSize, this.selectedColor, this.selectedShade].filter(Boolean).join(' · ');

                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Cart!',
                        html: '<p class="text-sm text-gray-600 dark:text-gray-300"><strong>' + this.qty + 'x</strong> &ldquo;' + config.productName + '&rdquo;' + (optSummary ? ' <br><span class="text-xs text-brand-700 font-medium">(' + optSummary + ')</span>' : '') + ' has been added to your shopping bag.</p>',
                        showCancelButton: true,
                        confirmButtonText: 'View Cart',
                        cancelButtonText: 'Continue Shopping',
                        confirmButtonColor: '#15803d',
                        cancelButtonColor: '#6b7280',
                        background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#111827',
                        customClass: {
                            popup: 'rounded-2xl border border-cream-200 dark:border-gray-700 shadow-2xl',
                            confirmButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm',
                            cancelButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = config.cartUrl;
                        }
                    });
                } else {
                    const msg = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Failed to add item to cart.');
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to Add',
                        text: msg,
                        confirmButtonColor: '#15803d'
                    });
                }
            } catch (err) {
                console.error('Cart add error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Could not connect to server. Please try again.',
                    confirmButtonColor: '#15803d'
                });
            } finally {
                this.loading = false;
            }
        },
        buyNow() {
            if (!this.validateSelections()) return;
            if (this.buyLoading || this.loading || this.maxStock <= 0) return;
            this.buyLoading = true;
            const params = new URLSearchParams({
                buy_now: 1,
                product_id: config.productId,
                quantity: this.qty
            });
            if (this.selectedSize) params.set('size', this.selectedSize);
            if (this.selectedColor) params.set('color', this.selectedColor);
            if (this.selectedShade) params.set('shade', this.selectedShade);

            window.location.href = `${config.checkoutUrl}?${params.toString()}`;
        }
    };
};

window.reviewImageModal = function() {
    return {
        isOpen: false,
        activeReview: null,
        activeIndex: 0,
        zoom: 1,
        minZoom: 1,
        maxZoom: 3.5,
        panX: 0,
        panY: 0,
        isDragging: false,
        startX: 0,
        startY: 0,
        open(reviewData, index = 0) {
            this.activeReview = reviewData;
            this.activeIndex = (index >= 0 && reviewData.images && index < reviewData.images.length) ? index : 0;
            this.resetZoom();
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },
        close() {
            this.isOpen = false;
            this.resetZoom();
            document.body.style.overflow = '';
        },
        prev() {
            if (!this.activeReview || !this.activeReview.images || this.activeReview.images.length <= 1) return;
            this.activeIndex = (this.activeIndex - 1 + this.activeReview.images.length) % this.activeReview.images.length;
            this.resetZoom();
        },
        next() {
            if (!this.activeReview || !this.activeReview.images || this.activeReview.images.length <= 1) return;
            this.activeIndex = (this.activeIndex + 1) % this.activeReview.images.length;
            this.resetZoom();
        },
        selectImage(idx) {
            this.activeIndex = idx;
            this.resetZoom();
        },
        zoomIn() {
            this.zoom = Math.min(this.maxZoom, Math.round((this.zoom + 0.3) * 10) / 10);
        },
        zoomOut() {
            this.zoom = Math.max(this.minZoom, Math.round((this.zoom - 0.3) * 10) / 10);
            if (this.zoom <= 1) {
                this.resetPan();
            }
        },
        toggleZoom() {
            if (this.zoom > 1) {
                this.resetZoom();
            } else {
                this.zoom = 2;
                this.resetPan();
            }
        },
        resetZoom() {
            this.zoom = 1;
            this.resetPan();
        },
        resetPan() {
            this.panX = 0;
            this.panY = 0;
            this.isDragging = false;
        },
        onWheel(e) {
            const delta = e.deltaY < 0 ? 0.25 : -0.25;
            const newZoom = Math.max(this.minZoom, Math.min(this.maxZoom, Math.round((this.zoom + delta) * 100) / 100));
            this.zoom = newZoom;
            if (this.zoom <= 1) {
                this.resetPan();
            }
        },
        startPan(e) {
            if (this.zoom <= 1) return;
            this.isDragging = true;
            this.startX = e.clientX - this.panX;
            this.startY = e.clientY - this.panY;
        },
        doPan(e) {
            if (!this.isDragging || this.zoom <= 1) return;
            this.panX = e.clientX - this.startX;
            this.panY = e.clientY - this.startY;
        },
        endPan() {
            this.isDragging = false;
        }
    };
};
</script>
@endpush

