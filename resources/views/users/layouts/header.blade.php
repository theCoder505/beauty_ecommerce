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
    class="{{ $isHome ? 'bg-gradient-to-r from-[#fa7e8c] via-[#e1276f] to-[#7d0b48] text-white' : 'bg-white/95 backdrop-blur border-b border-pink-100 text-gray-900' }} sticky top-0 z-50 transition-colors duration-300"
    x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center h-16 sm:h-20 gap-4 lg:gap-6 justify-between">

        {{-- Logo Matching Mockup --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0 group">
            <div
                class="{{ $isHome ? 'text-white' : 'text-[#e1276f]' }} flex items-center justify-center transition-transform group-hover:scale-105">
                <svg class="w-7 h-7 sm:w-8 sm:h-8 fill-current" viewBox="0 0 24 24">
                    <path
                        d="M12 2C12 2 8 8 8 13C8 16.31 10.69 19 14 19C15.65 19 17.15 18.33 18.24 17.24C16.89 16.5 15.5 15.2 14.7 13.5C13.6 11.2 14.1 8.3 15.5 6.3C14.5 4.5 13.2 3.1 12 2ZM6.5 14C5.1 14 4 15.1 4 16.5C4 17.9 5.1 19 6.5 19C7.9 19 9 17.9 9 16.5C9 15.1 7.9 14 6.5 14Z" />
                </svg>
            </div>
            <div class="leading-none">
                <span
                    class="block font-bold text-lg sm:text-2xl tracking-tight {{ $isHome ? 'text-white' : 'text-gray-900' }}">GlowCare</span>
                <span
                    class="block text-[9px] sm:text-[10px] uppercase font-bold tracking-widest {{ $isHome ? 'text-pink-100/90' : 'text-[#e1276f]' }} mt-0.5">Health
                    &amp; Beauty</span>
            </div>
        </a>

        {{-- Nav (desktop) - Matching Image Navigation --}}
        <nav class="hidden md:flex items-center gap-5 lg:gap-7 text-xs sm:text-sm font-medium">
            <a href="{{ route('home') }}"
                class="relative py-1 transition {{ $isHome ? ($isHome ? 'text-white font-semibold' : 'text-[#e1276f] font-semibold') : 'text-gray-700 hover:text-[#e1276f]' }}">
                <span>Home</span>
                @if ($isHome)
                    <span
                        class="absolute -bottom-1.5 left-0 w-full h-0.5 bg-brand-700 dark:bg-brand-400 rounded-full"></span>
                @endif
            </a>
            <a href="{{ $isHome ? '#categories' : route('home') . '#categories' }}"
                class="relative py-1 transition {{ $isHome ? 'text-white/90 hover:text-white' : 'text-gray-700 hover:text-[#e1276f]' }}">
                <span>Categories</span>
            </a>
            <a href="{{ route('shop.index') }}"
                class="relative py-1 transition {{ $isShop ? 'text-white font-semibold' : ($isHome ? 'text-white/90 hover:text-white' : 'text-gray-700 hover:text-[#e1276f]') }}">
                <span>Shop</span>
                @if ($isShop)
                    <span
                        class="absolute -bottom-1.5 left-0 w-full h-0.5 bg-brand-700 dark:bg-brand-400 rounded-full"></span>
                @endif
            </a>
            <a href="{{ $isHome ? '#offers' : route('home') . '#offers' }}"
                class="relative py-1 transition {{ $isHome ? 'text-white/90 hover:text-white' : 'text-gray-700 hover:text-[#e1276f]' }}">
                <span>Offers</span>
            </a>
            <a href="{{ route('contact') }}"
                class="relative py-1 transition {{ $isContact ? 'text-white font-semibold' : ($isHome ? 'text-white/90 hover:text-white' : 'text-gray-700 hover:text-[#e1276f]') }}">
                <span>Contact</span>
                @if ($isContact)
                    <span
                        class="absolute -bottom-1.5 left-0 w-full h-0.5 bg-brand-700 dark:bg-brand-400 rounded-full"></span>
                @endif
            </a>
            <a href="{{ route('blog.index') }}"
                class="relative py-1 transition {{ $isBlog ? 'text-white font-semibold' : ($isHome ? 'text-white/90 hover:text-white' : 'text-gray-700 hover:text-[#e1276f]') }}">
                <span>Blog</span>
                @if ($isBlog)
                    <span
                        class="absolute -bottom-1.5 left-0 w-full h-0.5 bg-brand-700 dark:bg-brand-400 rounded-full"></span>
                @endif
            </a>
        </nav>

        {{-- Right Controls: Pill Search, Notifications (when auth), User Icon, Cart Icon --}}
        <div class="flex items-center gap-2 sm:gap-3.5">
            {{-- Persistent Pill Search Bar Matching Reference Image --}}
            <div class="relative w-40 sm:w-52 lg:w-60">
                <form action="{{ route('shop.index') }}" method="GET" class="relative">
                    <input type="text" name="search" placeholder="Search for products..."
                        value="{{ request('search') }}"
                        class="w-full bg-white text-gray-800 placeholder-gray-400 rounded-full pl-4 pr-9 py-1.5 sm:py-2 text-xs shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-300 transition">
                    <button type="submit"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#e1276f] transition"
                        aria-label="Search">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
            </div>

            {{-- User Notifications (when authenticated) --}}
            @auth
                @php
                    $headerUserUnread = auth()->user()?->unreadNotifications()->count() ?? 0;
                    $isNotificationPage =
                        request()->routeIs('notifications.*') || request()->routeIs('user.notifications');
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
                                window.dispatchEvent(new CustomEvent('notifications-marked-all-read'));
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
                            window.dispatchEvent(new CustomEvent('notification-marked-read', { detail: { id: notif.id } }));
                        }
                        if (notif.url) {
                            window.location.href = notif.url;
                        }
                    }
                }" x-init="fetchNotifications()">
                    <button @click="open = !open" type="button"
                        class="relative p-1.5 sm:p-2 rounded-full transition {{ $isNotificationPage ? 'text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/60 ring-1 ring-brand-500/20' : ($isHome ? 'text-white hover:bg-white/10' : 'text-gray-700 hover:bg-cream-200') }}"
                        :class="open ? 'ring-1 ring-white/40' : ''" aria-label="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"
                            class="absolute -top-1 -right-1 bg-brand-700 text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full min-w-4 text-center leading-tight {{ $headerUserUnread > 0 ? '' : 'hidden' }}">{{ $headerUserUnread > 99 ? '99+' : ($headerUserUnread > 0 ? $headerUserUnread : '') }}</span>
                    </button>

                    {{-- Dropdown Panel --}}
                    <div x-show="open" @click.away="open = false" x-cloak x-transition
                        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white border border-gray-200 rounded-2xl shadow-2xl z-50 overflow-hidden text-gray-900">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-cream-50/50">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-gray-900">Notifications</span>
                                <span x-show="unreadCount > 0" x-text="unreadCount + ' new'"
                                    class="text-[10px] font-semibold bg-brand-100 text-brand-800 px-2 py-0.5 rounded-full"></span>
                            </div>
                            <button x-show="unreadCount > 0" @click="markAllRead()" type="button"
                                class="text-xs text-brand-700 hover:text-brand-800 font-medium transition">
                                Mark all read
                            </button>
                        </div>

                        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                            <template x-if="notifications.length === 0">
                                <div class="py-8 text-center text-gray-400 text-xs">
                                    No notifications yet
                                </div>
                            </template>
                            <template x-for="notif in notifications" :key="notif.id">
                                <div @click="clickNotification(notif)"
                                    class="p-3.5 hover:bg-cream-50 cursor-pointer transition flex items-start gap-3"
                                    :class="!notif.read ? 'bg-brand-50/40' : ''">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-xs"
                                        :class="!notif.read ? 'bg-brand-700 text-white' : 'bg-gray-100 text-gray-500'">
                                        <template x-if="notif.type.includes('order')">
                                            <span>📦</span>
                                        </template>
                                        <template x-if="notif.type.includes('like')">
                                            <span>❤️</span>
                                        </template>
                                        <template x-if="notif.type.includes('reply') || notif.type.includes('comment')">
                                            <span>💬</span>
                                        </template>
                                        <template
                                            x-if="!['order', 'like', 'reply', 'comment'].some(t => notif.type.includes(t))">
                                            <span>🔔</span>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1">
                                            <p class="text-xs font-semibold text-gray-900 truncate" x-text="notif.title">
                                            </p>
                                            <span class="text-[10px] text-gray-400 whitespace-nowrap"
                                                x-text="notif.created_at"></span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-0.5 line-clamp-2" x-text="notif.message"></p>
                                    </div>
                                    <span x-show="!notif.read"
                                        class="w-2 h-2 rounded-full bg-brand-600 shrink-0 mt-1.5"></span>
                                </div>
                            </template>
                        </div>

                        <div class="p-2.5 border-t border-gray-100 text-center bg-cream-50/50">
                            <a href="{{ route('notifications.index') }}"
                                class="text-xs font-semibold text-brand-700 hover:text-brand-800 block transition">
                                View All Notifications →
                            </a>
                        </div>
                    </div>
                </div>
            @endauth

            {{-- User Profile Icon --}}
            @auth
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="p-1 sm:p-1.5 rounded-full transition {{ $isHome ? 'text-white hover:text-pink-100' : 'text-gray-700 hover:text-[#e1276f]' }}"
                        title="My Account">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover border border-white/50 shadow-sm">
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak x-transition
                        class="absolute right-0 mt-2 w-48 bg-white border border-pink-100 rounded-2xl shadow-xl py-1 z-50 text-gray-800">
                        <div class="px-4 py-2 border-b border-pink-50 text-xs font-semibold text-gray-500">
                            {{ auth()->user()->name }}
                        </div>
                        <a href="{{ route('user.dashboard') }}"
                            class="block px-4 py-2 text-sm hover:bg-pink-50 hover:text-[#e1276f] transition">
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}"
                    class="p-1 sm:p-1.5 rounded-full transition {{ $isHome ? 'text-white hover:text-pink-100' : 'text-gray-700 hover:text-[#e1276f]' }}"
                    title="Sign In / Register">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </a>
            @endauth

            {{-- Cart Icon With Badge (3 in image, dynamic here) --}}
            <a href="{{ route('cart.index') }}"
                class="relative p-1 sm:p-1.5 rounded-full transition {{ $isHome ? 'text-white hover:text-pink-100' : 'text-gray-700 hover:text-[#e1276f]' }}"
                title="Cart">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span id="header-cart-count"
                    class="absolute -top-1 -right-1 bg-[#ff1475] text-white text-[10px] font-extrabold min-w-4 h-4 px-1 rounded-full flex items-center justify-center shadow-md">
                    {{ $cartCount > 0 ? $cartCount : 3 }}
                </span>
            </a>

            {{-- Mobile hamburger button --}}
            <button @click="mobileOpen = !mobileOpen"
                class="md:hidden p-1.5 rounded-full transition {{ $isHome ? 'text-white hover:bg-white/10' : 'text-gray-700 hover:bg-pink-50' }}"
                aria-label="Toggle navigation">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile nav dropdown --}}
    <div x-show="mobileOpen" x-cloak x-transition
        class="md:hidden bg-white text-gray-900 border-t border-pink-100 px-5 py-4 space-y-2 shadow-lg">
        <a href="{{ route('home') }}" class="block py-2 font-semibold text-[#e1276f]">Home</a>
        <a href="{{ route('home') }}#categories" class="block py-2 text-gray-700 hover:text-[#e1276f]">Categories</a>
        <a href="{{ route('shop.index') }}" class="block py-2 text-gray-700 hover:text-[#e1276f]">Shop</a>
        <a href="{{ route('home') }}#offers" class="block py-2 text-gray-700 hover:text-[#e1276f]">Offers</a>
        <a href="{{ route('contact') }}" class="block py-2 text-gray-700 hover:text-[#e1276f]">Contact</a>
        <a href="{{ route('blog.index') }}" class="block py-2 text-gray-700 hover:text-[#e1276f]">Blog</a>
    </div>
</header>
