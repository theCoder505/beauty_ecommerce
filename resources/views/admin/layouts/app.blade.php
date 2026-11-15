<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $siteSettings = app(\App\Services\SettingService::class)->all(); @endphp
    <title>@yield('title', 'Dashboard') – {{ $siteSettings['site_name'] ?? 'Admin Panel' }}</title>
    
    @if(!empty($siteSettings['site_icon']))
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/'.$siteSettings['site_icon']) }}">
    <link rel="apple-touch-icon" href="{{ asset('storage/'.$siteSettings['site_icon']) }}">
    @endif

    {{-- Dark theme script (avoids FOUC) --}}
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>
<body class="font-sans bg-gray-100 dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased"
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('admin_sidebar_collapsed') === 'true',
        toggleCollapse() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('admin_sidebar_collapsed', this.sidebarCollapsed);
        }
    }">

    {{-- Sidebar Overlay --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside
        :class="[
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'
        ]"
        class="fixed top-0 left-0 h-full w-64 bg-gray-900 dark:bg-gray-950 z-40 transition-all duration-300 flex flex-col border-r border-gray-800">

        {{-- Logo & Toggle --}}
        <div class="h-16 px-4 border-b border-gray-800 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 overflow-hidden transition-all duration-300" :class="sidebarCollapsed ? 'lg:hidden' : ''">
                @if(!empty($siteSettings['site_logo']))
                    <img src="{{ asset('storage/'.$siteSettings['site_logo']) }}" alt="{{ $siteSettings['site_name'] ?? 'Logo' }}" class="h-8 max-w-[140px] object-contain">
                @else
                    <span class="text-brand-400 font-serif text-xl font-bold whitespace-nowrap">✦ {{ $siteSettings['site_name'] ?? 'GlowHaus' }}</span>
                @endif
            </a>
            <a href="{{ route('admin.dashboard') }}" class="hidden font-serif text-xl font-bold text-brand-400 mx-auto" :class="sidebarCollapsed ? 'lg:block' : ''" title="{{ $siteSettings['site_name'] ?? 'Admin' }}">
                @if(!empty($siteSettings['site_icon']))
                    <img src="{{ asset('storage/'.$siteSettings['site_icon']) }}" alt="Icon" class="h-7 w-7 object-contain">
                @else
                    ✦
                @endif
            </a>
            <button type="button" @click="toggleCollapse()"
                class="hidden lg:flex p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition focus:outline-none"
                :title="sidebarCollapsed ? 'Expand sidebar' : 'Minimize sidebar'">
                <svg class="w-4 h-4 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            @php
            $navItems = [
                ['route' => 'admin.dashboard',          'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',  'label' => 'Dashboard'],
                ['route' => 'admin.categories.index',   'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'label' => 'Categories'],
                ['route' => 'admin.products.index',     'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',                                                                                              'label' => 'Products'],
                ['route' => 'admin.orders.index',       'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',                             'label' => 'Orders'],
                ['route' => 'admin.reviews.index',      'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'label' => 'Reviews'],
                ['route' => 'admin.blog-posts.index',   'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'label' => 'Blog Posts'],
                ['route' => 'admin.blog-comments.index','icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'label' => 'Comments'],
                ['route' => 'admin.subscribers.index',  'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',                                                        'label' => 'Subscribers'],
                ['route' => 'admin.settings.index',     'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Settings'],
            ];
            @endphp

            @foreach($navItems as $item)
            @php
                $isActive = $item['route'] === 'admin.dashboard'
                    ? request()->routeIs('admin.dashboard')
                    : (request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])));
            @endphp
            <div class="relative group">
                <a href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ $isActive ? 'bg-brand-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                    <span class="truncate whitespace-nowrap transition-opacity duration-200" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                </a>
                {{-- Floating Tooltip when collapsed on desktop --}}
                <div x-show="sidebarCollapsed" x-cloak
                    class="hidden lg:group-hover:block absolute left-full top-1/2 -translate-y-1/2 ml-3 px-2.5 py-1.5 bg-gray-900 text-white text-xs font-semibold rounded-lg shadow-2xl border border-gray-700 whitespace-nowrap z-50 pointer-events-none">
                    {{ $item['label'] }}
                </div>
            </div>
            @endforeach
        </nav>

        {{-- User info --}}
        <div class="p-3 border-t border-gray-800">
            <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'lg:justify-center' : ''">
                <img src="{{ auth('admin')->user()?->avatar_url ?? asset('images/placeholder-avatar.jpg') }}" class="w-9 h-9 rounded-full object-cover shrink-0" title="{{ auth('admin')->user()?->name ?? 'Admin' }}">
                <div class="min-w-0" :class="sidebarCollapsed ? 'lg:hidden' : ''">
                    <p class="text-sm font-semibold text-white truncate">{{ auth('admin')->user()?->name ?? 'Admin' }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth('admin')->user()?->email ?? 'admin@beautyecom.com' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full text-left text-xs text-red-400 hover:text-red-300 transition flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-red-950/40"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
                    title="Sign Out">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'"
        class="min-h-screen flex flex-col transition-all duration-300">
        {{-- Top bar --}}
        <header class="sticky top-0 z-20 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 h-16 flex items-center px-4 gap-4">
            {{-- Mobile drawer toggle --}}
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" aria-label="Open mobile menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            {{-- Desktop collapse toggle in header --}}
            <button type="button" @click="toggleCollapse()"
                class="hidden lg:flex items-center justify-center p-2 rounded-lg text-gray-500 hover:text-gray-800 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition focus:outline-none"
                :title="sidebarCollapsed ? 'Expand sidebar' : 'Minimize sidebar'" aria-label="Toggle sidebar">
                <svg class="w-5 h-5 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
            <h1 class="font-semibold text-gray-800 dark:text-white text-sm">@yield('title', 'Dashboard')</h1>
            <div class="ml-auto flex items-center gap-3">
                <button onclick="toggleTheme()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition text-gray-500">
                    <svg class="w-4 h-4 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <a href="{{ route('home') }}" target="_blank" class="text-xs text-gray-500 hover:text-brand-700 transition">View Site ↗</a>
            </div>
        </header>

        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    {{-- SweetAlert2 Session Notifications --}}
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.toast) toast(@json(session('success')), 'success');
        });
    </script>
    @endif
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.toast) toast(@json(session('error')), 'error');
        });
    </script>
    @endif
    @if(session('status'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.toast) toast(@json(session('status')), 'info');
        });
    </script>
    @endif

    @stack('scripts')
</body>
</html>

