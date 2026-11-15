@php
    $settings   = app(\App\Services\SettingService::class)->all();
    $cartCount  = auth()->check() ? app(\App\Services\CartService::class)->count() : 0;
    $wlCount    = auth()->check() ? app(\App\Services\WishlistService::class)->count() : 0;
    $siteName   = $settings['site_name'] ?? config('app.name');
@endphp
<header class="sticky top-0 z-50 bg-cream-50/95 dark:bg-gray-950/95 backdrop-blur border-b border-cream-200 dark:border-gray-800" x-data="{ mobileOpen: false, searchOpen: false }">
    <div class="max-w-7xl mx-auto px-4 flex items-center h-16 gap-4">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-serif font-bold text-xl text-gray-900 dark:text-white shrink-0">
            @if(!empty($settings['site_logo']))
                <img src="{{ asset('storage/'.$settings['site_logo']) }}" alt="{{ $siteName }}" class="h-9 w-auto">
            @else
                <span class="text-brand-700">✦</span> {{ $siteName }}
            @endif
        </a>

        {{-- Nav (desktop) --}}
        <nav class="hidden md:flex items-center gap-6 ml-6 text-sm font-medium text-gray-700 dark:text-gray-300">
            <a href="{{ route('home') }}"       class="hover:text-brand-700 dark:hover:text-brand-400 transition">Home</a>
            <a href="{{ route('shop.index') }}" class="hover:text-brand-700 dark:hover:text-brand-400 transition">Shop</a>
            <a href="{{ route('blog.index') }}" class="hover:text-brand-700 dark:hover:text-brand-400 transition">Blog</a>
        </nav>

        <div class="ml-auto flex items-center gap-3">
            {{-- Search toggle --}}
            <button @click="searchOpen = !searchOpen" class="p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition" aria-label="Search">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>

            {{-- Theme toggle --}}
            <button onclick="toggleTheme()" class="p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition" aria-label="Toggle theme">
                <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>

            {{-- Wishlist --}}
            @auth
            <a href="{{ route('wishlist.index') }}" class="relative p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition" aria-label="Wishlist">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                @if($wlCount > 0)
                <span class="absolute -top-1 -right-1 bg-brand-700 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $wlCount }}</span>
                @endif
            </a>
            @endauth

            {{-- Cart --}}
            <a href="{{ route('cart.index') }}" class="relative p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition" aria-label="Cart">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                @if($cartCount > 0)
                <span class="absolute -top-1 -right-1 bg-brand-700 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $cartCount }}</span>
                @endif
            </a>

            {{-- User menu --}}
            @auth
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-brand-700 dark:hover:text-brand-400 transition">
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover border border-cream-300">
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl py-1 z-50">
                    <a href="{{ route('user.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-800">Dashboard</a>
                    <a href="{{ route('user.orders') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-800">My Orders</a>
                    <a href="{{ route('user.profile') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-800">Profile</a>
                    <hr class="my-1 border-gray-200 dark:border-gray-700">
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Sign Out</button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" class="bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-brand-800 transition">Sign In</a>
            @endauth

            {{-- Mobile menu button --}}
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </div>

    {{-- Search bar --}}
    <div x-show="searchOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="border-t border-cream-200 dark:border-gray-800 bg-cream-50 dark:bg-gray-950 px-4 py-3">
        <form action="{{ route('shop.index') }}" method="GET" class="max-w-2xl mx-auto flex gap-2">
            <input type="text" name="search" placeholder="Search for products…" value="{{ request('search') }}"
                class="flex-1 px-4 py-2 rounded-lg border border-cream-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600">
            <button type="submit" class="bg-brand-700 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-brand-800 transition">Search</button>
        </form>
    </div>

    {{-- Mobile nav --}}
    <div x-show="mobileOpen" x-transition x-collapse class="md:hidden border-t border-cream-200 dark:border-gray-800 bg-cream-50 dark:bg-gray-950 px-4 py-4 space-y-3 text-sm font-medium">
        <a href="{{ route('home') }}"       class="block py-2 text-gray-700 dark:text-gray-200">Home</a>
        <a href="{{ route('shop.index') }}" class="block py-2 text-gray-700 dark:text-gray-200">Shop</a>
        <a href="{{ route('blog.index') }}" class="block py-2 text-gray-700 dark:text-gray-200">Blog</a>
        @guest
        <a href="{{ route('login') }}"    class="block py-2 text-gray-700 dark:text-gray-200">Sign In</a>
        <a href="{{ route('register') }}" class="block py-2 text-brand-700">Create Account</a>
        @endguest
    </div>
</header>

