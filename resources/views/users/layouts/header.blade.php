@php
    $settings = app(\App\Services\SettingService::class)->all();
    $cartCount = app(\App\Services\CartService::class)->count();
    $wlCount = auth()->check() ? app(\App\Services\WishlistService::class)->count() : 0;
    $siteName = $settings['site_name'] ?? config('app.name');

    $isHome = request()->routeIs('home');
    $isShop = request()->routeIs('shop.*') || request()->routeIs('products.*');
    $isBlog = request()->routeIs('blog.*');
    $isContact = request()->routeIs('contact*');
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
        <nav class="hidden md:flex items-center gap-6 ml-6 text-sm font-medium">
            <a href="{{ route('home') }}"
                class="relative py-1 transition {{ $isHome ? 'text-brand-700 dark:text-brand-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:text-brand-700 dark:hover:text-brand-400' }}">
                <span>Home</span>
                @if($isHome)
                    <span class="absolute -bottom-1.5 left-0 w-full h-0.5 bg-brand-700 dark:bg-brand-400 rounded-full"></span>
                @endif
            </a>
            <a href="{{ route('shop.index') }}"
                class="relative py-1 transition {{ $isShop ? 'text-brand-700 dark:text-brand-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:text-brand-700 dark:hover:text-brand-400' }}">
                <span>Shop</span>
                @if($isShop)
                    <span class="absolute -bottom-1.5 left-0 w-full h-0.5 bg-brand-700 dark:bg-brand-400 rounded-full"></span>
                @endif
            </a>
            <a href="{{ route('blog.index') }}"
                class="relative py-1 transition {{ $isBlog ? 'text-brand-700 dark:text-brand-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:text-brand-700 dark:hover:text-brand-400' }}">
                <span>Blog</span>
                @if($isBlog)
                    <span class="absolute -bottom-1.5 left-0 w-full h-0.5 bg-brand-700 dark:bg-brand-400 rounded-full"></span>
                @endif
            </a>
            <a href="{{ route('contact') }}"
                class="relative py-1 transition {{ $isContact ? 'text-brand-700 dark:text-brand-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:text-brand-700 dark:hover:text-brand-400' }}">
                <span>Contact</span>
                @if($isContact)
                    <span class="absolute -bottom-1.5 left-0 w-full h-0.5 bg-brand-700 dark:bg-brand-400 rounded-full"></span>
                @endif
            </a>
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
            <a href="{{ auth()->check() ? route('wishlist.index') : route('login') }}"
                class="relative p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition {{ request()->routeIs('wishlist.*') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/60 ring-1 ring-brand-500/20' : 'text-gray-700 dark:text-gray-300' }}"
                aria-label="Wishlist">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <span id="header-wishlist-count"
                    class="{{ $wlCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 bg-brand-700 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $wlCount }}</span>
            </a>

            {{-- Cart --}}
            <a href="{{ route('cart.index') }}"
                class="relative p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition {{ request()->routeIs('cart.*') ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/60 ring-1 ring-brand-500/20' : 'text-gray-700 dark:text-gray-300' }}"
                aria-label="Cart">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span id="header-cart-count"
                    class="{{ $cartCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 bg-brand-700 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $cartCount }}</span>
            </a>

            {{-- User Notifications (when authenticated) --}}
            @auth
                @php
                    $headerUserUnread = auth()->user()?->unreadNotifications()->count() ?? 0;
                @endphp
                <div class="relative" x-data="{
                    open: false,
                    unreadCount: {{ $headerUserUnread }},
                    notifications: [],
                    loading: false,
                    async fetchNotifications() {
                        this.loading = true;
                        try {
                            const res = await fetch('{{ route('notifications.feed') }}', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (res.ok) {
                                const data = await res.json();
                                this.unreadCount = (typeof data.unread_count !== 'undefined') ? data.unread_count : {{ $headerUserUnread }};
                                this.notifications = data.notifications || [];
                            }
                        } catch (e) {
                            console.error('Failed to load user notifications', e);
                        } finally {
                            this.loading = false;
                        }
                    },
                    async markAllRead() {
                        try {
                            const res = await fetch('{{ route('notifications.mark-all-read') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (res.ok) {
                                this.unreadCount = 0;
                                this.notifications.forEach(n => n.read = true);
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    },
                    async clickNotification(notif) {
                        if (!notif.read) {
                            fetch('{{ url('notifications') }}/' + notif.id + '/read', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            notif.read = true;
                            if (this.unreadCount > 0) this.unreadCount--;
                        }
                        if (notif.url) {
                            window.location.href = notif.url;
                        }
                    }
                }" x-init="fetchNotifications()">
                    <button @click="open = !open" type="button"
                        class="relative p-2 rounded-full hover:bg-cream-200 dark:hover:bg-gray-800 transition text-gray-700 dark:text-gray-300"
                        aria-label="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"
                            class="absolute -top-1 -right-1 bg-brand-700 text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full min-w-4 text-center leading-tight {{ $headerUserUnread > 0 ? '' : 'hidden' }}">{{ $headerUserUnread > 99 ? '99+' : ($headerUserUnread > 0 ? $headerUserUnread : '') }}</span>
                    </button>

                    {{-- Dropdown Panel --}}
                    <div x-show="open" @click.away="open = false" x-cloak x-transition
                        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-cream-50/50 dark:bg-gray-950/50">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-gray-900 dark:text-gray-100">Notifications</span>
                                <span x-show="unreadCount > 0" x-text="unreadCount + ' new'"
                                    class="text-[10px] font-semibold bg-brand-100 text-brand-800 dark:bg-brand-900/40 dark:text-brand-300 px-2 py-0.5 rounded-full"></span>
                            </div>
                            <button x-show="unreadCount > 0" @click="markAllRead()" type="button"
                                class="text-xs text-brand-700 hover:text-brand-800 dark:text-brand-400 font-medium transition">
                                Mark all read
                            </button>
                        </div>

                        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-if="notifications.length === 0">
                                <div class="py-8 text-center text-gray-400 dark:text-gray-500 text-xs">
                                    No notifications yet
                                </div>
                            </template>
                            <template x-for="notif in notifications" :key="notif.id">
                                <div @click="clickNotification(notif)"
                                    class="p-3.5 hover:bg-cream-50 dark:hover:bg-gray-800/60 cursor-pointer transition flex items-start gap-3"
                                    :class="!notif.read ? 'bg-brand-50/40 dark:bg-brand-950/20' : ''">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-xs"
                                        :class="!notif.read ? 'bg-brand-700 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500'">
                                        <template x-if="notif.type.includes('order')">
                                            <span>📦</span>
                                        </template>
                                        <template x-if="notif.type.includes('like')">
                                            <span>❤️</span>
                                        </template>
                                        <template x-if="notif.type.includes('reply') || notif.type.includes('comment')">
                                            <span>💬</span>
                                        </template>
                                        <template x-if="!['order', 'like', 'reply', 'comment'].some(t => notif.type.includes(t))">
                                            <span>🔔</span>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1">
                                            <p class="text-xs font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="notif.title"></p>
                                            <span class="text-[10px] text-gray-400 whitespace-nowrap" x-text="notif.created_at"></span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 line-clamp-2" x-text="notif.message"></p>
                                    </div>
                                    <span x-show="!notif.read" class="w-2 h-2 rounded-full bg-brand-600 shrink-0 mt-1.5"></span>
                                </div>
                            </template>
                        </div>

                        <div class="p-2.5 border-t border-gray-100 dark:border-gray-800 text-center bg-cream-50/50 dark:bg-gray-950/50">
                            <a href="{{ route('user.notifications') }}" class="text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400 block transition">
                                View All Notifications →
                            </a>
                        </div>
                    </div>
                </div>
            @endauth

            {{-- User menu --}}
            @auth
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-brand-700 dark:hover:text-brand-400 transition"
                        title="Account Menu">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                            class="w-8 h-8 rounded-full object-cover transition {{ request()->routeIs('user.*') ? 'border-2 border-brand-700 dark:border-brand-400 ring-2 ring-brand-500/30' : 'border border-cream-300 dark:border-gray-700' }}">
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl py-1 z-50">
                        <a href="{{ route('user.dashboard') }}"
                            class="block px-4 py-2 text-sm transition {{ request()->routeIs('user.dashboard') ? 'text-brand-700 dark:text-brand-400 font-semibold bg-cream-50 dark:bg-gray-800' : 'text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-800' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('user.orders') }}"
                            class="block px-4 py-2 text-sm transition {{ request()->routeIs('user.orders*') ? 'text-brand-700 dark:text-brand-400 font-semibold bg-cream-50 dark:bg-gray-800' : 'text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-800' }}">
                            My Orders
                        </a>
                        <a href="{{ route('user.notifications') }}"
                            class="block px-4 py-2 text-sm transition flex items-center justify-between {{ request()->routeIs('user.notifications*') ? 'text-brand-700 dark:text-brand-400 font-semibold bg-cream-50 dark:bg-gray-800' : 'text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-800' }}">
                            <span>Notifications</span>
                            @if(auth()->user()->unreadNotifications()->count() > 0)
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-brand-700 text-white">
                                    {{ auth()->user()->unreadNotifications()->count() }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('user.profile') }}"
                            class="block px-4 py-2 text-sm transition {{ request()->routeIs('user.profile') ? 'text-brand-700 dark:text-brand-400 font-semibold bg-cream-50 dark:bg-gray-800' : 'text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-800' }}">
                            Profile
                        </a>
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
        class="md:hidden border-t border-cream-200 dark:border-gray-800 bg-cream-50 dark:bg-gray-950 px-4 py-4 space-y-1 text-sm font-medium">
        <a href="{{ route('home') }}"
            class="block px-3 py-2 rounded-xl transition {{ $isHome ? 'bg-brand-100 text-brand-800 dark:bg-brand-950/60 dark:text-brand-300 font-semibold' : 'text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-900' }}">
            Home
        </a>
        <a href="{{ route('shop.index') }}"
            class="block px-3 py-2 rounded-xl transition {{ $isShop ? 'bg-brand-100 text-brand-800 dark:bg-brand-950/60 dark:text-brand-300 font-semibold' : 'text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-900' }}">
            Shop
        </a>
        <a href="{{ route('blog.index') }}"
            class="block px-3 py-2 rounded-xl transition {{ $isBlog ? 'bg-brand-100 text-brand-800 dark:bg-brand-950/60 dark:text-brand-300 font-semibold' : 'text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-900' }}">
            Blog
        </a>
        <a href="{{ route('contact') }}"
            class="block px-3 py-2 rounded-xl transition {{ $isContact ? 'bg-brand-100 text-brand-800 dark:bg-brand-950/60 dark:text-brand-300 font-semibold' : 'text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-900' }}">
            Contact
        </a>
        @guest
            <div class="pt-2 border-t border-cream-200 dark:border-gray-800 mt-2 space-y-1">
                <a href="{{ route('login') }}" class="block px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-cream-100 dark:hover:bg-gray-900 rounded-xl">Sign In</a>
                <a href="{{ route('register') }}" class="block px-3 py-2 text-brand-700 font-semibold hover:bg-brand-50 dark:hover:bg-brand-950/40 rounded-xl">Create Account</a>
            </div>
        @endguest
    </div>
</header>
