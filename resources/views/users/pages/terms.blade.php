@extends('users.layouts.app')
@section('title', 'Terms & Conditions — ' . ($siteName ?? 'GlowHaus'))

@section('content')
<div class="min-h-screen bg-cream-50/50 dark:bg-gray-950 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-brand-700 transition">Home</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-gray-200 font-semibold">Terms & Conditions</span>
        </nav>

        {{-- Header Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 sm:p-12 border border-cream-200 dark:border-gray-800 shadow-sm mb-8 text-center relative overflow-hidden">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-400 mb-4 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Terms & Conditions</h1>
            <p class="text-xs text-gray-500 mt-2">Last Updated: {{ date('F Y') }} · Effective for all customers of {{ $siteName ?? 'GlowHaus' }}</p>
        </div>

        {{-- Content Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 sm:p-12 border border-cream-200 dark:border-gray-800 shadow-sm leading-relaxed text-gray-700 dark:text-gray-300">
            @if(!empty($content))
                <div class="rich-content prose dark:prose-invert max-w-none text-base leading-relaxed">
                    @if(strip_tags($content) !== $content)
                        {!! $content !!}
                    @else
                        <div class="whitespace-pre-line space-y-4">
                            {!! nl2br(e($content)) !!}
                        </div>
                    @endif
                </div>
            @else
                <div class="space-y-8 text-sm">
                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">1. Agreement to Terms</h2>
                        <p>Welcome to {{ $siteName }}. By accessing or placing an order through our website, you agree to be bound by these Terms & Conditions. Please read them carefully prior to purchasing products.</p>
                    </section>

                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">2. Products & Authenticity</h2>
                        <p>All beauty products, cosmetics, fragrances, and skincare formulations available in our store are 100% authentic, curated from certified luxury suppliers and authorized distributors. Product descriptions and imagery are provided with high accuracy; slight shade differences may occur across different digital displays.</p>
                    </section>

                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">3. Orders, Pricing & Payment</h2>
                        <p>All prices displayed are inclusive of statutory taxes unless stated otherwise. We reserve the right to correct pricing errors before processing orders. Payments can be completed through Cash on Delivery (COD), online credit/debit cards, and verified payment gateways.</p>
                    </section>

                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">4. Delivery & Shipping</h2>
                        <p>Orders are dispatched with care within 24 to 48 hours of confirmation. Delivery times vary by location. Shipping fees are calculated at checkout based on destination and delivery options chosen.</p>
                    </section>

                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">5. Return & Refund Policy</h2>
                        <p>Once an order is completed, customers may submit a return or refund request from their account Order Details page within our return window. Customers must provide the specific reason for requesting a return or refund. Approved returns and accepted refunds will be processed according to our policy guidelines.</p>
                    </section>

                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">6. Customer Reviews</h2>
                        <p>Verified purchasers of completed orders may submit product reviews from their order page. All reviews reflect genuine experiences and may be published on the respective product details pages.</p>
                    </section>

                    <section>
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white mb-3">7. Contact & Inquiries</h2>
                        <p>For questions or assistance regarding these terms, please contact our concierge team via our <a href="{{ route('contact') }}" class="text-brand-700 dark:text-brand-400 font-semibold hover:underline">Contact Page</a>.</p>
                    </section>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
