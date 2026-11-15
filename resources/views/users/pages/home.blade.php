@extends('users.layouts.app')

@php
    $settings = app(\App\Services\SettingService::class)->all();
    $currency = $settings['currency_symbol'] ?? '৳';
@endphp

@section('content')

{{-- ═══ HERO ═══ --}}
<section class="relative overflow-hidden bg-cream-100 dark:bg-gray-900 min-h-[88vh] flex items-center">
    {{-- Background decorative shape --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-20 -right-20 w-96 h-96 rounded-full bg-brand-100/40 dark:bg-brand-900/20 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-96 h-96 rounded-full bg-cream-300/60 dark:bg-gray-800/40 blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 py-20 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        {{-- Left content --}}
        <div class="space-y-6">
            <p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand-700 dark:text-brand-400 flex items-center gap-3">
                <span class="w-8 h-px bg-brand-700 dark:bg-brand-400"></span> Luxury Beauty House
            </p>
            <h1 class="font-serif text-5xl md:text-7xl font-bold leading-[1.05] text-gray-900 dark:text-white">
                Made to<br><em class="italic text-brand-700 dark:text-brand-400">Stick in</em><br>Your Memory!
            </h1>
            <p class="text-gray-600 dark:text-gray-400 text-lg leading-relaxed max-w-md">
                Discover refined fragrances and cosmetics made for unforgettable moments. Each product is blended with timeless character and quiet luxury.
            </p>
            <div class="flex flex-wrap gap-4 pt-2">
                <a href="{{ route('shop.index') }}" class="bg-brand-700 text-white font-semibold px-8 py-3.5 rounded-full hover:bg-brand-800 transition shadow-lg shadow-brand-700/25">
                    Shop Now
                </a>
                <a href="{{ route('shop.index') }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 font-semibold px-6 py-3.5 rounded-full border border-gray-300 dark:border-gray-600 hover:border-brand-700 dark:hover:border-brand-500 hover:text-brand-700 dark:hover:text-brand-400 transition">
                    Explore Collections <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
            {{-- Stats --}}
            <div class="flex flex-wrap gap-8 pt-4 border-t border-cream-200 dark:border-gray-700">
                <div><p class="font-serif text-3xl font-bold text-gray-900 dark:text-white">50<span class="text-brand-700">+</span></p><p class="text-xs text-gray-500 uppercase tracking-wide mt-1">Unique Scents</p></div>
                <div><p class="font-serif text-3xl font-bold text-gray-900 dark:text-white">25k<span class="text-brand-700">+</span></p><p class="text-xs text-gray-500 uppercase tracking-wide mt-1">Happy Customers</p></div>
                <div><p class="font-serif text-3xl font-bold text-gray-900 dark:text-white">98<span class="text-brand-700">%</span></p><p class="text-xs text-gray-500 uppercase tracking-wide mt-1">Satisfaction Rate</p></div>
            </div>
        </div>

        {{-- Right: featured product cards --}}
        <div class="relative h-[480px] hidden lg:block">
            @foreach($featuredProducts->take(3) as $i => $p)
            @php
                $positions = [
                    'absolute top-0 right-0 w-56 rotate-2',
                    'absolute top-20 left-0 w-52 -rotate-3',
                    'absolute bottom-0 right-16 w-48 rotate-1',
                ];
            @endphp
            <div class="{{ $positions[$i] ?? 'absolute' }} group cursor-pointer">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition duration-300">
                    <img src="{{ $p->thumbnail_url }}" alt="{{ $p->name }}" class="w-full h-40 object-cover group-hover:scale-105 transition duration-500">
                    <div class="p-3">
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $p->name }}</p>
                        <p class="text-brand-700 font-bold text-sm mt-1">{{ $currency }}{{ number_format($p->current_price) }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ SCENT/CATEGORY TICKER ═══ --}}
<section class="bg-brand-700 text-white py-3 overflow-hidden">
    <div class="ticker-track flex items-center gap-8 whitespace-nowrap" style="width: max-content;">
        @foreach(array_merge(['SKINCARE','✦','MAKEUP','✦','FRAGRANCE','✦','BODY CARE','✦','HAIR CARE','✦','SUN CARE','✦','NAIL CARE','✦','NEW ARRIVALS','✦'], ['SKINCARE','✦','MAKEUP','✦','FRAGRANCE','✦','BODY CARE','✦','HAIR CARE','✦','SUN CARE','✦','NAIL CARE','✦','NEW ARRIVALS','✦']) as $item)
        <span class="text-xs font-semibold tracking-[0.2em] {{ $item === '✦' ? 'text-brand-300' : '' }}">{{ $item }}</span>
        @endforeach
    </div>
</section>

{{-- ═══ ABOUT ═══ --}}
<section class="max-w-7xl mx-auto px-4 py-24 grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
    <div class="relative">
        <div class="aspect-[4/5] rounded-3xl overflow-hidden bg-cream-200 dark:bg-gray-800 shadow-2xl">
            <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=600&q=80" alt="About GlowHaus" class="w-full h-full object-cover">
        </div>
        <div class="absolute -bottom-6 -right-6 bg-brand-700 text-white rounded-2xl p-5 shadow-xl hidden md:block">
            <p class="font-serif text-2xl font-bold">12</p>
            <p class="text-xs text-brand-200 mt-1">Luxury Collections</p>
        </div>
    </div>
    <div class="space-y-6">
        <p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand-700 dark:text-brand-400 flex items-center gap-3">
            <span class="w-8 h-px bg-brand-700 dark:bg-brand-400"></span> About Us
        </p>
        <h2 class="font-serif text-4xl md:text-5xl font-bold leading-[1.1] text-gray-900 dark:text-white">
            More than a product,<br>it is a <em class="italic text-brand-700 dark:text-brand-400">personal signature</em>
        </h2>
        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
            GlowHaus creates beauty that becomes part of your story. From soft morning rituals to deep evening routines, every product is designed to feel elegant, intimate, and memorable.
        </p>
        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
            Made with carefully selected ingredients, our formulations balance modern luxury with timeless craftsmanship.
        </p>
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 text-brand-700 dark:text-brand-400 font-semibold hover:gap-3 transition-all">
            Discover Our Story <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>

{{-- ═══ FEATURED CATEGORIES ═══ --}}
<section class="bg-cream-100 dark:bg-gray-900 py-24">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand-700 dark:text-brand-400 mb-3">Our Collections</p>
            <h2 class="font-serif text-4xl md:text-5xl font-bold text-gray-900 dark:text-white">
                Discover our <em class="italic text-brand-700 dark:text-brand-400">collections</em>
            </h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($featuredCategories as $cat)
            <a href="{{ route('shop.category', $cat->slug) }}" class="group text-center">
                <div class="aspect-square rounded-2xl overflow-hidden bg-cream-200 dark:bg-gray-800 mb-3 shadow-sm group-hover:shadow-lg transition">
                    <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                </div>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 group-hover:text-brand-700 dark:group-hover:text-brand-400 transition">{{ $cat->name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $cat->products_count }} items</p>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ FEATURED PRODUCTS (Elegant fragrances section) ═══ --}}
<section class="max-w-7xl mx-auto px-4 py-24">
    <div class="text-center mb-12">
        <p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand-700 dark:text-brand-400 mb-3">Shop the Goods</p>
        <h2 class="font-serif text-4xl md:text-5xl font-bold text-gray-900 dark:text-white">
            Elegant fragrances with <em class="italic text-brand-700 dark:text-brand-400">timeless essence</em>
        </h2>
        <p class="mt-4 text-gray-500 dark:text-gray-400 max-w-xl mx-auto text-sm">Our signature products are crafted to stay with you through every moment, leaving a soft trail of confidence and sophistication.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($featuredProducts as $product)
        @include('users.partials.product-card', ['product' => $product, 'currency' => $currency])
        @endforeach
    </div>

    <div class="text-center mt-10">
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 border border-brand-700 text-brand-700 dark:text-brand-400 dark:border-brand-500 font-semibold px-8 py-3 rounded-full hover:bg-brand-700 hover:text-white dark:hover:bg-brand-700 dark:hover:text-white transition">
            View All Collections <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>

{{-- ═══ BEST SELLERS ═══ --}}
<section class="bg-cream-100 dark:bg-gray-900 py-24">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand-700 dark:text-brand-400 mb-3">Best Sellers</p>
            <h2 class="font-serif text-4xl md:text-5xl font-bold text-gray-900 dark:text-white">
                Bestselling products, <em class="italic text-brand-700 dark:text-brand-400">pure refinement</em>
            </h2>
            <p class="mt-4 text-gray-500 dark:text-gray-400 max-w-xl mx-auto text-sm">Loved by our customers for their elegance and unforgettable scent profiles.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($bestSellers->take(8) as $product)
            @include('users.partials.product-card', ['product' => $product, 'currency' => $currency])
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('shop.index') }}?sort=popular" class="bg-brand-700 text-white font-semibold px-8 py-3 rounded-full hover:bg-brand-800 transition shadow-lg shadow-brand-700/25">
                Shop Now →
            </a>
        </div>
    </div>
</section>

{{-- ═══ TESTIMONIALS ═══ --}}
<section class="max-w-7xl mx-auto px-4 py-24">
    <div class="text-center mb-12">
        <p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand-700 dark:text-brand-400 mb-3">Reviews</p>
        <h2 class="font-serif text-4xl md:text-5xl font-bold text-gray-900 dark:text-white">
            Honest words, <em class="italic text-brand-700 dark:text-brand-400">meaningful impressions</em>
        </h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach([
            ['name' => 'Ayesha Rahman',  'role' => 'Business Owner',  'text' => 'GlowHaus products have completely transformed my skincare routine. The quality is unmatched and the packaging is absolutely beautiful. Highly recommend!', 'rating' => 5],
            ['name' => 'Emma Wilson',    'role' => 'Beauty Blogger',   'text' => 'The AI planner was a great starting point. It showed us what kind of system we needed and in those most event it potentially saved me a lot of time.', 'rating' => 5],
            ['name' => 'Sarah Ahmed',   'role' => 'Makeup Artist',    'text' => 'As a professional makeup artist, I am very particular about the products I use. GlowHaus never disappoints. The foundation range is extraordinary.', 'rating' => 5],
        ] as $review)
        <div class="bg-cream-50 dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-cream-200 dark:border-gray-700">
            <div class="flex items-center gap-1 mb-4">
                @for($i = 0; $i < $review['rating']; $i++)<svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
            </div>
            <svg class="w-6 h-6 text-brand-700 dark:text-brand-500 mb-3" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
            <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">{{ $review['text'] }}</p>
            <div class="flex items-center gap-3 mt-5">
                <div class="w-10 h-10 rounded-full bg-brand-700 flex items-center justify-center text-white font-bold text-sm">{{ substr($review['name'],0,1) }}</div>
                <div>
                    <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ $review['name'] }}</p>
                    <p class="text-xs text-gray-500">{{ $review['role'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ═══ BLOG HIGHLIGHTS ═══ --}}
@if($latestPosts->isNotEmpty())
<section class="bg-cream-100 dark:bg-gray-900 py-24">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row items-start md:items-end justify-between mb-12 gap-4">
            <div>
                <p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand-700 dark:text-brand-400 mb-3">Beauty Blog</p>
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-gray-900 dark:text-white">
                    Beauty tips & <em class="italic text-brand-700 dark:text-brand-400">insights</em>
                </h2>
            </div>
            <a href="{{ route('blog.index') }}" class="text-brand-700 dark:text-brand-400 font-semibold hover:underline text-sm flex items-center gap-1">View all posts →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($latestPosts as $post)
            <article class="group">
                <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/9] rounded-2xl overflow-hidden bg-cream-200 dark:bg-gray-800 mb-4">
                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                </a>
                @if($post->category)
                <span class="text-xs font-semibold uppercase tracking-widest text-brand-700 dark:text-brand-400">{{ $post->category->name }}</span>
                @endif
                <h3 class="font-serif text-xl font-bold text-gray-900 dark:text-white mt-2 leading-tight group-hover:text-brand-700 dark:group-hover:text-brand-400 transition">
                    <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                </h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-2 leading-relaxed line-clamp-2">{{ $post->excerpt }}</p>
                <p class="text-xs text-gray-400 mt-3">{{ $post->published_at?->format('M j, Y') }}</p>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection

