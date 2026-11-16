@extends('users.layouts.app')

@php
    $settings = app(\App\Services\SettingService::class)->all();
    $siteName = $settings['site_name'] ?? config('app.name', 'GlowHaus');
    $metaTitle = '404 - Page Not Found | ' . $siteName;
    $metaDescription = 'The page you are looking for is not available. Explore our luxury beauty, fragrance, and skincare collections.';
@endphp

@section('content')
<section class="relative overflow-hidden min-h-[85vh] flex items-center justify-center py-16 px-4 bg-cream-50 dark:bg-gray-950">
    {{-- Ambient decorative background glows --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-96 md:w-[500px] h-96 md:h-[500px] rounded-full bg-brand-100/50 dark:bg-brand-900/20 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 md:w-[480px] h-96 md:h-[480px] rounded-full bg-cream-300/60 dark:bg-gray-800/30 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-cream-200/30 dark:bg-brand-950/20 blur-[100px]"></div>
    </div>

    {{-- Main Container --}}
    <div class="relative max-w-4xl mx-auto w-full text-center">

        {{-- Card Wrapper --}}
        <div class="relative bg-white/70 dark:bg-gray-900/70 backdrop-blur-xl border border-cream-200 dark:border-gray-800 rounded-3xl p-6 sm:p-12 md:p-16 shadow-2xl shadow-cream-200/50 dark:shadow-none overflow-hidden">

            {{-- Watermark 404 in background --}}
            <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 font-serif font-black text-[130px] sm:text-[200px] md:text-[250px] leading-none text-cream-200/40 dark:text-gray-800/25 select-none pointer-events-none -z-0">
                404
            </span>

            <div class="relative z-10 max-w-2xl mx-auto space-y-6">

                {{-- Status Pill Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold tracking-[0.25em] uppercase bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-800/60 shadow-sm">
                    <span class="text-brand-600 dark:text-brand-400">✦</span> 404 ERROR &bull; PAGE NOT FOUND
                </div>

                {{-- Visual Icon / Illustration --}}
                <div class="relative my-4 flex justify-center">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-tr from-brand-100 to-cream-100 dark:from-brand-950 dark:to-gray-800 flex items-center justify-center p-5 shadow-inner border border-brand-200/50 dark:border-gray-700 ring-8 ring-brand-50/50 dark:ring-gray-900/50">
                        {{-- Luxury Perfume Flacon / Beauty Mist SVG --}}
                        <svg class="w-full h-full text-brand-700 dark:text-brand-400" viewBox="0 0 64 64" fill="none" stroke="currentColor">
                            <!-- Cap / Atomizer -->
                            <rect x="26" y="8" width="12" height="7" rx="2" stroke-width="2.5" stroke-linecap="round"/>
                            <path d="M32 15V22" stroke-width="2.5" stroke-linecap="round"/>
                            <path d="M22 22H42" stroke-width="2.5" stroke-linecap="round"/>
                            <!-- Bottle Body -->
                            <path d="M20 22C16 22 14 25 14 29V49C14 54 18 57 23 57H41C46 57 50 54 50 49V29C50 25 48 22 44 22H20Z" stroke-width="2.5" stroke-linejoin="round"/>
                            <!-- Facet Details -->
                            <path d="M23 29L20 49" stroke-width="1.8" stroke-dasharray="2 2" stroke-linecap="round"/>
                            <path d="M41 29L44 49" stroke-width="1.8" stroke-dasharray="2 2" stroke-linecap="round"/>
                            <circle cx="32" cy="40" r="7" stroke-width="2" stroke-dasharray="3 3"/>
                            <!-- Sparkles / Evaporating mist -->
                            <path d="M47 12L49 14M52 10L50 12" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="51" cy="18" r="1.5" fill="currentColor"/>
                            <path d="M14 14L16 16M11 17L13 19" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                        </svg>
                    </div>
                </div>

                {{-- Headline --}}
                <h1 class="font-serif text-3xl sm:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
                    Lost in Fragrance, <br>
                    <em class="italic text-brand-700 dark:text-brand-400">Not in Elegance</em>
                </h1>

                {{-- Description --}}
                <p class="text-gray-600 dark:text-gray-400 text-base sm:text-lg leading-relaxed">
                    The beauty formula, product, or destination you are seeking seems to have evaporated or moved to a new shelf. Let us guide you back to your radiant routine.
                </p>

                {{-- Direct Search Input --}}
                <div class="max-w-md mx-auto pt-2">
                    <form action="{{ route('shop.index') }}" method="GET" class="relative flex items-center">
                        <input type="text" name="search" placeholder="Search products, scents, skincare..."
                            class="w-full pl-11 pr-24 py-3 rounded-full text-sm bg-cream-50/80 dark:bg-gray-800/80 border border-cream-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-500 shadow-sm transition">
                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 absolute left-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <button type="submit" class="absolute right-1.5 px-4 py-2 bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold rounded-full transition shadow">
                            Search
                        </button>
                    </form>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 pt-4">
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white font-semibold px-7 py-3 rounded-full transition duration-200 shadow-lg shadow-brand-700/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Return Home
                    </a>

                    <a href="{{ route('shop.index') }}"
                       class="inline-flex items-center gap-2 border border-gray-300 dark:border-gray-700 hover:border-brand-700 dark:hover:border-brand-500 text-gray-800 dark:text-gray-200 hover:text-brand-700 dark:hover:text-brand-400 font-semibold px-6 py-3 rounded-full transition duration-200 bg-white/40 dark:bg-gray-800/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Explore Collections
                    </a>

                    <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('home') }}'"
                            class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 text-sm font-medium px-4 py-3 rounded-full hover:bg-cream-100 dark:hover:bg-gray-800/50 transition duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Go Back
                    </button>
                </div>

            </div>
        </div>

        {{-- Helpful Quick Discovery Shelf --}}
        <div class="mt-12 text-left">
            <p class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-500 dark:text-gray-400 text-center mb-6">
                Popular Beauty Destinations
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('shop.index') }}" class="group bg-white/60 dark:bg-gray-900/60 backdrop-blur border border-cream-200 dark:border-gray-800 hover:border-brand-700 dark:hover:border-brand-500 rounded-2xl p-4 transition duration-200 shadow-sm hover:shadow-md">
                    <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-950 flex items-center justify-center text-brand-700 dark:text-brand-300 mb-3 group-hover:scale-110 transition">
                        ✦
                    </div>
                    <p class="font-semibold text-sm text-gray-900 dark:text-white group-hover:text-brand-700 dark:group-hover:text-brand-400 transition">All Products</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Browse our full catalog</p>
                </a>

                <a href="{{ route('shop.index') }}?sort=popular" class="group bg-white/60 dark:bg-gray-900/60 backdrop-blur border border-cream-200 dark:border-gray-800 hover:border-brand-700 dark:hover:border-brand-500 rounded-2xl p-4 transition duration-200 shadow-sm hover:shadow-md">
                    <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-950 flex items-center justify-center text-brand-700 dark:text-brand-300 mb-3 group-hover:scale-110 transition">
                        ★
                    </div>
                    <p class="font-semibold text-sm text-gray-900 dark:text-white group-hover:text-brand-700 dark:group-hover:text-brand-400 transition">Best Sellers</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Most loved formulas</p>
                </a>

                <a href="{{ route('shop.index') }}?sort=newest" class="group bg-white/60 dark:bg-gray-900/60 backdrop-blur border border-cream-200 dark:border-gray-800 hover:border-brand-700 dark:hover:border-brand-500 rounded-2xl p-4 transition duration-200 shadow-sm hover:shadow-md">
                    <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-950 flex items-center justify-center text-brand-700 dark:text-brand-300 mb-3 group-hover:scale-110 transition">
                        ✧
                    </div>
                    <p class="font-semibold text-sm text-gray-900 dark:text-white group-hover:text-brand-700 dark:group-hover:text-brand-400 transition">New Arrivals</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Freshly released items</p>
                </a>

                <a href="{{ route('blog.index') }}" class="group bg-white/60 dark:bg-gray-900/60 backdrop-blur border border-cream-200 dark:border-gray-800 hover:border-brand-700 dark:hover:border-brand-500 rounded-2xl p-4 transition duration-200 shadow-sm hover:shadow-md">
                    <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-950 flex items-center justify-center text-brand-700 dark:text-brand-300 mb-3 group-hover:scale-110 transition">
                        🕮
                    </div>
                    <p class="font-semibold text-sm text-gray-900 dark:text-white group-hover:text-brand-700 dark:group-hover:text-brand-400 transition">Beauty Journal</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tips, rituals & guides</p>
                </a>
            </div>
        </div>

    </div>
</section>
@endsection
