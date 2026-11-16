@extends('users.layouts.app')

@php
    $settings = app(\App\Services\SettingService::class)->all();
    $siteName = $settings['site_name'] ?? config('app.name', 'GlowHaus');
    $metaTitle = '503 - Under Scheduled Maintenance | ' . $siteName;
    $metaDescription = 'We are currently performing brief maintenance to elevate your beauty shopping experience.';
@endphp

@section('content')
<section class="relative overflow-hidden min-h-[85vh] flex items-center justify-center py-16 px-4 bg-cream-50 dark:bg-gray-950">
    {{-- Ambient decorative background glows --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-96 md:w-[500px] h-96 md:h-[500px] rounded-full bg-brand-100/40 dark:bg-brand-900/20 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 md:w-[480px] h-96 md:h-[480px] rounded-full bg-cream-300/60 dark:bg-gray-800/30 blur-3xl"></div>
    </div>

    {{-- Main Container --}}
    <div class="relative max-w-4xl mx-auto w-full text-center">

        {{-- Card Wrapper --}}
        <div class="relative bg-white/70 dark:bg-gray-900/70 backdrop-blur-xl border border-cream-200 dark:border-gray-800 rounded-3xl p-6 sm:p-12 md:p-16 shadow-2xl shadow-cream-200/50 dark:shadow-none overflow-hidden">

            {{-- Watermark 503 in background --}}
            <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 font-serif font-black text-[130px] sm:text-[200px] md:text-[250px] leading-none text-cream-200/40 dark:text-gray-800/25 select-none pointer-events-none -z-0">
                503
            </span>

            <div class="relative z-10 max-w-2xl mx-auto space-y-6">

                {{-- Status Pill Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold tracking-[0.25em] uppercase bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-800/60 shadow-sm">
                    <span class="text-brand-600 dark:text-brand-400">✦</span> 503 &bull; ELEVATING THE EXPERIENCE
                </div>

                {{-- Visual Icon / Illustration --}}
                <div class="relative my-4 flex justify-center">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-tr from-brand-100 to-cream-100 dark:from-brand-950 dark:to-gray-800 flex items-center justify-center p-5 shadow-inner border border-brand-200/60 dark:border-gray-700 ring-8 ring-brand-50/50 dark:ring-gray-900/50">
                        {{-- Luxury Hourglass / Polishing Routine SVG --}}
                        <svg class="w-full h-full text-brand-700 dark:text-brand-400" viewBox="0 0 64 64" fill="none" stroke="currentColor">
                            <path d="M16 12H48" stroke-width="2.5" stroke-linecap="round"/>
                            <path d="M16 52H48" stroke-width="2.5" stroke-linecap="round"/>
                            <path d="M20 12V24L28 32L20 40V52" stroke-width="2.5" stroke-linejoin="round"/>
                            <path d="M44 12V24L36 32L44 40V52" stroke-width="2.5" stroke-linejoin="round"/>
                            <circle cx="32" cy="44" r="3" fill="currentColor"/>
                            <circle cx="32" cy="36" r="1.5" fill="currentColor"/>
                        </svg>
                    </div>
                </div>

                {{-- Headline --}}
                <h1 class="font-serif text-3xl sm:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
                    Brief Maintenance in Progress, <br>
                    <em class="italic text-brand-700 dark:text-brand-400">Refining Pure Luxury</em>
                </h1>

                {{-- Description --}}
                <p class="text-gray-600 dark:text-gray-400 text-base sm:text-lg leading-relaxed">
                    We are currently polishing our digital storefront to bring you smoother navigation, faster checkout, and fresh luxury arrivals. We will be back online shortly.
                </p>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 pt-4">
                    <button type="button" onclick="window.location.reload()"
                            class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white font-semibold px-7 py-3 rounded-full transition duration-200 shadow-lg shadow-brand-700/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Check Again
                    </button>
                </div>

            </div>
        </div>

    </div>
</section>
@endsection
