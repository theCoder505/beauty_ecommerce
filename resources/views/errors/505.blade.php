@extends('users.layouts.app')

@php
    $settings = app(\App\Services\SettingService::class)->all();
    $siteName = $settings['site_name'] ?? config('app.name', 'GlowHaus');
    $metaTitle = '505 - Protocol Version Not Supported | ' . $siteName;
    $metaDescription = 'The server does not support the HTTP protocol version used in the request. Discover our luxury beauty collections.';
@endphp

@section('content')
<section class="relative overflow-hidden min-h-[85vh] flex items-center justify-center py-16 px-4 bg-cream-50 dark:bg-gray-950">
    {{-- Ambient decorative background glows --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-96 md:w-[500px] h-96 md:h-[500px] rounded-full bg-amber-100/40 dark:bg-amber-950/20 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 md:w-[480px] h-96 md:h-[480px] rounded-full bg-cream-300/60 dark:bg-gray-800/30 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-brand-100/30 dark:bg-brand-950/20 blur-[100px]"></div>
    </div>

    {{-- Main Container --}}
    <div class="relative max-w-4xl mx-auto w-full text-center">

        {{-- Card Wrapper --}}
        <div class="relative bg-white/70 dark:bg-gray-900/70 backdrop-blur-xl border border-cream-200 dark:border-gray-800 rounded-3xl p-6 sm:p-12 md:p-16 shadow-2xl shadow-cream-200/50 dark:shadow-none overflow-hidden">

            {{-- Watermark 505 in background --}}
            <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 font-serif font-black text-[130px] sm:text-[200px] md:text-[250px] leading-none text-cream-200/40 dark:text-gray-800/25 select-none pointer-events-none -z-0">
                505
            </span>

            <div class="relative z-10 max-w-2xl mx-auto space-y-6">

                {{-- Status Pill Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold tracking-[0.25em] uppercase bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 shadow-sm">
                    <span class="text-amber-600 dark:text-amber-400">✦</span> 505 ERROR &bull; PROTOCOL NOT SUPPORTED
                </div>

                {{-- Visual Icon / Illustration --}}
                <div class="relative my-4 flex justify-center">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-tr from-amber-100 to-cream-100 dark:from-amber-950 dark:to-gray-800 flex items-center justify-center p-5 shadow-inner border border-amber-200/60 dark:border-gray-700 ring-8 ring-amber-50/50 dark:ring-gray-900/50">
                        {{-- Luxury Apothecary / Compass / Protocol Synchronization Motif SVG --}}
                        <svg class="w-full h-full text-amber-700 dark:text-amber-400" viewBox="0 0 64 64" fill="none" stroke="currentColor">
                            <!-- Outer Elegant Ring -->
                            <circle cx="32" cy="32" r="23" stroke-width="2.5" stroke-dasharray="6 3"/>
                            <!-- Inner Concentric Dial -->
                            <circle cx="32" cy="32" r="14" stroke-width="2"/>
                            <!-- Balanced Center Crystal -->
                            <polygon points="32,22 37,32 32,42 27,32" stroke-width="2" fill="currentColor" fill-opacity="0.15"/>
                            <circle cx="32" cy="32" r="2.5" fill="currentColor"/>
                            <!-- Sparks -->
                            <path d="M32 4V8M32 56V60M4 32H8M56 32H60" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>

                {{-- Headline --}}
                <h1 class="font-serif text-3xl sm:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
                    A Momentary Pause in <br>
                    <em class="italic text-brand-700 dark:text-brand-400">Harmonious Connection</em>
                </h1>

                {{-- Description --}}
                <p class="text-gray-600 dark:text-gray-400 text-base sm:text-lg leading-relaxed">
                    The server does not support the HTTP protocol version transmitted by your request. A quick browser refresh or checking for updates usually resolves this instantly.
                </p>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 pt-4">
                    <button type="button" onclick="window.location.reload()"
                            class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white font-semibold px-7 py-3 rounded-full transition duration-200 shadow-lg shadow-brand-700/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh Page
                    </button>

                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 border border-gray-300 dark:border-gray-700 hover:border-brand-700 dark:hover:border-brand-500 text-gray-800 dark:text-gray-200 hover:text-brand-700 dark:hover:text-brand-400 font-semibold px-6 py-3 rounded-full transition duration-200 bg-white/40 dark:bg-gray-800/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Back to Home
                    </a>

                    <a href="{{ route('shop.index') }}"
                       class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-brand-700 dark:hover:text-brand-400 text-sm font-medium px-5 py-3 rounded-full hover:bg-cream-100 dark:hover:bg-gray-800/50 transition duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Browse Shop
                    </a>
                </div>

                {{-- Status explanation footnote --}}
                <div class="pt-4 border-t border-cream-200/60 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    If this error persists, please try updating your browser to the latest version or reach out to our concierge support team.
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
