@php
    $settings = app(\App\Services\SettingService::class)->all();
    $cartCount = app(\App\Services\CartService::class)->count();
    $wlCount = auth()->check() ? app(\App\Services\WishlistService::class)->count() : 0;
    $siteName = $settings['site_name'] ?? config('app.name');
@endphp
<header
    class="sticky top-0 z-50 bg-cream-50/95 dark:bg-gray-950/95 backdrop-blur border-b border-cream-200 dark:border-gray-800"
    x-data="{ mobileOpen: false, searchOpen: false }">
    <div class="max-w-7xl mx-auto px-4 flex items-center h-16 gap-4">

        {{-- Logo --}}
        <a href="{{ route('home') }}"
            class="flex items-center gap-2 font-serif font-bold text-xl text-gray-900 dark:text-white shrink-0">
            @if (!empty($settings['site_logo']))
                <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="{{ $siteName }}"
                    class="h-9 w-auto max-w-45 object-contain">
            @elseif(!empty($settings['site_icon']))
                <img src="{{ asset('storage/' . $settings['site_icon']) }}" alt="{{ $siteName }}"
                    class="h-8 w-8 object-contain">
                <span>{{ $siteName }}</span>
            @else
                <span class="text-brand-700">✦</span> {{ $siteName }}
            @endif
        </a>

        {{-- Nav (desktop) --}}
        <nav class="hidden md:flex items-center gap-6 ml-6 text-sm font-medium text-gray-700 dark:text-gray-300">
            <a href="{{ route('home') }}" class="hover:text-brand-700 dark:hover:text-brand-400 transition">Home</a>
            <a href="{{ route('shop.index') }}"
                class="hover:text-brand-700 dark:hover:text-brand-400 transition">Shop</a>
            <a href="{{ route('blog.index') }}"
                class="hover:text-brand-700 dark:hover:text-brand-400 transition">Blog</a>
        </nav>

        <div class="ml-auto flex items-center gap-2 sm:gap-3">
            {{-- Expandable Inline Search Bar (expands leftward on same line) --}}
            <div class="relative flex items-center" @click.away="searchOpen = false"
                @keydown.escape.window="searchOpen = false">
                <form action="{{ route('shop.index') }}" method="GET"
                    class="flex items-center transition-all duration-300 ease-in-out overflow-hidden"
                    :class="searchOpen ?
                        'w-36 xs:w-44 sm:w-56 md:w-64 lg:w-72 max-w-[48vw] sm:max-w-none opacity-100 mr-1 sm:mr-2' :
                        'w-0 opacity-0 pointer-events-none'">
                    <div class="relative w-full">
                        <input type="text" name="search" x-ref="searchInput" placeholder="Search products…"
                            value="{{ request('search') }}"
                            class="w-full pl-8 pr-7 py-1.5 text-xs sm:text-sm rounded-full border border-cream-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none shadow-sm transition">
                        <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button type="button" x-show="searchOpen" @click="searchOpen = false"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs p-0.5 rounded-full"
                            aria-label="Close search">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </form>

                <button type="button"
                    @click="if (searchOpen && $refs.searchInput.value.trim().length > 0) { $refs.searchInput.form.submit(); } else { searchOpen = !searchOpen; $nextTick(() => { if (searchOpen) $refs.searchInput.focus(); }); }"
                    class="p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition shrink-0"
                    :class="searchOpen ? 'text-brand-700 dark:text-brand-400 bg-cream-200/70 dark:bg-gray-800/70' :
                        'text-gray-700 dark:text-gray-300'"
                    aria-label="Toggle search">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>

            {{-- Theme toggle --}}
            <button onclick="toggleTheme()"
                class="p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition" aria-label="Toggle theme">
                <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>

            {{-- Wishlist --}}
            @auth
                <a href="{{ route('wishlist.index') }}"
                    class="relative p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition"
                    aria-label="Wishlist">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    @if ($wlCount > 0)
                        <span
                            class="absolute -top-1 -right-1 bg-brand-700 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $wlCount }}</span>
                    @endif
                </a>
            @endauth

            {{-- Cart --}}
            <a href="{{ route('cart.index') }}"
                class="relative p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition"
                aria-label="Cart">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span id="header-cart-count"
                    class="{{ $cartCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 bg-brand-700 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $cartCount }}</span>
            </a>

            {{-- User menu --}}
            @auth
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-brand-700 dark:hover:text-brand-400 transition">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                            class="w-8 h-8 rounded-full object-cover border border-cream-300">
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl py-1 z-50">
                        <a href="{{ route('user.dashboard') }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-800">Dashboard</a>
                        <a href="{{ route('user.orders') }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-800">My
                            Orders</a>
                        <a href="{{ route('user.profile') }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-800">Profile</a>
                        <hr class="my-1 border-gray-200 dark:border-gray-700">
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Sign
                                Out</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}"
                    class="bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-brand-800 transition">Sign
                    In</a>
            @endauth

            {{-- Mobile menu button --}}
            <button @click="mobileOpen = !mobileOpen"
                class="md:hidden p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>


    {{-- Mobile nav --}}
    <div x-show="mobileOpen" x-transition x-collapse
        class="md:hidden border-t border-cream-200 dark:border-gray-800 bg-cream-50 dark:bg-gray-950 px-4 py-4 space-y-3 text-sm font-medium">
        <a href="{{ route('home') }}" class="block py-2 text-gray-700 dark:text-gray-200">Home</a>
        <a href="{{ route('shop.index') }}" class="block py-2 text-gray-700 dark:text-gray-200">Shop</a>
        <a href="{{ route('blog.index') }}" class="block py-2 text-gray-700 dark:text-gray-200">Blog</a>
        @guest
            <a href="{{ route('login') }}" class="block py-2 text-gray-700 dark:text-gray-200">Sign In</a>
            <a href="{{ route('register') }}" class="block py-2 text-brand-700">Create Account</a>
        @endguest
    </div>
</header>
