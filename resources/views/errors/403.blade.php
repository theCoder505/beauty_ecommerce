@extends('users.layouts.app')

@php
    $settings = app(\App\Services\SettingService::class)->all();
    $siteName = $settings['site_name'] ?? config('app.name', 'GlowHaus');
    $metaTitle = '403 - Access Restricted | ' . $siteName;
    $metaDescription = 'Access to this area is restricted. Explore our luxury beauty collections.';
@endphp

@section('content')
<section class="relative overflow-hidden min-h-[85vh] flex items-center justify-center py-16 px-4 bg-cream-50 dark:bg-gray-950">
    {{-- Ambient decorative background glows --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-96 md:w-[500px] h-96 md:h-[500px] rounded-full bg-slate-200/50 dark:bg-slate-900/20 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 md:w-[480px] h-96 md:h-[480px] rounded-full bg-cream-300/60 dark:bg-gray-800/30 blur-3xl"></div>
    </div>

    {{-- Main Container --}}
    <div class="relative max-w-4xl mx-auto w-full text-center">

        {{-- Card Wrapper --}}
        <div class="relative bg-white/70 dark:bg-gray-900/70 backdrop-blur-xl border border-cream-200 dark:border-gray-800 rounded-3xl p-6 sm:p-12 md:p-16 shadow-2xl shadow-cream-200/50 dark:shadow-none overflow-hidden">

            {{-- Watermark 403 in background --}}
            <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 font-serif font-black text-[130px] sm:text-[200px] md:text-[250px] leading-none text-cream-200/40 dark:text-gray-800/25 select-none pointer-events-none -z-0">
                403
            </span>

            <div class="relative z-10 max-w-2xl mx-auto space-y-6">

                {{-- Status Pill Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold tracking-[0.25em] uppercase bg-gray-100 dark:bg-gray-800/80 text-gray-800 dark:text-gray-300 border border-gray-300 dark:border-gray-700 shadow-sm">
                    <span class="text-brand-600 dark:text-brand-400">✦</span> 403 ERROR &bull; ACCESS RESTRICTED
                </div>

                {{-- Visual Icon / Illustration --}}
                <div class="relative my-4 flex justify-center">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-tr from-gray-100 to-cream-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center p-5 shadow-inner border border-gray-200/60 dark:border-gray-700 ring-8 ring-gray-100/50 dark:ring-gray-900/50">
                        {{-- Luxury Lock / Private Chamber Key SVG --}}
                        <svg class="w-full h-full text-gray-700 dark:text-gray-300" viewBox="0 0 64 64" fill="none" stroke="currentColor">
                            <rect x="18" y="26" width="28" height="24" rx="4" stroke-width="2.5"/>
                            <path d="M24 26V18C24 13.58 27.58 10 32 10C36.42 10 40 13.58 40 18V26" stroke-width="2.5" stroke-linecap="round"/>
                            <circle cx="32" cy="37" r="3" fill="currentColor"/>
                            <path d="M32 40V44" stroke-width="2.5" stroke-linecap="round"/>
                            <circle cx="50" cy="18" r="1.5" fill="currentColor"/>
                        </svg>
                    </div>
                </div>

                {{-- Headline --}}
                <h1 class="font-serif text-3xl sm:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
                    A Private Suite, <br>
                    <em class="italic text-brand-700 dark:text-brand-400">Exclusive Territory</em>
                </h1>

                {{-- Description --}}
                <p class="text-gray-600 dark:text-gray-400 text-base sm:text-lg leading-relaxed">
                    You do not have permission to access this private destination. If you believe this is in error, please sign in to your authorized account or return to the main gallery.
                </p>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 pt-4">
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white font-semibold px-7 py-3 rounded-full transition duration-200 shadow-lg shadow-brand-700/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Back to Home
                    </a>

                    @guest
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 border border-gray-300 dark:border-gray-700 hover:border-brand-700 dark:hover:border-brand-500 text-gray-800 dark:text-gray-200 hover:text-brand-700 dark:hover:text-brand-400 font-semibold px-6 py-3 rounded-full transition duration-200 bg-white/40 dark:bg-gray-800/40">
                        Sign In
                    </a>
                    @endguest

                    <a href="{{ route('shop.index') }}"
                       class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-brand-700 dark:hover:text-brand-400 text-sm font-medium px-5 py-3 rounded-full hover:bg-cream-100 dark:hover:bg-gray-800/50 transition duration-200">
                        Browse Collections
                    </a>
                </div>

            </div>
        </div>

    </div>
</section>
@endsection
