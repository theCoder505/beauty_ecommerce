@extends('users.layouts.app')
@php 
    $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); 
    $activeCatId = isset($category) ? $category->id : request('category');
    $currentMin = request('price_min', $minPossiblePrice);
    $currentMax = request('price_max', $maxPossiblePrice);
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10" id="shop-page-wrapper">
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar Filters --}}
        <aside class="lg:w-64 shrink-0" x-data="{ filtersOpen: false }">
            <button @click="filtersOpen = !filtersOpen" class="lg:hidden w-full flex items-center justify-between bg-white dark:bg-gray-800 border border-cream-200 dark:border-gray-700 rounded-xl px-4 py-3 mb-4 font-semibold text-sm">
                Filters <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            </button>
            <div x-show="filtersOpen || window.innerWidth >= 1024" class="space-y-6">
                <form action="{{ route('shop.index') }}" method="GET" id="filter-form" onsubmit="event.preventDefault(); applyShopFilters();">
                    <input type="hidden" name="category" id="filter-category" value="{{ $activeCatId }}">
                    <input type="hidden" name="sort" id="filter-sort" value="{{ request('sort', '') }}">
                    <input type="hidden" name="search" id="filter-search" value="{{ request('search', '') }}">

                    {{-- Categories --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-cream-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-sm text-gray-900 dark:text-white">Categories</h3>
                            <button type="button" onclick="selectCategory(null)" class="text-xs text-gray-400 hover:text-brand-700 dark:hover:text-brand-400">Clear</button>
                        </div>
                        <ul class="space-y-2" id="category-filter-list">
                            <li>
                                <button type="button" onclick="selectCategory(null)" 
                                    class="category-filter-btn text-left w-full text-sm transition {{ empty($activeCatId) ? 'text-brand-700 dark:text-brand-400 font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-brand-700 dark:hover:text-brand-400' }}"
                                    data-category-id="">
                                    All Products
                                </button>
                            </li>
                            @foreach($categories as $cat)
                            <li>
                                <button type="button" onclick="selectCategory({{ $cat->id }})" 
                                    class="category-filter-btn text-left w-full text-sm transition flex justify-between items-center {{ (string)$activeCatId === (string)$cat->id ? 'text-brand-700 dark:text-brand-400 font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-brand-700 dark:hover:text-brand-400' }}"
                                    data-category-id="{{ $cat->id }}">
                                    <span>{{ $cat->name }}</span>
                                    <span class="text-xs text-gray-400">({{ $cat->products_count }})</span>
                                </button>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Double-Sided Price Range Filter (Point 1) --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-cream-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-sm text-gray-900 dark:text-white">Price Range</h3>
                            <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                                {{ $currency }}<span id="price-min-display">{{ $currentMin }}</span> – {{ $currency }}<span id="price-max-display">{{ $currentMax }}</span>
                            </span>
                        </div>
                        
                        {{-- Dual thumb range slider --}}
                        <div class="range-slider-wrapper my-3">
                            <div class="range-slider-track"></div>
                            <div id="slider-track-highlight" class="range-slider-highlight"></div>
                            <input type="range" id="price-slider-min" 
                                   min="{{ $minPossiblePrice }}" max="{{ $maxPossiblePrice }}" 
                                   value="{{ $currentMin }}" step="1" 
                                   aria-label="Minimum price">
                            <input type="range" id="price-slider-max" 
                                   min="{{ $minPossiblePrice }}" max="{{ $maxPossiblePrice }}" 
                                   value="{{ $currentMax }}" step="1" 
                                   aria-label="Maximum price">
                        </div>

                        {{-- Direct Numeric Inputs --}}
                        <div class="flex items-center gap-2 pt-1">
                            <div class="flex-1">
                                <label for="price-input-min" class="text-[11px] text-gray-400 block mb-1">Min ({{ $currency }})</label>
                                <input type="number" id="price-input-min" name="price_min" value="{{ $currentMin }}" min="{{ $minPossiblePrice }}" max="{{ $maxPossiblePrice }}"
                                    class="w-full px-2.5 py-1.5 text-xs border border-cream-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-brand-600">
                            </div>
                            <span class="text-gray-400 mt-4">–</span>
                            <div class="flex-1">
                                <label for="price-input-max" class="text-[11px] text-gray-400 block mb-1">Max ({{ $currency }})</label>
                                <input type="number" id="price-input-max" name="price_max" value="{{ $currentMax }}" min="{{ $minPossiblePrice }}" max="{{ $maxPossiblePrice }}"
                                    class="w-full px-2.5 py-1.5 text-xs border border-cream-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-brand-600">
                            </div>
                        </div>

                        <button type="button" onclick="applyShopFilters()" class="mt-4 w-full bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold py-2 rounded-lg transition shadow-sm">
                            Apply Price
                        </button>
                    </div>

                    {{-- Rating Filter --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-cream-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-sm text-gray-900 dark:text-white">Rating</h3>
                            <button type="button" onclick="clearRatingFilter()" class="text-xs text-gray-400 hover:text-brand-700 dark:hover:text-brand-400">All</button>
                        </div>
                        @foreach([4,3,2,1] as $stars)
                        <label class="flex items-center gap-2 cursor-pointer mb-2.5">
                            <input type="radio" name="rating" value="{{ $stars }}" {{ (string)request('rating') === (string)$stars ? 'checked' : '' }} onchange="applyShopFilters()" class="text-brand-700">
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= $stars ? 'text-amber-400' : 'text-gray-200 dark:text-gray-700' }} fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                                <span class="text-xs text-gray-500 ml-1">& up</span>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    {{-- Reset all button --}}
                    <button type="button" onclick="resetShopFilters()" class="w-full text-center text-xs font-semibold text-gray-500 hover:text-red-500 transition py-2">
                        Reset All Filters
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content (Point 5: AJAX updated) --}}
        <main class="flex-1 min-w-0 relative">
            {{-- Top bar --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 id="shop-heading-title" class="font-serif text-2xl font-bold text-gray-900 dark:text-white">
                        {{ isset($category) ? $category->name : 'All Products' }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-1"><span id="shop-total-count">{{ $products->total() }}</span> products found</p>
                </div>
                <div class="flex items-center gap-2">
                    <label for="shop-sort-select" class="sr-only">Sort products</label>
                    <select id="shop-sort-select" onchange="onSortChange(this.value)"
                        class="text-sm border border-cream-200 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-brand-600">
                        <option value="">Sort: Newest</option>
                        <option value="price_asc"  {{ request('sort') === 'price_asc'  ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="popular"    {{ request('sort') === 'popular'    ? 'selected' : '' }}>Most Popular</option>
                        <option value="rating"     {{ request('sort') === 'rating'     ? 'selected' : '' }}>Top Rated</option>
                    </select>
                </div>
            </div>

            {{-- Active Search Badge --}}
            <div id="search-active-badge" class="{{ request('search') ? 'mb-4 flex items-center gap-2' : 'hidden' }}">
                <span class="text-sm text-gray-500">Results for:</span>
                <span id="search-query-text" class="bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 text-sm font-semibold px-3 py-1 rounded-full">"{{ request('search') }}"</span>
                <button type="button" onclick="clearSearchFilter()" class="text-xs text-gray-400 hover:text-red-500 transition">✕ Clear</button>
            </div>

            {{-- AJAX Loading Overlay --}}
            <div id="shop-loading-overlay" class="hidden absolute inset-0 bg-white/70 dark:bg-gray-950/70 backdrop-blur-xs z-30 flex items-center justify-center rounded-2xl min-h-[300px]">
                <div class="flex flex-col items-center gap-3">
                    <svg class="animate-spin h-8 w-8 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">Updating products…</span>
                </div>
            </div>

            {{-- Products Container (Point 5) --}}
            <div id="shop-products-container" class="transition-opacity duration-200">
                @include('users.partials.shop-product-list', ['products' => $products, 'currency' => $currency])
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const MIN_POSSIBLE_PRICE = {{ $minPossiblePrice }};
    const MAX_POSSIBLE_PRICE = {{ $maxPossiblePrice }};

    // Range slider DOM elements
    const sliderMin = document.getElementById('price-slider-min');
    const sliderMax = document.getElementById('price-slider-max');
    const inputMin  = document.getElementById('price-input-min');
    const inputMax  = document.getElementById('price-input-max');
    const displayMin = document.getElementById('price-min-display');
    const displayMax = document.getElementById('price-max-display');
    const trackHighlight = document.getElementById('slider-track-highlight');

    // Update track highlight bar
    function updateTrackHighlight() {
        const minVal = parseFloat(sliderMin.value);
        const maxVal = parseFloat(sliderMax.value);
        const span = MAX_POSSIBLE_PRICE - MIN_POSSIBLE_PRICE;
        const leftPercent = span > 0 ? ((minVal - MIN_POSSIBLE_PRICE) / span) * 100 : 0;
        const rightPercent = span > 0 ? ((maxVal - MIN_POSSIBLE_PRICE) / span) * 100 : 100;
        
        trackHighlight.style.left = leftPercent + '%';
        trackHighlight.style.width = Math.max(0, rightPercent - leftPercent) + '%';
    }

    // Sync sliders and inputs
    let priceDebounceTimer = null;
    function syncPrice(source, triggerAjax = true) {
        let minVal = parseFloat(source === 'input' ? inputMin.value : sliderMin.value) || MIN_POSSIBLE_PRICE;
        let maxVal = parseFloat(source === 'input' ? inputMax.value : sliderMax.value) || MAX_POSSIBLE_PRICE;

        if (minVal < MIN_POSSIBLE_PRICE) minVal = MIN_POSSIBLE_PRICE;
        if (maxVal > MAX_POSSIBLE_PRICE) maxVal = MAX_POSSIBLE_PRICE;

        if (source === 'minSlider') {
            if (minVal > maxVal) {
                minVal = maxVal;
                sliderMin.value = minVal;
            }
        } else if (source === 'maxSlider') {
            if (maxVal < minVal) {
                maxVal = minVal;
                sliderMax.value = maxVal;
            }
        } else if (source === 'input') {
            if (minVal > maxVal) maxVal = minVal;
            sliderMin.value = minVal;
            sliderMax.value = maxVal;
        }

        inputMin.value = Math.round(minVal);
        inputMax.value = Math.round(maxVal);
        displayMin.textContent = Math.round(minVal);
        displayMax.textContent = Math.round(maxVal);

        updateTrackHighlight();

        if (triggerAjax) {
            clearTimeout(priceDebounceTimer);
            priceDebounceTimer = setTimeout(() => {
                applyShopFilters();
            }, 350);
        }
    }

    sliderMin.addEventListener('input', () => syncPrice('minSlider', true));
    sliderMax.addEventListener('input', () => syncPrice('maxSlider', true));
    inputMin.addEventListener('change', () => syncPrice('input', true));
    inputMax.addEventListener('change', () => syncPrice('input', true));

    // Initialize highlight on load
    updateTrackHighlight();

    // Select category via AJAX
    function selectCategory(catId) {
        document.getElementById('filter-category').value = catId || '';
        
        // Update active class on buttons
        document.querySelectorAll('.category-filter-btn').forEach(btn => {
            const btnCat = btn.getAttribute('data-category-id');
            const isActive = (!catId && !btnCat) || (String(btnCat) === String(catId));
            if (isActive) {
                btn.className = 'category-filter-btn text-left w-full text-sm transition text-brand-700 dark:text-brand-400 font-bold flex justify-between items-center';
            } else {
                btn.className = 'category-filter-btn text-left w-full text-sm transition text-gray-600 dark:text-gray-400 hover:text-brand-700 dark:hover:text-brand-400 flex justify-between items-center';
            }
        });

        applyShopFilters();
    }

    // Sort change via AJAX
    function onSortChange(sortVal) {
        document.getElementById('filter-sort').value = sortVal;
        applyShopFilters();
    }

    // Clear search
    function clearSearchFilter() {
        document.getElementById('filter-search').value = '';
        document.getElementById('search-active-badge').classList.add('hidden');
        applyShopFilters();
    }

    // Clear rating
    function clearRatingFilter() {
        document.querySelectorAll('input[name="rating"]').forEach(r => r.checked = false);
        applyShopFilters();
    }

    // Reset all filters
    function resetShopFilters() {
        document.getElementById('filter-category').value = '';
        document.getElementById('filter-sort').value = '';
        document.getElementById('filter-search').value = '';
        document.getElementById('search-active-badge').classList.add('hidden');
        document.getElementById('shop-sort-select').value = '';
        clearRatingFilter();
        
        sliderMin.value = MIN_POSSIBLE_PRICE;
        sliderMax.value = MAX_POSSIBLE_PRICE;
        syncPrice('input', false);
        
        document.querySelectorAll('.category-filter-btn').forEach(btn => {
            const btnCat = btn.getAttribute('data-category-id');
            btn.className = (!btnCat)
                ? 'category-filter-btn text-left w-full text-sm transition text-brand-700 dark:text-brand-400 font-bold flex justify-between items-center'
                : 'category-filter-btn text-left w-full text-sm transition text-gray-600 dark:text-gray-400 hover:text-brand-700 dark:hover:text-brand-400 flex justify-between items-center';
        });

        applyShopFilters();
    }

    // Main AJAX filter function (Point 5)
    let abortController = null;
    function applyShopFilters(pageUrl = null) {
        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        const overlay = document.getElementById('shop-loading-overlay');
        const container = document.getElementById('shop-products-container');
        overlay.classList.remove('hidden');
        container.classList.add('opacity-40');

        let url;
        if (pageUrl) {
            url = new URL(pageUrl, window.location.origin);
        } else {
            url = new URL("{{ route('shop.index') }}", window.location.origin);
            const catId = document.getElementById('filter-category').value;
            const sort = document.getElementById('filter-sort').value;
            const search = document.getElementById('filter-search').value;
            const priceMin = inputMin.value;
            const priceMax = inputMax.value;
            const ratingRadio = document.querySelector('input[name="rating"]:checked');

            if (catId) url.searchParams.set('category', catId);
            if (sort) url.searchParams.set('sort', sort);
            if (search) url.searchParams.set('search', search);
            if (priceMin && priceMin != MIN_POSSIBLE_PRICE) url.searchParams.set('price_min', priceMin);
            if (priceMax && priceMax != MAX_POSSIBLE_PRICE) url.searchParams.set('price_max', priceMax);
            if (ratingRadio) url.searchParams.set('rating', ratingRadio.value);
        }

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: abortController.signal
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            container.innerHTML = data.html;
            document.getElementById('shop-total-count').textContent = data.total;
            if (data.title) {
                document.getElementById('shop-heading-title').textContent = data.title;
            }
            window.history.pushState(null, '', url.toString());

            // Re-bind pagination clicks
            bindPaginationLinks();
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Filter error:', err);
                if (window.toast) toast('Error loading products. Please try again.', 'error');
            }
        })
        .finally(() => {
            overlay.classList.add('hidden');
            container.classList.remove('opacity-40');
        });
    }

    // Intercept pagination clicks for seamless JS navigation
    function bindPaginationLinks() {
        const container = document.getElementById('shop-products-container');
        container.querySelectorAll('.shop-pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetUrl = this.getAttribute('href');
                if (targetUrl) {
                    applyShopFilters(targetUrl);
                    window.scrollTo({
                        top: document.getElementById('shop-page-wrapper').offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // Initial binding
    document.addEventListener('DOMContentLoaded', () => {
        bindPaginationLinks();
    });
</script>
@endpush
