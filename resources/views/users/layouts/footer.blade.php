@php
    $settings = app(\App\Services\SettingService::class)->all();
    $siteName = $settings['site_name'] ?? config('app.name');
@endphp
<footer class="bg-warm-900 dark:bg-gray-950 text-cream-200 mt-20">
    {{-- Main footer --}}
    <div class="max-w-7xl mx-auto px-4 py-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
        {{-- Brand --}}
        <div class="lg:col-span-1">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 font-serif font-bold text-2xl text-white">
                @if(!empty($settings['site_logo']))
                    <img src="{{ asset('storage/'.$settings['site_logo']) }}" alt="{{ $siteName }}" class="h-9 w-auto max-w-[180px] object-contain">
                @elseif(!empty($settings['site_icon']))
                    <img src="{{ asset('storage/'.$settings['site_icon']) }}" alt="{{ $siteName }}" class="h-8 w-8 object-contain">
                    <span>{{ $siteName }}</span>
                @else
                    <span class="text-brand-400">✦</span> {{ $siteName }}
                @endif
            </a>
            <p class="mt-4 text-sm text-cream-400 leading-relaxed">Luxury beauty crafted for unforgettable moments. Each product is blended with timeless elegance and a touch of quiet luxury.</p>
            {{-- Social links --}}
            <div class="flex gap-3 mt-6">
                @if(!empty($settings['facebook_url']))
                <a href="{{ $settings['facebook_url'] }}" target="_blank" class="w-9 h-9 rounded-full bg-cream-200/10 hover:bg-brand-700 flex items-center justify-center transition">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                </a>
                @endif
                @if(!empty($settings['instagram_url']))
                <a href="{{ $settings['instagram_url'] }}" target="_blank" class="w-9 h-9 rounded-full bg-cream-200/10 hover:bg-brand-700 flex items-center justify-center transition">
                    <svg class="w-4 h-4 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
                @endif
                @if(!empty($settings['twitter_url']))
                <a href="{{ $settings['twitter_url'] }}" target="_blank" class="w-9 h-9 rounded-full bg-cream-200/10 hover:bg-brand-700 flex items-center justify-center transition">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/></svg>
                </a>
                @endif
            </div>
        </div>

        {{-- Shop Links --}}
        <div>
            <h3 class="font-semibold text-white mb-4">Shop</h3>
            <ul class="space-y-2 text-sm text-cream-400">
                <li><a href="{{ route('shop.index') }}" class="hover:text-brand-400 transition">All Products</a></li>
                <li><a href="{{ route('shop.index') }}?sort=popular" class="hover:text-brand-400 transition">Best Sellers</a></li>
                <li><a href="{{ route('shop.index') }}?sort=newest" class="hover:text-brand-400 transition">New Arrivals</a></li>
                <li><a href="{{ route('shop.index') }}?sale=1" class="hover:text-brand-400 transition">On Sale</a></li>
            </ul>
        </div>

        {{-- Account --}}
        <div>
            <h3 class="font-semibold text-white mb-4">Account</h3>
            <ul class="space-y-2 text-sm text-cream-400">
                @auth
                <li><a href="{{ route('user.dashboard') }}" class="hover:text-brand-400 transition">Dashboard</a></li>
                <li><a href="{{ route('user.orders') }}" class="hover:text-brand-400 transition">My Orders</a></li>
                <li><a href="{{ route('wishlist.index') }}" class="hover:text-brand-400 transition">Wishlist</a></li>
                @else
                <li><a href="{{ route('login') }}" class="hover:text-brand-400 transition">Sign In</a></li>
                <li><a href="{{ route('register') }}" class="hover:text-brand-400 transition">Create Account</a></li>
                @endauth
                <li><a href="{{ route('blog.index') }}" class="hover:text-brand-400 transition">Beauty Blog</a></li>
            </ul>
        </div>

        {{-- Contact --}}
        <div>
            <h3 class="font-semibold text-white mb-4">Contact</h3>
            <ul class="space-y-3 text-sm text-cream-400">
                @if(!empty($settings['contact_email']))
                <li class="flex gap-2 items-start">
                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    {{ $settings['contact_email'] }}
                </li>
                @endif
                @if(!empty($settings['contact_phone']))
                <li class="flex gap-2 items-start">
                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    {{ $settings['contact_phone'] }}
                </li>
                @endif
                @if(!empty($settings['contact_address']))
                <li class="flex gap-2 items-start">
                    <svg class="w-4 h-4 mt-0.5 shrink-0 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ $settings['contact_address'] }}
                </li>
                @endif
            </ul>
        </div>
    </div>

    {{-- Newsletter strip --}}
    <div class="border-t border-cream-200/10">
        <div class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <p class="font-semibold text-white">Subscribe to our newsletter</p>
                <p class="text-sm text-cream-400">Get beauty tips, exclusive offers & new arrivals.</p>
            </div>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex gap-2 w-full md:w-auto">
                @csrf
                <input type="email" name="email" placeholder="Your email address" required
                    class="flex-1 md:w-64 px-4 py-2 rounded-lg bg-cream-200/10 border border-cream-200/20 text-sm text-white placeholder-cream-500 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <button type="submit" class="bg-brand-700 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-brand-600 transition">Subscribe</button>
            </form>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="border-t border-cream-200/10">
        <div class="max-w-7xl mx-auto px-4 py-5 flex flex-col md:flex-row items-center justify-between gap-2 text-xs text-cream-500">
            <p>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
            <div class="flex gap-4">
                <a href="{{ route('sitemap') }}" class="hover:text-brand-400 transition">Sitemap</a>
                <span>Powered by GlowHaus</span>
            </div>
        </div>
    </div>
</footer>

