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

    <main>
        @yield('content')
    </main>

    @include('users.layouts.footer')

    {{-- Scroll to top button --}}
    <div x-data="{
        visible: false,
        checkScroll() {
            this.visible = (window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0) > 300;
        },
        scrollToTop() {
            const startY = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
            if (startY <= 0) return;

            // Duration dynamically tuned between 450ms and 750ms based on distance
            const duration = Math.min(750, Math.max(450, Math.round(Math.sqrt(startY) * 15)));
            const startTime = ('now' in window.performance) ? performance.now() : Date.now();

            // Smooth cubic ease-out curve for gentle, luxurious deceleration
            const easeOutCubic = t => (--t) * t * t + 1;

            let userCancelled = false;
            const cancelOnUserInteraction = () => {
                userCancelled = true;
                cleanup();
            };

            const cleanup = () => {
                window.removeEventListener('wheel', cancelOnUserInteraction);
                window.removeEventListener('touchmove', cancelOnUserInteraction);
            };

            window.addEventListener('wheel', cancelOnUserInteraction, { passive: true, once: true });
            window.addEventListener('touchmove', cancelOnUserInteraction, { passive: true, once: true });

            const step = (now) => {
                if (userCancelled) return;

                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easedProgress = easeOutCubic(progress);

                const targetY = Math.round(startY * (1 - easedProgress));
                window.scrollTo(0, targetY);

                if (progress < 1 && targetY > 0) {
                    window.requestAnimationFrame(step);
                } else {
                    window.scrollTo(0, 0);
                    cleanup();
                }
            };

            window.requestAnimationFrame(step);
        }
    }"
    x-init="checkScroll()"
    @scroll.window.passive="checkScroll()"
    class="fixed bottom-6 right-6 z-40 print:hidden"
    id="scroll-to-top-container">
        <button
            x-show="visible"
            x-cloak
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-4 scale-90"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-90"
            @click="scrollToTop()"
            type="button"
            id="scroll-to-top-btn"
            aria-label="Scroll to top"
            title="Scroll to top"
            class="group w-11 h-11 rounded-full bg-brand-700 hover:bg-brand-800 text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center ring-2 ring-brand-500/20 dark:ring-brand-400/30 focus:outline-none focus:ring-4 focus:ring-brand-500/40 cursor-pointer">
            <svg class="w-5 h-5 transition-transform duration-200 group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
            </svg>
        </button>
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

