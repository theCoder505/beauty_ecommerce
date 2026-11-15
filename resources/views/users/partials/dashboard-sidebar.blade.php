{{-- Shared sidebar partial for user dashboard --}}
<aside class="lg:w-56 shrink-0">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700 sticky top-20">
        <div class="flex items-center gap-3 mb-5 pb-5 border-b border-cream-200 dark:border-gray-700">
            <img src="{{ auth()->user()->avatar_url }}" class="w-10 h-10 rounded-full object-cover">
            <div class="min-w-0"><p class="font-semibold text-sm text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}</p><p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p></div>
        </div>
        <nav class="space-y-1 text-sm font-medium">
            @php $links = [
                ['route' => 'user.dashboard',       'label' => 'Dashboard',      'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'user.orders',          'label' => 'My Orders',      'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['route' => 'user.wishlist',        'label' => 'Wishlist',       'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                ['route' => 'user.profile',         'label' => 'Profile',        'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['route' => 'user.change-password',  'label' => 'Change Password','icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                ['route' => 'user.change-email',     'label' => 'Change Email',   'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ]; @endphp
            @foreach($links as $link)
            <a href="{{ route($link['route']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs($link['route'].'*') ? 'bg-brand-700 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-cream-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/></svg>{{ $link['label'] }}
            </a>
            @endforeach
        </nav>
    </div>
</aside>

