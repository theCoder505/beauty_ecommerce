@extends('users.layouts.app')
@php $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); @endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar Filters --}}
        <aside class="lg:w-64 shrink-0" x-data="{ filtersOpen: false }">
            <button @click="filtersOpen = !filtersOpen" class="lg:hidden w-full flex items-center justify-between bg-white dark:bg-gray-800 border border-cream-200 dark:border-gray-700 rounded-xl px-4 py-3 mb-4 font-semibold text-sm">
                Filters <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            </button>
            <div x-show="filtersOpen || window.innerWidth >= 1024" class="space-y-6">
                <form action="{{ route('shop.index') }}" method="GET" id="filter-form">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">

                    {{-- Categories --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-cream-200 dark:border-gray-700">
                        <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-4">Categories</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('shop.index') }}" class="text-sm {{ !request('category') ? 'text-brand-700 dark:text-brand-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-brand-700' }} transition">
                                    All Products
                                </a>
                            </li>
                            @foreach($categories as $cat)
                            <li>
                                <a href="{{ route('shop.category', $cat->slug) }}" class="text-sm {{ request()->routeIs('shop.category') && request()->route('category') && request()->route('category')->id === $cat->id ? 'text-brand-700 dark:text-brand-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-brand-700 dark:hover:text-brand-400' }} transition flex justify-between">
                                    {{ $cat->name }} <span class="text-xs text-gray-400">({{ $cat->products_count }})</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Price Filter --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-cream-200 dark:border-gray-700">
                        <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-4">Price Range</h3>
                        <div class="flex gap-2">
                            <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}"
                                class="w-full px-3 py-2 text-sm border border-cream-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 focus:outline-none focus:ring-1 focus:ring-brand-600">
                            <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}"
                                class="w-full px-3 py-2 text-sm border border-cream-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 focus:outline-none focus:ring-1 focus:ring-brand-600">
                        </div>
                        <button type="submit" class="mt-3 w-full bg-brand-700 text-white text-sm font-semibold py-2 rounded-lg hover:bg-brand-800 transition">Apply</button>
                    </div>

                    {{-- Rating Filter --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-cream-200 dark:border-gray-700">
                        <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-4">Rating</h3>
                        @foreach([4,3,2,1] as $stars)
                        <label class="flex items-center gap-2 cursor-pointer mb-2">
                            <input type="radio" name="rating" value="{{ $stars }}" {{ request('rating') == $stars ? 'checked' : '' }} onchange="document.getElementById('filter-form').submit()" class="text-brand-700">
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)<svg class="w-3.5 h-3.5 {{ $i <= $stars ? 'text-amber-400' : 'text-gray-200' }} fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                                <span class="text-xs text-gray-500 ml-1">& up</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 min-w-0">
            {{-- Top bar --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white">
                        {{ isset($currentCategory) ? $currentCategory->name : 'All Products' }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $products->total() }} products found</p>
                </div>
                <form action="{{ request()->url() }}" method="GET" class="flex items-center gap-2">
                    @foreach(request()->except('sort') as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <select name="sort" onchange="this.form.submit()"
                        class="text-sm border border-cream-200 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-brand-600">
                        <option value="">Sort: Newest</option>
                        <option value="price_asc"  {{ request('sort') === 'price_asc'  ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="popular"    {{ request('sort') === 'popular'    ? 'selected' : '' }}>Most Popular</option>
                        <option value="rating"     {{ request('sort') === 'rating'     ? 'selected' : '' }}>Top Rated</option>
                    </select>
                </form>
            </div>

            {{-- Search --}}
            @if(request('search'))
            <div class="mb-4 flex items-center gap-2">
                <span class="text-sm text-gray-500">Results for:</span>
                <span class="bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 text-sm font-semibold px-3 py-1 rounded-full">"{{ request('search') }}"</span>
                <a href="{{ route('shop.index') }}" class="text-xs text-gray-400 hover:text-red-500">✕ Clear</a>
            </div>
            @endif

            {{-- Products Grid --}}
            @if($products->isEmpty())
            <div class="text-center py-24">
                <svg class="w-16 h-16 text-gray-200 dark:text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-gray-500 font-semibold">No products found</p>
                <a href="{{ route('shop.index') }}" class="mt-4 inline-block text-brand-700 hover:underline text-sm">Clear all filters</a>
            </div>
            @else
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($products as $product)
                @include('users.partials.product-card', ['product' => $product, 'currency' => $currency])
                @endforeach
            </div>
            <div class="mt-10">{{ $products->withQueryString()->links() }}</div>
            @endif
        </main>
    </div>
</div>
@endsection

