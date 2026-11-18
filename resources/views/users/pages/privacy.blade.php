@extends('users.layouts.app')
@section('title', 'Privacy Policy — ' . ($siteName ?? 'GlowHaus'))

@section('content')
<div class="min-h-screen bg-cream-50/50 dark:bg-gray-950 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-brand-700 transition">Home</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-gray-200 font-semibold">Privacy Policy</span>
        </nav>

        {{-- Header Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 sm:p-12 border border-cream-200 dark:border-gray-800 shadow-sm mb-8 text-center relative overflow-hidden">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-400 mb-4 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Privacy Policy</h1>
            <p class="text-xs text-gray-500 mt-2">Last Updated: {{ date('F Y') }} · How {{ $siteName ?? 'GlowHaus' }} collects, safeguards, and respects your personal information</p>
        </div>

        {{-- Content Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 sm:p-12 border border-cream-200 dark:border-gray-800 shadow-sm leading-relaxed text-gray-700 dark:text-gray-300">
            @if(!empty($content))
                <div class="prose dark:prose-invert max-w-none text-sm space-y-4 whitespace-pre-line">
                    {!! nl2br(e($content)) !!}
                </div>
            @else
                <div class="space-y-8 text-sm">
                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">1. Information We Collect</h2>
                        <p>We collect essential information required to fulfill orders, provide personalized luxury beauty recommendations, and offer premier customer care. This includes your name, shipping address, email address, telephone number, payment preference details, and order history.</p>
                    </section>

                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">2. How We Use Your Data</h2>
                        <ul class="list-disc list-inside space-y-1.5 pl-2">
                            <li>To process, fulfill, deliver, and track your beauty orders.</li>
                            <li>To handle returns, refunds, customer service queries, and support communications.</li>
                            <li>To authenticate user sessions, manage your dashboard, saved addresses, and wishlists.</li>
                            <li>To send important order notifications, OTP verification codes, and optional newsletters.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">3. Payment & Data Security</h2>
                        <p>Payment information is processed via encrypted, industry-standard gateways (such as Stripe). We do not store raw credit card numbers on our servers. All sensitive data transmission is protected with SSL/TLS encryption.</p>
                    </section>

                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">4. Cookies & Analytics</h2>
                        <p>We utilize cookies to maintain your shopping cart, preserve your theme preferences, and analyze aggregated site visit traffic to enhance our browsing and shopping experience.</p>
                    </section>

                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">5. Third-Party Sharing</h2>
                        <p>We do not sell, rent, or trade your personal information. Data is shared strictly with trusted logistics partners (couriers) and payment providers for the sole purpose of delivering your orders.</p>
                    </section>

                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">6. Your Rights & Contact</h2>
                        <p>You have the right to access, update, or request deletion of your personal data at any time from your account profile. For privacy-related inquiries, reach out through our <a href="{{ route('contact') }}" class="text-brand-700 dark:text-brand-400 font-semibold hover:underline">Contact Page</a>.</p>
                    </section>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
