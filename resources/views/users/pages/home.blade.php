@extends('users.layouts.app')

@php
    $settings = app(\App\Services\SettingService::class)->all();
    $currency = $settings['currency_symbol'] ?? '$';

    // 5 Categories exactly matching the reference image
    $landingCategories = [
        [
            'name' => 'Skin Care',
            'slug' => 'skincare',
            'count' => '120+ Products',
            'image' => asset('images/landing/cat-skincare.jpg'),
        ],
        [
            'name' => 'Hair Care',
            'slug' => 'hair-care',
            'count' => '95+ Products',
            'image' => asset('images/landing/cat-haircare.jpg'),
        ],
        [
            'name' => 'Body Care',
            'slug' => 'body-care',
            'count' => '80+ Products',
            'image' => asset('images/landing/cat-bodycare.jpg'),
        ],
        [
            'name' => 'Makeup',
            'slug' => 'foundation-concealer',
            'count' => '150+ Products',
            'image' => asset('images/landing/cat-makeup.jpg'),
        ],
        [
            'name' => 'Health & Wellness',
            'slug' => 'sun-care',
            'count' => '90+ Products',
            'image' => asset('images/landing/cat-wellness.jpg'),
        ],
    ];

    // 5 Best Sellers exactly matching the reference image
    $landingProducts = [
        [
            'id' => 1,
            'name' => 'Vitamin C Face Serum',
            'slug' => 'vitamin-c-brightening-serum',
            'size' => '30ml',
            'rating' => 5,
            'reviews' => 265,
            'price' => 24.99,
            'old_price' => 34.99,
            'image' => asset('images/landing/prod-serum.jpg'),
        ],
        [
            'id' => 2,
            'name' => 'Hydrating Face Cream',
            'slug' => 'hydra-boost-moisturizer',
            'size' => '50g',
            'rating' => 5,
            'reviews' => 198,
            'price' => 19.99,
            'old_price' => 29.99,
            'image' => asset('images/landing/prod-cream.jpg'),
        ],
        [
            'id' => 31,
            'name' => 'Keratin Shampoo',
            'slug' => 'argan-oil-repair-shampoo-400ml',
            'size' => '200ml',
            'rating' => 5,
            'reviews' => 310,
            'price' => 15.99,
            'old_price' => 22.99,
            'image' => asset('images/landing/prod-shampoo.jpg'),
        ],
        [
            'id' => 44,
            'name' => 'Aloe Vera Gel',
            'slug' => 'soothing-after-sun-gel-200ml',
            'size' => '150ml',
            'rating' => 5,
            'reviews' => 154,
            'price' => 12.99,
            'old_price' => 18.99,
            'image' => asset('images/landing/prod-aloevera.jpg'),
        ],
        [
            'id' => 11,
            'name' => 'Matte Lipstick',
            'slug' => 'velvet-matte-lipstick-ruby-red',
            'size' => '4.2g',
            'rating' => 5,
            'reviews' => 212,
            'price' => 9.99,
            'old_price' => 14.99,
            'image' => asset('images/landing/prod-lipstick.jpg'),
        ],
    ];
@endphp

@section('content')

    @php
        $heroSlide = isset($heroSlides) && $heroSlides->isNotEmpty() ? $heroSlides->first() : null;
        $heroRightImage = null;
        if (!empty($settings['hero_image'])) {
            $heroRightImage = asset('storage/' . $settings['hero_image']);
        } elseif ($heroSlide && !empty($heroSlide->image)) {
            $heroRightImage = $heroSlide->image_url;
        } else {
            $heroRightImage = asset('images/landing/hero-model-seamless.png');
        }

        $heroBadge = $heroSlide->badge_text ?? '✦ Radiate Confidence Every Day';
        $heroTitle = $heroSlide->title ?? 'Beauty & Wellness for a Better You';
        $heroSubtitle =
            $heroSlide->subtitle ?? 'Discover premium health & beauty products for healthy skin, hair & body.';
        $heroBtn1Text = $heroSlide->primary_button_text ?? 'Shop Now';
        $heroBtn1Url = $heroSlide->primary_button_url ?? route('shop.index');
        $heroBtn2Text = $heroSlide->secondary_button_text ?? 'Explore Categories';
        $heroBtn2Url = $heroSlide->secondary_button_url ?? '#categories';

        $hasMultipleSlides = isset($heroSlides) && $heroSlides->count() > 1 && empty($settings['hero_image']);
        $slidesJson =
            isset($heroSlides) && $heroSlides->isNotEmpty()
                ? $heroSlides
                    ->map(function ($slide) {
                        return [
                            'badge' => $slide->badge_text ?? '✦ Radiate Confidence Every Day',
                            'title' => $slide->title,
                            'subtitle' =>
                                $slide->subtitle ??
                                'Discover premium health & beauty products for healthy skin, hair & body.',
                            'primary_btn_text' => $slide->primary_button_text ?? 'Shop Now',
                            'primary_btn_url' => $slide->primary_button_url ?? route('shop.index'),
                            'secondary_btn_text' => $slide->secondary_button_text ?? 'Explore Categories',
                            'secondary_btn_url' => $slide->secondary_button_url ?? '#categories',
                            'image' => $slide->image_url,
                            'floating_badge' => $slide->floating_badge_text,
                        ];
                    })
                    ->values()
                : [];
    @endphp

    {{-- ═══════════════════════════════════════════════════════════════════
     1. HERO SECTION (100% Matching Reference Image)
     ═══════════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden text-white -mt-16 sm:-mt-20 pt-16 sm:pt-20"
        style="background: linear-gradient(105deg, #fa7e8c 0%, #ee4680 25%, #e1276f 55%, #b3145c 80%, #7d0b48 100%);"
        @if ($hasMultipleSlides) x-data="{
        slides: {{ json_encode($slidesJson) }},
        active: 0,
        timer: null,
        init() {
            if (this.slides.length > 1) {
                this.timer = setInterval(() => {
                    this.active = (this.active + 1) % this.slides.length;
                }, 5000);
            }
        }
    }" @endif>

        {{-- Organic glowing petals and sparkles matching the mockup --}}
        <div class="absolute inset-0 pointer-events-none select-none overflow-hidden">
            {{-- Soft radiant glow orbs --}}
            <div class="absolute -top-24 -left-16 w-80 h-80 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute top-1/2 right-1/4 w-96 h-96 rounded-full bg-pink-300/15 blur-3xl"></div>
            <div class="absolute -bottom-20 left-1/3 w-72 h-72 rounded-full bg-rose-400/20 blur-2xl"></div>

            {{-- Glowing floral leaf overlays matching the reference image --}}
            <svg class="absolute -right-10 top-0 w-80 lg:w-120 h-full text-pink-300/15 pointer-events-none"
                viewBox="0 0 500 500" fill="currentColor">
                <path d="M450,150 Q480,250 350,320 Q200,400 320,150 Q380,50 450,150 Z" />
                <path d="M380,50 Q450,180 300,240 Q180,300 240,80 Q290,10 380,50 Z" opacity="0.6" />
                <path d="M480,260 Q520,380 360,420 Q220,450 340,280 Q400,200 480,260 Z" opacity="0.4" />
            </svg>

            {{-- Sparkling accents --}}
            <span class="absolute top-10 left-1/4 w-1.5 h-1.5 rounded-full bg-white/70 animate-ping"></span>
            <span class="absolute top-1/3 left-1/3 w-2 h-2 rounded-full bg-pink-100/60 blur-[1px]"></span>
            <span class="absolute bottom-16 left-1/2 w-1.5 h-1.5 rounded-full bg-white/80"></span>
            <span class="absolute top-20 right-1/3 w-1.5 h-1.5 rounded-full bg-white/70"></span>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-12 sm:pt-10 sm:pb-16 lg:pt-12 lg:pb-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-4 items-center">

                {{-- Left Column: Badge, Headline, Subtitle, CTA Buttons --}}
                <div class="lg:col-span-7 space-y-6 sm:space-y-7 text-center lg:text-left z-10">
                    {{-- Translucent Frosted Badge --}}
                    <div
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-xs sm:text-sm font-semibold tracking-wide shadow-sm">
                        <span class="text-white text-xs">✦</span>
                        <span
                            @if ($hasMultipleSlides) x-text="slides[active].badge" @endif>{{ $heroBadge }}</span>
                    </div>

                    {{-- Headline --}}
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.14] max-w-md sm:max-w-lg mx-auto lg:mx-0"
                        @if ($hasMultipleSlides) x-text="slides[active].title" @endif>
                        {{ $heroTitle }}
                    </h1>

                    {{-- Subtitle --}}
                    <p class="text-pink-50/90 text-sm sm:text-base lg:text-lg max-w-md sm:max-w-lg mx-auto lg:mx-0 leading-relaxed font-normal"
                        @if ($hasMultipleSlides) x-text="slides[active].subtitle" @endif>
                        {{ $heroSubtitle }}
                    </p>

                    {{-- Action CTA Buttons --}}
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-2">
                        {{-- Shop Now Button --}}
                        <a :href="@if ($hasMultipleSlides) slides[active].primary_btn_url @else '{{ $heroBtn1Url }}' @endif"
                            class="inline-flex items-center gap-2 bg-[#ff1475] hover:bg-[#e00062] active:scale-95 text-white font-bold px-7 sm:px-8 py-3.5 rounded-full shadow-lg shadow-pink-950/20 hover:shadow-pink-950/40 hover:-translate-y-0.5 transition-all text-sm group">
                            <span
                                @if ($hasMultipleSlides) x-text="slides[active].primary_btn_text" @endif>{{ $heroBtn1Text }}</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>

                        {{-- Explore Categories Button --}}
                        <a :href="@if ($hasMultipleSlides) slides[active].secondary_btn_url @else '{{ $heroBtn2Url }}' @endif"
                            class="inline-flex items-center gap-2.5 bg-white hover:bg-pink-50 active:scale-95 text-gray-900 font-bold px-6 sm:px-7 py-3.5 rounded-full shadow-md hover:-translate-y-0.5 transition-all text-sm group">
                            <span
                                @if ($hasMultipleSlides) x-text="slides[active].secondary_btn_text" @endif>{{ $heroBtn2Text }}</span>
                            <svg class="w-4 h-4 text-pink-600 transition-transform group-hover:rotate-45"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </a>
                    </div>

                    {{-- Carousel Indicators when multiple slides exist --}}
                    @if ($hasMultipleSlides)
                        <div class="flex items-center justify-center lg:justify-start gap-2 pt-2">
                            <template x-for="(s, idx) in slides" :key="idx">
                                <button type="button" @click="active = idx"
                                    :class="active === idx ? 'w-8 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
                                    class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                                    :aria-label="'Go to slide ' + (idx + 1)"></button>
                            </template>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Beautiful Model seamlessly integrated into the hero background --}}
                <div class="lg:col-span-5 relative flex items-end justify-center lg:justify-end">
                    <div
                        class="relative w-full max-w-105 sm:max-w-120 lg:max-w-132.5 h-75 sm:h-95 lg:h-115 xl:h-125 flex items-end justify-center lg:justify-end overflow-hidden">
                        @if ($hasMultipleSlides)
                            @foreach ($slidesJson as $idx => $s)
                                <img x-show="active === {{ $idx }}"
                                    x-transition:enter="transition-opacity ease-out duration-500"
                                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition-opacity ease-in duration-300 absolute"
                                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                    src="{{ $s['image'] }}" alt="{{ $s['title'] }}" width="530" height="500"
                                    @if ($idx === 0) fetchpriority="high" @else loading="lazy" @endif
                                    class="max-w-full max-h-75 sm:max-h-95 lg:max-h-115 xl:max-h-125 w-auto h-auto object-contain object-bottom select-none drop-shadow-xl transition-transform duration-700 hover:scale-[1.02] {{ $idx > 0 ? 'hidden' : '' }}"
                                    :class="{ 'hidden': false }">
                            @endforeach
                        @else
                            <img src="{{ $heroRightImage }}" alt="{{ $heroTitle }}" width="530" height="500"
                                fetchpriority="high"
                                class="max-w-full max-h-75 sm:max-h-95 lg:max-h-115 xl:max-h-125 w-auto h-auto object-contain object-bottom select-none drop-shadow-xl transition-transform duration-700 hover:scale-[1.02]">
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
     2. TRUST BADGES BAR (Floating Card Below Hero)
     ═══════════════════════════════════════════════════════════════════ --}}
    <section class="relative z-30 -mt-6 sm:-mt-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="bg-white rounded-2xl sm:rounded-3xl shadow-xl shadow-pink-900/5 border border-pink-100 p-5 sm:p-7 grid grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8 backdrop-blur-sm">
            {{-- Item 1: 100% Original --}}
            <div class="flex items-center gap-3.5">
                <div
                    class="w-12 h-12 rounded-2xl bg-pink-50 text-[#e1276f] flex items-center justify-center shrink-0 border border-pink-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900 leading-tight">100% Original</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Authentic &amp; trusted brands</p>
                </div>
            </div>

            {{-- Item 2: Expert Approved --}}
            <div class="flex items-center gap-3.5">
                <div
                    class="w-12 h-12 rounded-2xl bg-pink-50 text-[#e1276f] flex items-center justify-center shrink-0 border border-pink-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900 leading-tight">Expert Approved</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Dermatologically tested</p>
                </div>
            </div>

            {{-- Item 3: Fast Delivery --}}
            <div class="flex items-center gap-3.5">
                <div
                    class="w-12 h-12 rounded-2xl bg-pink-50 text-[#e1276f] flex items-center justify-center shrink-0 border border-pink-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900 leading-tight">Fast Delivery</h4>
                    <p class="text-xs text-gray-500 mt-0.5">At your doorstep</p>
                </div>
            </div>

            {{-- Item 4: Easy Returns --}}
            <div class="flex items-center gap-3.5">
                <div
                    class="w-12 h-12 rounded-2xl bg-pink-50 text-[#e1276f] flex items-center justify-center shrink-0 border border-pink-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900 leading-tight">Easy Returns</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Hassle-free returns</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
     3. SHOP BY CATEGORY SECTION (Matching Screenshot)
     ═══════════════════════════════════════════════════════════════════ --}}
    <section id="categories" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-18">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    Shop by Category
                </h2>
            </div>
            <a href="{{ route('shop.index') }}"
                class="text-xs sm:text-sm font-semibold text-[#e1276f] hover:text-[#c2185b] flex items-center gap-1.5 group">
                <span>View All Categories</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
            @if (isset($featuredCategories) && $featuredCategories->isNotEmpty())
                @foreach ($featuredCategories->take(10) as $cat)
                    <a href="{{ route('shop.category', $cat->slug) }}"
                        class="group bg-white border border-pink-100 rounded-3xl overflow-hidden text-center shadow-sm hover:shadow-xl hover:shadow-pink-500/10 hover:-translate-y-1.5 transition-all duration-300 flex flex-col items-center">

                        {{-- Image Box with Soft Warm Pink Background --}}
                        <div
                            class="w-full aspect-square overflow-hidden bg-[#fff5f7] flex items-center justify-center border border-pink-50">
                            <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>

                        <div class="p-4">
                            {{-- Label --}}
                            <h3
                                class="font-bold text-sm sm:text-base text-gray-900 group-hover:text-[#e1276f] transition-colors leading-snug">
                                {{ $cat->name }}
                            </h3>
                            <p class="text-xs text-gray-400 mt-1 font-medium">
                                {{ $cat->products_count ?? 0 }} {{ Str::plural('Product', $cat->products_count ?? 0) }}
                            </p>
                        </div>
                    </a>
                @endforeach
            @else
                @foreach ($landingCategories as $cat)
                    <a href="{{ route('shop.category', $cat['slug']) }}"
                        class="group bg-white border border-pink-100 rounded-3xl overflow-hidden text-center shadow-sm hover:shadow-xl hover:shadow-pink-500/10 hover:-translate-y-1.5 transition-all duration-300 flex flex-col items-center">

                        <div
                            class="w-full aspect-square rounded-t-2xl overflow-hidden bg-[#fff5f7] flex items-center justify-center border border-pink-50 p-3">
                            <img src="{{ $cat['image'] }}" alt="{{ $cat['name'] }}"
                                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500">
                        </div>

                        <div class="p-4">
                            <h3
                                class="font-bold text-sm sm:text-base text-gray-900 group-hover:text-[#e1276f] transition-colors leading-snug">
                                {{ $cat['name'] }}
                            </h3>
                            <p class="text-xs text-gray-400 mt-1 font-medium">
                                {{ $cat['count'] }}
                            </p>
                        </div>
                    </a>
                @endforeach
            @endif
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
     4. ADVERTISEMENT BANNER 1 (Limited Time Offer - Up to 30% OFF)
     ═══════════════════════════════════════════════════════════════════ --}}
    <section id="offers" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <div class="relative overflow-hidden rounded-3xl shadow-xl text-white p-6 sm:p-10 lg:p-12"
            style="background: linear-gradient(105deg, #fa7e8c 0%, #ee4680 30%, #e1276f 65%, #c2185b 100%);">

            {{-- Glowing floral SVG overlays --}}
            <div class="absolute inset-0 pointer-events-none select-none overflow-hidden">
                <svg class="absolute -right-10 top-0 w-80 h-full text-white/10" viewBox="0 0 400 400"
                    fill="currentColor">
                    <path d="M400,100 Q450,250 250,300 Q150,350 280,150 Z" />
                </svg>
                <div class="absolute -bottom-10 -left-10 w-64 h-64 rounded-full bg-white/10 blur-2xl"></div>
            </div>

            <div class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                {{-- Left: Beauty Bottles Cluster --}}
                <div class="md:col-span-5 flex justify-center md:justify-start">
                    <div
                        class="relative w-full max-w-70 sm:max-w-[320px] rounded-2xl overflow-hidden shadow-lg border-2 border-white/30 bg-white/10 backdrop-blur-sm p-1">
                        <img src="{{ asset('images/landing/promo-bottles.jpg') }}" alt="Special Beauty Sale"
                            class="w-full h-auto object-cover rounded-xl select-none">
                    </div>
                </div>

                {{-- Right: Promotional Text & CTA Button --}}
                <div class="md:col-span-7 text-center md:text-left space-y-3 sm:space-y-4">
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/25 text-white text-xs font-semibold">
                        <span>✨</span>
                        <span>Limited Time Offer</span>
                    </div>
                    <h3 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight">
                        Up to 30% OFF
                    </h3>
                    <p class="text-pink-50 text-sm sm:text-base font-medium max-w-md mx-auto md:mx-0">
                        On Top Beauty &amp; Wellness Brands
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('shop.index') }}"
                            class="inline-flex items-center gap-2 bg-white text-[#e1276f] hover:text-[#c2185b] hover:bg-pink-50 active:scale-95 font-bold px-7 py-3.5 rounded-full shadow-lg hover:-translate-y-0.5 transition-all text-sm group">
                            <span>Shop the Sale</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
     5. BEST SELLERS SECTION (Matching Screenshot)
     ═══════════════════════════════════════════════════════════════════ --}}
    <section id="bestsellers" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-18">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    Best Sellers
                </h2>
            </div>
            <a href="{{ route('shop.index') }}?sort=popular"
                class="text-xs sm:text-sm font-semibold text-[#e1276f] hover:text-[#c2185b] flex items-center gap-1.5 group">
                <span>View All Products</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @if (isset($bestSellers) && $bestSellers->isNotEmpty())
                @foreach ($bestSellers->take(10) as $prod)
                    @php
                        $avgRating = round($prod->approved_reviews_avg_rating ?? 5);
                        $reviewCount = $prod->approved_reviews_count ?: 48;
                        $isWish = auth()->check()
                            ? app(\App\Services\WishlistService::class)->isWishlisted($prod->id)
                            : false;
                        $sizeLabel = $prod->unit_volume ?: ($prod->sizes->first()?->size ?: 'Standard Size');
                    @endphp
                    <div
                        class="bg-white rounded-2xl border border-pink-100 overflow-hidden flex flex-col justify-between hover:shadow-xl hover:shadow-pink-500/10 hover:-translate-y-1 transition-all duration-300 group">
                        <div>
                            {{-- Product Image Box --}}
                            <div
                                class="relative aspect-square bg-[#fff8fa] overflow-hidden flex items-center justify-center border border-pink-50">
                                <a href="{{ route('products.show', $prod->slug) }}"
                                    class="w-full h-full flex items-center justify-center">
                                    <img src="{{ $prod->thumbnail_url }}" alt="{{ $prod->name }}"
                                        class="max-h-full max-w-full object-contain group-hover:scale-110 transition-transform duration-500">
                                </a>

                                {{-- Wishlist Button --}}
                                <button type="button"
                                    onclick="event.stopPropagation(); toggleWishlist({{ $prod->id }}, this)"
                                    class="absolute top-2 right-2 p-1.5 rounded-full bg-white/90 backdrop-blur-sm text-gray-400 hover:text-[#e1276f] transition shadow-sm"
                                    title="Add to wishlist">
                                    <svg class="w-3.5 h-3.5 {{ $isWish ? 'text-[#e1276f] fill-current' : '' }}"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>
                            </div>

                            <div class="p-3.5 sm:p-4">
                                {{-- Title & Specs --}}
                                <h3
                                    class="font-bold text-xs sm:text-sm text-gray-900 line-clamp-1 group-hover:text-[#e1276f] transition-colors leading-snug">
                                    <a href="{{ route('products.show', $prod->slug) }}">{{ $prod->name }}</a>
                                </h3>
                                <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $sizeLabel }}</p>

                                {{-- Ratings --}}
                                <div class="flex items-center gap-1 mt-1.5">
                                    <div class="flex text-amber-400 text-xs">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <span>★</span>
                                        @endfor
                                    </div>
                                    <span class="text-[11px] text-gray-400 font-medium">({{ $reviewCount }})</span>
                                </div>

                                {{-- Price with Strikethrough --}}
                                <div class="flex items-baseline gap-2 mt-2">
                                    <span class="font-extrabold text-base sm:text-lg text-gray-900">
                                        {{ $currency }}{{ number_format($prod->current_price, 2) }}
                                    </span>
                                    @if ($prod->price > $prod->current_price)
                                        <span class="text-xs text-gray-400 line-through">
                                            {{ $currency }}{{ number_format($prod->price, 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Full-Width Vibrant Pink Add to Cart Button Matching Mockup --}}
                        <div class="p-3.5 pt-1">
                            <button type="button" onclick="addToCartAjax({{ $prod->id }}, 1, this)"
                                class="w-full bg-[#ff1475] hover:bg-[#e00062] active:scale-95 text-white font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-2 shadow-md shadow-pink-900/10 hover:shadow-pink-900/20 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Add to Cart</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            @else
                @foreach ($landingProducts as $prod)
                    <div
                        class="bg-white rounded-3xl border border-pink-100 flex flex-col justify-between hover:shadow-xl hover:shadow-pink-500/10 hover:-translate-y-1 transition-all duration-300 group">
                        <div>
                            {{-- Product Image Box --}}
                            <div
                                class="relative aspect-square rounded-2xl bg-[#fff8fa] overflow-hidden flex items-center justify-center border border-pink-50">
                                <a href="{{ route('products.show', $prod['slug']) }}"
                                    class="w-full h-full flex items-center justify-center">
                                    <img src="{{ $prod['image'] }}" alt="{{ $prod['name'] }}"
                                        class="max-h-full max-w-full object-contain group-hover:scale-110 transition-transform duration-500">
                                </a>

                                {{-- Wishlist Button --}}
                                <button type="button"
                                    onclick="event.stopPropagation(); toggleWishlist({{ $prod['id'] }}, this)"
                                    class="absolute top-2 right-2 p-1.5 rounded-full bg-white/90 backdrop-blur-sm text-gray-400 hover:text-[#e1276f] transition shadow-sm"
                                    title="Add to wishlist">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>
                            </div>

                            <div class="p-3.5 sm:p-4">
                                {{-- Title & Specs --}}
                                <h3
                                    class="font-bold text-xs sm:text-sm text-gray-900 line-clamp-1 group-hover:text-[#e1276f] transition-colors leading-snug">
                                    <a href="{{ route('products.show', $prod['slug']) }}">{{ $prod['name'] }}</a>
                                </h3>
                                <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $prod['size'] }}</p>

                                {{-- Ratings --}}
                                <div class="flex items-center gap-1 mt-1.5">
                                    <div class="flex text-amber-400 text-xs">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <span>★</span>
                                        @endfor
                                    </div>
                                    <span class="text-[11px] text-gray-400 font-medium">({{ $prod['reviews'] }})</span>
                                </div>

                                {{-- Price with Strikethrough --}}
                                <div class="flex items-baseline gap-2 mt-2">
                                    <span class="font-extrabold text-base sm:text-lg text-gray-900">
                                        {{ $currency }}{{ number_format($prod['price'], 2) }}
                                    </span>
                                    @if (!empty($prod['old_price']) && $prod['old_price'] > $prod['price'])
                                        <span class="text-xs text-gray-400 line-through">
                                            {{ $currency }}{{ number_format($prod['old_price'], 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Full-Width Vibrant Pink Add to Cart Button Matching Mockup --}}
                        <div class="p-3.5 pt-1">
                            <button type="button" onclick="addToCartAjax({{ $prod['id'] }}, 1, this)"
                                class="w-full bg-[#ff1475] hover:bg-[#e00062] active:scale-95 text-white font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-2 shadow-md shadow-pink-900/10 hover:shadow-pink-900/20 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Add to Cart</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
     6. ADVERTISEMENT BANNER 2 (In-between Ad like the image)
     ═══════════════════════════════════════════════════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="relative overflow-hidden rounded-3xl shadow-xl text-white p-6 sm:p-10 lg:p-12"
            style="background: linear-gradient(105deg, #fa7e8c 0%, #ee4680 30%, #e1276f 65%, #c2185b 100%);">

            {{-- Glowing floral SVG overlays --}}
            <div class="absolute inset-0 pointer-events-none select-none overflow-hidden">
                <svg class="absolute -right-10 top-0 w-80 h-full text-white/10" viewBox="0 0 400 400"
                    fill="currentColor">
                    <path d="M400,100 Q450,250 250,300 Q150,350 280,150 Z" />
                </svg>
                <div class="absolute -bottom-10 -left-10 w-64 h-64 rounded-full bg-white/10 blur-2xl"></div>
            </div>

            <div class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                {{-- Left: Beauty Product Imagery --}}
                <div class="md:col-span-5 flex justify-center md:justify-start">
                    <div
                        class="relative w-full max-w-70 sm:max-w-[320px] rounded-2xl overflow-hidden shadow-lg border-2 border-white/30 bg-white/10 backdrop-blur-sm p-1">
                        <img src="{{ asset('images/landing/promo-bottles.jpg') }}" alt="Flash Beauty Deals"
                            class="w-full h-auto object-cover rounded-xl select-none">
                    </div>
                </div>

                {{-- Right: Promotional Copy & CTA --}}
                <div class="md:col-span-7 text-center md:text-left space-y-3 sm:space-y-4">
                    <div
                        class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/25 text-white text-xs font-semibold">
                        <span>✦</span>
                        <span>Exclusive Seasonal Drop</span>
                    </div>
                    <h3 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight">
                        Flat 25% OFF
                    </h3>
                    <p class="text-pink-50 text-sm sm:text-base font-medium max-w-md mx-auto md:mx-0">
                        On All Organic Skincare, Hydrating Serums &amp; Luxury Lip Care
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('shop.index') }}?sale=1"
                            class="inline-flex items-center gap-2 bg-white text-[#e1276f] hover:text-[#c2185b] hover:bg-pink-50 active:scale-95 font-bold px-7 py-3.5 rounded-full shadow-lg hover:-translate-y-0.5 transition-all text-sm group">
                            <span>Explore Deals</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
     7. REST OF THE PRODUCTS (Latest 24 Products + View All Link)
     ═══════════════════════════════════════════════════════════════════ --}}
    <section id="products" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-18 border-t border-pink-100">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    Explore Our Collection
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Discover our latest collection of premium, dermatologist-approved beauty essentials.
                </p>
            </div>
            <a href="{{ route('shop.index') }}"
                class="text-xs sm:text-sm font-semibold text-[#e1276f] hover:text-[#c2185b] flex items-center gap-1.5 group shrink-0">
                <span>View All Products</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </a>
        </div>

        @if (isset($latestProducts) && $latestProducts->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach ($latestProducts as $prod)
                    @php
                        $avgRating = round($prod->approved_reviews_avg_rating ?? 5);
                        $reviewCount = $prod->approved_reviews_count ?: 48;
                        $isWish = auth()->check()
                            ? app(\App\Services\WishlistService::class)->isWishlisted($prod->id)
                            : false;
                    @endphp
                    <div
                        class="bg-white rounded-2xl border border-pink-100 flex flex-col justify-between hover:shadow-xl hover:shadow-pink-500/10 hover:-translate-y-1.5 transition-all duration-300 group">
                        <div>
                            {{-- Product Image Box --}}
                            <div
                                class="relative aspect-square rounded-t-2xl bg-[#fff8fa] overflow-hidden flex items-center justify-center border border-pink-50">
                                <a href="{{ route('products.show', $prod->slug) }}"
                                    class="w-full h-full flex items-center justify-center">
                                    <img src="{{ $prod->thumbnail_url }}" alt="{{ $prod->name }}"
                                        class="max-h-full max-w-full object-contain group-hover:scale-110 transition-transform duration-500">
                                </a>

                                {{-- Wishlist Button --}}
                                <button type="button"
                                    onclick="event.stopPropagation(); toggleWishlist({{ $prod->id }}, this)"
                                    class="absolute top-2 right-2 p-1.5 rounded-full bg-white/90 backdrop-blur-sm text-gray-400 hover:text-[#e1276f] transition shadow-sm"
                                    title="Add to wishlist">
                                    <svg class="w-4 h-4 {{ $isWish ? 'text-[#e1276f] fill-current' : '' }}"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>
                            </div>

                            <div class="p-4">
                                {{-- Category tag --}}
                                @if ($prod->category)
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-[#e1276f] block mb-1">
                                        {{ $prod->category->name }}
                                    </span>
                                @endif

                                {{-- Title --}}
                                <h3
                                    class="font-bold text-sm text-gray-900 line-clamp-1 group-hover:text-[#e1276f] transition-colors leading-snug">
                                    <a href="{{ route('products.show', $prod->slug) }}">{{ $prod->name }}</a>
                                </h3>

                                {{-- Volume / Weight tag --}}
                                <p class="text-xs text-gray-400 font-medium mt-0.5">
                                    {{ $prod->unit_volume ?: 'Standard Size' }}</p>

                                {{-- Rating stars --}}
                                <div class="flex items-center gap-1 mt-1.5">
                                    <div class="flex text-amber-400 text-xs">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <span>★</span>
                                        @endfor
                                    </div>
                                    <span class="text-[11px] text-gray-400 font-medium">({{ $reviewCount }})</span>
                                </div>

                                {{-- Price --}}
                                <div class="flex items-baseline gap-2 mt-2">
                                    <span class="font-extrabold text-base sm:text-lg text-gray-900">
                                        {{ $currency }}{{ number_format($prod->current_price, 2) }}
                                    </span>
                                    @if ($prod->price > $prod->current_price)
                                        <span class="text-xs text-gray-400 line-through">
                                            {{ $currency }}{{ number_format($prod->price, 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Full Width Add to Cart button --}}
                        <div class="p-4 pt-1">
                            <button type="button" onclick="addToCartAjax({{ $prod->id }}, 1, this)"
                                class="w-full bg-[#ff1475] hover:bg-[#e00062] active:scale-95 text-white font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-2 shadow-md shadow-pink-900/10 hover:shadow-pink-900/20 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Add to Cart</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Prominent "View All Products" button at the bottom of the collection --}}
            <div class="mt-12 text-center">
                <a href="{{ route('shop.index') }}"
                    class="inline-flex items-center gap-2.5 bg-white border-2 border-[#e1276f] text-[#e1276f] hover:bg-[#e1276f] hover:text-white active:scale-95 font-bold px-9 py-3.5 rounded-full shadow-md hover:shadow-lg transition-all text-sm group">
                    <span>View All Products</span>
                    <span class="group-hover:translate-x-1.5 transition-transform">&rarr;</span>
                </a>
            </div>
        @endif
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
     8. LATEST BLOGS SECTION (Latest 16 Posts)
     ═══════════════════════════════════════════════════════════════════ --}}
    @if (isset($latestPosts) && $latestPosts->isNotEmpty())
        <section id="blog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-18 border-t border-pink-100">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        Beauty Insights &amp; Tips
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Expert skincare guides, routine advice, and wellness rituals from our editorial team.
                    </p>
                </div>
                <a href="{{ route('blog.index') }}"
                    class="text-xs sm:text-sm font-semibold text-[#e1276f] hover:text-[#c2185b] flex items-center gap-1.5 group shrink-0">
                    <span>View All Articles</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                @foreach ($latestPosts as $post)
                    <article
                        class="group bg-white rounded-3xl overflow-hidden border border-pink-100 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <a href="{{ route('blog.show', $post->slug) }}"
                                class="block aspect-16/10 overflow-hidden bg-[#fff5f7]">
                                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            </a>
                            <div class="p-5">
                                @if ($post->category)
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#e1276f] block mb-1">
                                        {{ $post->category->name }}
                                    </span>
                                @endif
                                <h3
                                    class="font-bold text-sm sm:text-base text-gray-900 line-clamp-2 group-hover:text-[#e1276f] transition-colors leading-snug">
                                    <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                                </h3>
                                <p class="text-gray-500 text-xs mt-2 line-clamp-2 leading-relaxed">
                                    {{ $post->excerpt }}
                                </p>
                            </div>
                        </div>

                        <div class="p-5 pt-0 mt-auto">
                            <div
                                class="pt-3 border-t border-pink-50 flex items-center justify-between text-[11px] text-gray-400">
                                <span>{{ $post->published_at?->format('M j, Y') }}</span>
                                <a href="{{ route('blog.show', $post->slug) }}"
                                    class="text-[#e1276f] font-semibold hover:underline">
                                    Read Article &rarr;
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Bottom View All Articles link --}}
            <div class="mt-12 text-center">
                <a href="{{ route('blog.index') }}"
                    class="inline-flex items-center gap-2.5 bg-white border-2 border-[#e1276f] text-[#e1276f] hover:bg-[#e1276f] hover:text-white active:scale-95 font-bold px-9 py-3.5 rounded-full shadow-md hover:shadow-lg transition-all text-sm group">
                    <span>View All Articles</span>
                    <span class="group-hover:translate-x-1.5 transition-transform">&rarr;</span>
                </a>
            </div>
        </section>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
     9. CONTACT FORM SECTION
     ═══════════════════════════════════════════════════════════════════ --}}
    <section id="contact-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 border-t border-pink-100">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <div
                class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-pink-50 text-[#e1276f] text-xs font-semibold border border-pink-100 mb-3">
                <span>✦</span>
                <span>Get In Touch</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                We'd Love to Hear From You
            </h2>
            <p class="text-sm sm:text-base text-gray-500 mt-2">
                Have questions about product recommendations, orders, or skincare regimens? Send our beauty advisors a
                message.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start" x-data="{
            step: 'form', // 'form', 'otp', 'success'
            loading: false,
            errorMessage: '',
            successMessage: '',
            timer: 60,
            timerInterval: null,
            canResend: false,
            form: {
                name: '{{ old('name', auth()->user()?->name ?? '') }}',
                email: '{{ old('email', auth()->user()?->email ?? '') }}',
                subject: '',
                message: '',
                otp: ''
            },
            startTimer() {
                this.timer = 60;
                this.canResend = false;
                if (this.timerInterval) clearInterval(this.timerInterval);
                this.timerInterval = setInterval(() => {
                    if (this.timer > 0) {
                        this.timer--;
                    } else {
                        this.canResend = true;
                        clearInterval(this.timerInterval);
                    }
                }, 1000);
            },
            async requestOtp() {
                this.errorMessage = '';
                if (!this.form.name.trim() || !this.form.email.trim() || !this.form.subject.trim() || !this.form.message.trim()) {
                    this.errorMessage = 'Please complete all required fields.';
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch('{{ route('contact.send-otp') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: this.form.name,
                            email: this.form.email,
                            subject: this.form.subject,
                            message: this.form.message
                        })
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.step = 'otp';
                        this.startTimer();
                    } else {
                        this.errorMessage = data.message || 'Failed to send verification code. Please check your inputs.';
                    }
                } catch (e) {
                    this.errorMessage = 'An unexpected network error occurred. Please try again.';
                } finally {
                    this.loading = false;
                }
            },
            async resendOtp() {
                if (!this.canResend) return;
                this.errorMessage = '';
                this.loading = true;
                try {
                    const res = await fetch('{{ route('contact.resend-otp') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.startTimer();
                    } else {
                        this.errorMessage = data.message || 'Failed to resend code.';
                    }
                } catch (e) {
                    this.errorMessage = 'Failed to resend verification code.';
                } finally {
                    this.loading = false;
                }
            },
            async submitInquiry() {
                this.errorMessage = '';
                if (!this.form.otp || this.form.otp.trim().length !== 6) {
                    this.errorMessage = 'Please enter the complete 6-digit code.';
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch('{{ route('contact.submit') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            otp: this.form.otp
                        })
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.step = 'success';
                        this.successMessage = data.message || 'Thank you! Your inquiry has been sent.';
                    } else {
                        this.errorMessage = data.message || 'Invalid verification code.';
                    }
                } catch (e) {
                    this.errorMessage = 'Verification failed. Please try again.';
                } finally {
                    this.loading = false;
                }
            }
        }">

            {{-- Left: Contact Info Cards --}}
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white rounded-3xl border border-pink-100 p-6 sm:p-8 shadow-sm space-y-6">
                    <h3 class="font-extrabold text-lg text-gray-900 border-b border-pink-50 pb-4">
                        Contact Information
                    </h3>

                    {{-- Email Support --}}
                    <div class="flex items-start gap-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-pink-50 text-[#e1276f] flex items-center justify-center shrink-0 border border-pink-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900">Email Inquiries</h4>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $settings['contact_email'] ?? 'hello@glowcare.com' }}</p>
                            <p class="text-[11px] text-[#e1276f] font-medium mt-1">Typical reply within 2 hours</p>
                        </div>
                    </div>

                    {{-- Phone / WhatsApp --}}
                    <div class="flex items-start gap-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-pink-50 text-[#e1276f] flex items-center justify-center shrink-0 border border-pink-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900">Call &amp; WhatsApp Support</h4>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $settings['contact_phone'] ?? '+880 1700-000000' }}
                            </p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Mon - Sat: 9:00 AM - 9:00 PM</p>
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="flex items-start gap-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-pink-50 text-[#e1276f] flex items-center justify-center shrink-0 border border-pink-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900">Flagship Studio</h4>
                            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                {{ $settings['contact_address'] ?? 'Gulshan-2, Dhaka, Bangladesh' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Trust Promise Card --}}
                <div class="rounded-3xl p-6 text-white shadow-lg"
                    style="background: linear-gradient(105deg, #fa7e8c 0%, #ee4680 30%, #e1276f 100%);">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">✨</span>
                        <div>
                            <h4 class="font-extrabold text-sm sm:text-base">Guaranteed Authentic Beauty</h4>
                            <p class="text-xs text-pink-50/90 mt-0.5 leading-relaxed">
                                Every formula is dermatologically verified, 100% genuine, and stored in climate-controlled
                                facilities.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Interactive Contact Form --}}
            <div
                class="lg:col-span-7 bg-white rounded-3xl border border-pink-100 p-6 sm:p-10 shadow-sm relative overflow-hidden">
                {{-- Error banner --}}
                <div x-show="errorMessage" x-cloak x-transition
                    class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <span x-text="errorMessage"></span>
                    </div>
                    <button type="button" @click="errorMessage = ''"
                        class="text-red-400 hover:text-red-600 font-bold ml-2">&times;</button>
                </div>

                {{-- STEP 1: FORM INPUTS --}}
                <div x-show="step === 'form'" x-transition>
                    <form @submit.prevent="requestOtp()" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            {{-- Name --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Your Name <span
                                        class="text-[#e1276f]">*</span></label>
                                <input type="text" x-model="form.name" required placeholder="Jane Doe"
                                    class="w-full bg-[#fff8fa] border border-pink-100 rounded-xl px-4 py-3 text-xs sm:text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-300 transition">
                            </div>

                            {{-- Email --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Email Address <span
                                        class="text-[#e1276f]">*</span></label>
                                <input type="email" x-model="form.email" required placeholder="jane@example.com"
                                    class="w-full bg-[#fff8fa] border border-pink-100 rounded-xl px-4 py-3 text-xs sm:text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-300 transition">
                            </div>
                        </div>

                        {{-- Subject --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Subject <span
                                    class="text-[#e1276f]">*</span></label>
                            <input type="text" x-model="form.subject" required
                                placeholder="Question about my skin type or order"
                                class="w-full bg-[#fff8fa] border border-pink-100 rounded-xl px-4 py-3 text-xs sm:text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-300 transition">
                        </div>

                        {{-- Message --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Your Message <span
                                    class="text-[#e1276f]">*</span></label>
                            <textarea x-model="form.message" rows="4" required placeholder="How can our beauty consultants help you today?"
                                class="w-full bg-[#fff8fa] border border-pink-100 rounded-xl p-4 text-xs sm:text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-300 transition"></textarea>
                        </div>

                        {{-- Submit Button --}}
                        <div>
                            <button type="submit" :disabled="loading"
                                class="w-full bg-[#ff1475] hover:bg-[#e00062] active:scale-95 disabled:opacity-60 text-white font-bold py-3.5 px-6 rounded-full text-sm flex items-center justify-center gap-2 shadow-lg shadow-pink-900/10 hover:shadow-pink-900/20 transition-all">
                                <span x-show="!loading">Send Message &rarr;</span>
                                <span x-show="loading" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Sending Code...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- STEP 2: OTP VERIFICATION --}}
                <div x-show="step === 'otp'" x-cloak x-transition class="space-y-6 text-center py-4">
                    <div
                        class="w-14 h-14 rounded-full bg-pink-50 text-[#e1276f] flex items-center justify-center mx-auto border border-pink-100">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <div>
                        <h4 class="font-extrabold text-xl text-gray-900">Verify Your Email</h4>
                        <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                            We sent a 6-digit verification code to <span class="font-semibold text-gray-800"
                                x-text="form.email"></span>.
                        </p>
                    </div>

                    <div class="max-w-xs mx-auto">
                        <input type="text" x-model="form.otp" maxlength="6" placeholder="123456"
                            class="w-full text-center tracking-[0.5em] font-mono text-xl sm:text-2xl font-bold bg-[#fff8fa] border border-pink-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-pink-300 transition">
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <button type="button" @click="submitInquiry()" :disabled="loading"
                            class="w-full sm:w-auto bg-[#ff1475] hover:bg-[#e00062] active:scale-95 disabled:opacity-60 text-white font-bold py-3 px-8 rounded-full text-sm shadow-md transition-all">
                            <span x-show="!loading">Verify &amp; Submit</span>
                            <span x-show="loading">Verifying...</span>
                        </button>
                        <button type="button" @click="resendOtp()" :disabled="!canResend || loading"
                            class="text-xs font-semibold text-[#e1276f] hover:underline disabled:opacity-50">
                            <span x-show="!canResend" x-text="'Resend code in ' + timer + 's'"></span>
                            <span x-show="canResend">Resend Code</span>
                        </button>
                    </div>
                </div>

                {{-- STEP 3: SUCCESS CONFIRMATION --}}
                <div x-show="step === 'success'" x-cloak x-transition class="text-center py-8 space-y-4">
                    <div
                        class="w-16 h-16 rounded-full bg-green-50 text-green-600 flex items-center justify-center mx-auto border border-green-200 shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h4 class="font-extrabold text-2xl text-gray-900">Message Received!</h4>
                    <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto leading-relaxed" x-text="successMessage">
                    </p>
                    <div class="pt-4">
                        <button type="button"
                            @click="step = 'form'; form.subject = ''; form.message = ''; form.otp = '';"
                            class="inline-flex items-center gap-2 bg-pink-50 text-[#e1276f] hover:bg-pink-100 font-bold px-6 py-2.5 rounded-full text-xs transition">
                            <span>Send Another Message</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════
     10. BOTTOM TRUST BADGES / FEATURES BAR (Matching Screenshot Bottom)
     ═══════════════════════════════════════════════════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div
            class="bg-[#fff2f6] rounded-2xl sm:rounded-3xl border border-pink-100 p-6 sm:p-8 grid grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8 shadow-sm">
            {{-- Item 1: Secure Payments --}}
            <div class="flex items-center gap-3.5">
                <div
                    class="w-11 h-11 rounded-2xl bg-white text-[#e1276f] flex items-center justify-center shrink-0 shadow-sm border border-pink-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-gray-900 leading-tight">Secure Payments</h4>
                    <p class="text-xs text-gray-500 mt-0.5">100% safe &amp; secure</p>
                </div>
            </div>

            {{-- Item 2: Exclusive Offers --}}
            <div class="flex items-center gap-3.5">
                <div
                    class="w-11 h-11 rounded-2xl bg-white text-[#e1276f] flex items-center justify-center shrink-0 shadow-sm border border-pink-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-gray-900 leading-tight">Exclusive Offers</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Save more on top brands</p>
                </div>
            </div>

            {{-- Item 3: 24/7 Support --}}
            <div class="flex items-center gap-3.5">
                <div
                    class="w-11 h-11 rounded-2xl bg-white text-[#e1276f] flex items-center justify-center shrink-0 shadow-sm border border-pink-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-gray-900 leading-tight">24/7 Support</h4>
                    <p class="text-xs text-gray-500 mt-0.5">We're here to help</p>
                </div>
            </div>

            {{-- Item 4: Loyalty Rewards --}}
            <div class="flex items-center gap-3.5">
                <div
                    class="w-11 h-11 rounded-2xl bg-white text-[#e1276f] flex items-center justify-center shrink-0 shadow-sm border border-pink-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-gray-900 leading-tight">Loyalty Rewards</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Earn points &amp; get rewards</p>
                </div>
            </div>
        </div>
    </section>

@endsection
