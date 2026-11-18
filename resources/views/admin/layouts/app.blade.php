<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-timezone" content="{{ config('app.timezone') }}">
    <script>
        window.APP_TIMEZONE = @json(config('app.timezone'));
    </script>
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
    <script>
        if (window.$ && $.fn && $.fn.dataTable) {
            // Prevent browser alert() popups for DataTables warnings
            $.fn.dataTable.ext.errMode = function(settings, helpPage, message) {
                console.warn('DataTables notice:', message, helpPage);
            };

            // Strip any single-cell colspan empty row before DataTables processes the DOM
            $(document).on('preInit.dt', function(e, settings) {
                const api = new $.fn.dataTable.Api(settings);
                $(api.table().node()).find('tbody tr td[colspan]').closest('tr').remove();
            });
        }
    </script>
    <style>
        .dataTables_wrapper .dataTables_empty {
            text-align: center !important;
            padding: 3rem 1.5rem !important;
            color: #6b7280 !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
        }
        .dark .dataTables_wrapper .dataTables_empty {
            color: #9ca3af !important;
            background-color: transparent !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.4rem 0.75rem;
            font-size: 0.8125rem;
            background-color: #ffffff;
            color: #111827;
        }
        .dark .dataTables_wrapper .dataTables_filter input {
            border-color: #374151;
            background-color: #1f2937;
            color: #f3f4f6;
        }
    </style>
    @stack('styles')
</head>
<body class="admin-body font-sans bg-gray-100 dark:bg-[#0b1329] text-gray-900 dark:text-gray-100 antialiased"
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
        class="fixed top-0 left-0 h-full w-64 bg-gray-900 dark:bg-[#0c1427] z-40 transition-all duration-300 flex flex-col border-r border-gray-800 dark:border-slate-800/80">

        {{-- Logo & Toggle --}}
        <div class="h-16 px-4 border-b border-gray-800 dark:border-slate-800/80 flex items-center justify-between">
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
                ['route' => 'admin.returns.index',      'icon' => 'M16 15v-1a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4',                                                                                                                     'label' => 'Returns & Refunds'],
                ['route' => 'admin.reports.profit-loss', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label' => 'P&L Report'],
                ['route' => 'admin.promo-codes.index',  'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',                                                      'label' => 'Promo Codes'],
                ['route' => 'admin.reviews.index',      'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'label' => 'Reviews'],
                ['route' => 'admin.blog-posts.index',   'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'label' => 'Blog Posts'],
                ['route' => 'admin.blog-comments.index','icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'label' => 'Comments'],
                ['route' => 'admin.users.index',        'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',                                              'label' => 'Users'],
                ['route' => 'admin.contact-messages.index', 'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4', 'label' => 'Contact Messages'],
                ['route' => 'admin.faqs.index',         'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'FAQs'],
                ['route' => 'admin.subscribers.index',  'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',                                                        'label' => 'Subscribers'],
                ['route' => 'admin.terms.edit',         'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',                                       'label' => 'Terms & Conditions'],
                ['route' => 'admin.privacy.edit',       'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',                                                        'label' => 'Privacy Policy'],
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
        <div class="p-3 border-t border-gray-800 dark:border-slate-800/80">
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
        <header class="sticky top-0 z-20 bg-white dark:bg-[#0f172a] border-b border-gray-200 dark:border-slate-800/80 h-16 flex items-center px-4 gap-4">
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
            <div class="ml-auto flex items-center gap-2 sm:gap-3">
                {{-- Admin Notification Dropdown --}}
                @php
                    $adminUnread = auth('admin')->user()?->unreadNotifications()->count() ?? 0;
                @endphp
                <div class="relative" x-data="{
                    open: false,
                    unreadCount: {{ $adminUnread }},
                    notifications: [],
                    loading: false,
                    async fetchNotifications() {
                        this.loading = true;
                        try {
                            const res = await fetch('{{ route('admin.notifications.feed') }}', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (res.ok) {
                                const data = await res.json();
                                this.unreadCount = (typeof data.unread_count !== 'undefined') ? data.unread_count : {{ $adminUnread }};
                                this.notifications = data.notifications || [];
                            }
                        } catch (e) {
                            console.error('Failed to load notifications', e);
                        } finally {
                            this.loading = false;
                        }
                    },
                    async markAllRead() {
                        try {
                            const res = await fetch('{{ route('admin.notifications.mark-all-read') }}', {
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
                                window.dispatchEvent(new CustomEvent('admin-notifications-marked-all-read'));
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    },
                    async clickNotification(notif) {
                        if (!notif.read) {
                            fetch('{{ url('admin/notifications') }}/' + notif.id + '/read', {
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
                            window.dispatchEvent(new CustomEvent('admin-notification-marked-read', { detail: { id: notif.id } }));
                        }
                        if (notif.url) {
                            window.location.href = notif.url;
                        }
                    }
                }" x-init="fetchNotifications()">
                    @php
                    $isAdminNotificationPage = request()->routeIs('admin.notifications.*');
                @endphp
                <button @click="open = !open" type="button" class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition {{ $isAdminNotificationPage ? 'text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/40 ring-1 ring-brand-500/20' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-200' }}" :class="open ? 'text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/40 ring-1 ring-brand-500/20' : ''" title="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"
                            class="absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full min-w-4 text-center leading-tight {{ $adminUnread > 0 ? '' : 'hidden' }}">{{ $adminUnread > 99 ? '99+' : ($adminUnread > 0 ? $adminUnread : '') }}</span>
                    </button>

                    {{-- Dropdown Panel --}}
                    <div x-show="open" @click.away="open = false" x-cloak x-transition
                        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-950/50">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-gray-900 dark:text-gray-100">Notifications</span>
                                <span x-show="unreadCount > 0" x-text="unreadCount + ' new'" class="text-[10px] font-semibold bg-brand-100 text-brand-800 dark:bg-brand-900/40 dark:text-brand-300 px-2 py-0.5 rounded-full"></span>
                            </div>
                            <button x-show="unreadCount > 0" @click="markAllRead()" type="button" class="text-xs text-brand-600 hover:text-brand-700 dark:text-brand-400 font-medium transition">
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
                                <div @click="clickNotification(notif)" class="p-3.5 hover:bg-gray-50 dark:hover:bg-slate-800/60 cursor-pointer transition flex items-start gap-3" :class="!notif.read ? 'bg-brand-50/40 dark:bg-slate-800/90' : ''">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-xs" :class="!notif.read ? 'bg-brand-600 dark:bg-slate-700 text-white dark:text-slate-100' : 'bg-gray-100 dark:bg-gray-800 text-gray-500'">
                                        <template x-if="notif.type.includes('order')">
                                            <span>📦</span>
                                        </template>
                                        <template x-if="notif.type.includes('like')">
                                            <span>❤️</span>
                                        </template>
                                        <template x-if="notif.type.includes('comment') || notif.type.includes('reply')">
                                            <span>💬</span>
                                        </template>
                                        <template x-if="notif.type.includes('review')">
                                            <span>⭐</span>
                                        </template>
                                        <template x-if="notif.type.includes('subscribe')">
                                            <span>✉️</span>
                                        </template>
                                        <template x-if="notif.type.includes('contact')">
                                            <span>📩</span>
                                        </template>
                                        <template x-if="!['order', 'like', 'comment', 'reply', 'review', 'subscribe', 'contact'].some(t => notif.type.includes(t))">
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
                                    <span x-show="!notif.read" class="w-2 h-2 rounded-full bg-brand-500 shrink-0 mt-1.5"></span>
                                </div>
                            </template>
                        </div>

                        <div class="p-2.5 border-t border-gray-100 dark:border-gray-800 text-center bg-gray-50/50 dark:bg-gray-950/50">
                            <a href="{{ route('admin.notifications.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 block transition">
                                View All Notifications →
                            </a>
                        </div>
                    </div>
                </div>

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

