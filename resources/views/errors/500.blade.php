@extends('users.layouts.app')

@php
    $settings = app(\App\Services\SettingService::class)->all();
    $siteName = $settings['site_name'] ?? config('app.name', 'GlowHaus');
    $metaTitle = '500 - Server Error | ' . $siteName;
    $metaDescription = 'Our servers encountered an unexpected issue. Our team is actively refining the system. Explore our luxury beauty collections.';
@endphp

@section('content')
<section class="relative overflow-hidden min-h-[85vh] flex items-center justify-center py-16 px-4 bg-cream-50 dark:bg-gray-950">
    {{-- Ambient decorative background glows --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-96 md:w-[500px] h-96 md:h-[500px] rounded-full bg-rose-100/40 dark:bg-rose-950/20 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 md:w-[480px] h-96 md:h-[480px] rounded-full bg-cream-300/60 dark:bg-gray-800/30 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-brand-100/30 dark:bg-brand-950/20 blur-[100px]"></div>
    </div>

    {{-- Main Container --}}
    <div class="relative max-w-4xl mx-auto w-full text-center">

        {{-- Card Wrapper --}}
        <div class="relative bg-white/70 dark:bg-gray-900/70 backdrop-blur-xl border border-cream-200 dark:border-gray-800 rounded-3xl p-6 sm:p-12 md:p-16 shadow-2xl shadow-cream-200/50 dark:shadow-none overflow-hidden">

            {{-- Watermark 500 in background --}}
            <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 font-serif font-black text-[130px] sm:text-[200px] md:text-[250px] leading-none text-cream-200/40 dark:text-gray-800/25 select-none pointer-events-none -z-0">
                500
            </span>

            <div class="relative z-10 max-w-2xl mx-auto space-y-6">

                {{-- Status Pill Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold tracking-[0.25em] uppercase bg-rose-50 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60 shadow-sm">
                    <span class="text-rose-600 dark:text-rose-400">✦</span> 500 ERROR &bull; SERVER ERROR
                </div>

                {{-- Visual Icon / Illustration --}}
                <div class="relative my-4 flex justify-center">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-tr from-rose-100 to-cream-100 dark:from-rose-950 dark:to-gray-800 flex items-center justify-center p-5 shadow-inner border border-rose-200/60 dark:border-gray-700 ring-8 ring-rose-50/50 dark:ring-gray-900/50">
                        {{-- Luxury Botanical Flask / Alembic Calibration SVG --}}
                        <svg class="w-full h-full text-rose-700 dark:text-rose-400" viewBox="0 0 64 64" fill="none" stroke="currentColor">
                            <!-- Flask Neck & Lip -->
                            <rect x="27" y="10" width="10" height="4" rx="1.5" stroke-width="2.5"/>
                            <path d="M29 14V26L16 48C14 51.5 16.5 56 20.5 56H43.5C47.5 56 50 51.5 48 48L35 26V14" stroke-width="2.5" stroke-linejoin="round"/>
                            <!-- Liquid Level with gentle wave -->
                            <path d="M21 44C25 42 29 46 33 44C37 42 41 45 43 44" stroke-width="2" stroke-linecap="round"/>
                            <!-- Botanical leaf / essence infusion inside -->
                            <path d="M32 34C32 34 36 38 34 42C32 46 28 42 28 42C28 42 28 36 32 34Z" stroke-width="2" stroke-linejoin="round" fill="currentColor" fill-opacity="0.15"/>
                            <!-- Sparkles -->
                            <circle cx="48" cy="18" r="1.5" fill="currentColor"/>
                            <circle cx="15" cy="28" r="1.5" fill="currentColor"/>
                            <path d="M46 22L48 24M50 20L48 22" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>

                {{-- Headline --}}
                <h1 class="font-serif text-3xl sm:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
                    Our Formulation Lab is <br>
                    <em class="italic text-brand-700 dark:text-brand-400">Refining the Blend</em>
                </h1>

                {{-- Description --}}
                <p class="text-gray-600 dark:text-gray-400 text-base sm:text-lg leading-relaxed">
                    Our servers encountered an unexpected hiccup while preparing your luxury experience. Our technical artisans have been alerted and are already restoring full harmony.
                </p>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 pt-4">
                    <button type="button" onclick="window.location.reload()"
                            class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white font-semibold px-7 py-3 rounded-full transition duration-200 shadow-lg shadow-brand-700/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Try Again
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
                        Browse Collections
                    </a>
                </div>

                {{-- Status explanation footnote --}}
                <div class="pt-4 border-t border-cream-200/60 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    If this error continues after a few moments, please reach out to our concierge customer service team for prompt assistance.
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
