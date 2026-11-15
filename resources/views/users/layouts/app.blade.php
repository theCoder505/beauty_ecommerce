<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dynamic SEO --}}
    <title>{{ $metaTitle ?? (isset($seoTitle) ? $seoTitle . ' – ' : '') . app(\App\Services\SettingService::class)->get('site_name', config('app.name')) }}</title>
    <meta name="description" content="{{ $metaDescription ?? app(\App\Services\SettingService::class)->get('default_meta_description', '') }}">
    <meta name="keywords"    content="{{ $metaKeywords    ?? app(\App\Services\SettingService::class)->get('default_meta_keywords', '') }}">

    {{-- Open Graph --}}
    <meta property="og:title"       content="{{ $metaTitle ?? config('app.name') }}">
    <meta property="og:description" content="{{ $metaDescription ?? '' }}">
    <meta property="og:type"        content="{{ $ogType ?? 'website' }}">
    <meta property="og:url"         content="{{ url()->current() }}">
    @if(isset($ogImage))
    <meta property="og:image" content="{{ $ogImage }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $metaTitle ?? config('app.name') }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? '' }}">

    {{-- Canonical --}}
    <link rel="canonical" href="{{ url()->current() }}">

    @php $siteIcon = app(\App\Services\SettingService::class)->get('site_icon'); @endphp
    @if($siteIcon)
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/'.$siteIcon) }}">
    <link rel="apple-touch-icon" href="{{ asset('storage/'.$siteIcon) }}">
    @endif

    {{-- Dark theme script (avoids FOUC) --}}
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    {{-- JSON-LD --}}
    @stack('jsonld')

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans bg-cream-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100 antialiased" x-data>

    @include('users.layouts.header')

    {{-- Toast Notifications --}}
    <div class="fixed top-20 right-4 z-[200] space-y-2" id="toast-container">
        @if(session('success'))
            <div class="toast flex items-center gap-3 bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium max-w-xs">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="toast flex items-center gap-3 bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium max-w-xs">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ session('error') }}
            </div>
        @endif
    </div>

    <main>
        @yield('content')
    </main>

    @include('users.layouts.footer')

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

