@extends('users.layouts.app')
@section('title', 'Contact Us & FAQs')

@php
    $siteName = $settings['site_name'] ?? 'GlowHaus Beauty';
    $contactEmail = $settings['contact_email'] ?? 'hello@glowhaus.com';
    $contactPhone = $settings['contact_phone'] ?? '+880 1700-000000';
    $contactAddress = $settings['contact_address'] ?? 'Gulshan-2, Dhaka, Bangladesh';
@endphp

@section('content')
<div class="min-h-screen bg-cream-50/50 dark:bg-gray-950 py-12" x-data="{
    step: 'form', // 'form', 'otp', 'success'
    loading: false,
    errorMessage: '',
    successMessage: '',
    timer: 60,
    timerInterval: null,
    canResend: false,
    form: {
        name: '{{ old('name', $user?->name ?? '') }}',
        email: '{{ old('email', $user?->email ?? '') }}',
        subject: '{{ old('subject', '') }}',
        message: '{{ old('message', '') }}',
        otp: ''
    },
    startTimer() {
        this.timer = 60;
        this.canResend = false;
        if (this.timerInterval) clearInterval(this.timerInterval);
        this.timerInterval = setInterval(() => {
            if (this.timer > 0) {
                this.timer--;
            } else {
                this.canResend = true;
                clearInterval(this.timerInterval);
            }
        }, 1000);
    },
    async requestOtp() {
        this.errorMessage = '';
        if (!this.form.name.trim() || !this.form.email.trim() || !this.form.subject.trim() || !this.form.message.trim()) {
            this.errorMessage = 'Please complete all required fields.';
            return;
        }
        this.loading = true;
        try {
            const res = await fetch('{{ route('contact.send-otp') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: this.form.name,
                    email: this.form.email,
                    subject: this.form.subject,
                    message: this.form.message
                })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                this.step = 'otp';
                this.startTimer();
            } else {
                this.errorMessage = data.message || 'Failed to send verification code. Please check your inputs.';
            }
        } catch (e) {
            this.errorMessage = 'An unexpected error occurred. Please try again.';
        } finally {
            this.loading = false;
        }
    },
    async resendOtp() {
        if (!this.canResend) return;
        this.errorMessage = '';
        this.loading = true;
        try {
            const res = await fetch('{{ route('contact.resend-otp') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (res.ok && data.success) {
                this.startTimer();
            } else {
                this.errorMessage = data.message || 'Failed to resend code.';
            }
        } catch (e) {
            this.errorMessage = 'Failed to resend code.';
        } finally {
            this.loading = false;
        }
    },
    async submitInquiry() {
        this.errorMessage = '';
        if (!this.form.otp || this.form.otp.length !== 6) {
            this.errorMessage = 'Please enter the complete 6-digit verification code.';
            return;
        }
        this.loading = true;
        try {
            const res = await fetch('{{ route('contact.submit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    otp: this.form.otp
                })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                this.step = 'success';
                this.successMessage = data.message;
            } else {
                this.errorMessage = data.message || 'Invalid verification code.';
            }
        } catch (e) {
            this.errorMessage = 'An error occurred while submitting your message.';
        } finally {
            this.loading = false;
        }
    }
}">
    <div class="max-w-7xl mx-auto px-4">
        {{-- Hero Header --}}
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-widest text-brand-700 dark:text-brand-400">✦ Client Concierge & Support ✦</span>
            <h1 class="text-3xl md:text-4xl font-serif font-bold text-gray-900 dark:text-white mt-2">
                We're Here to Elevate Your Experience
            </h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm mt-3 leading-relaxed">
                Have a question about an order, our formulas, or need account assistance? Verify your email below to send a direct message to our support team.
            </p>
        </div>

        {{-- Contact Grid: Info + Form --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-20">
            {{-- Contact Information Column --}}
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 border border-cream-200 dark:border-gray-800 shadow-sm relative overflow-hidden">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-brand-50 dark:bg-brand-950/20 rounded-full blur-2xl pointer-events-none"></div>

                    <h2 class="font-serif font-bold text-xl text-gray-900 dark:text-white mb-6">Concierge Channels</h2>

                    <div class="space-y-6 text-sm">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Email Inquiry</p>
                                <a href="mailto:{{ $contactEmail }}" class="font-semibold text-gray-900 dark:text-white hover:text-brand-700 dark:hover:text-brand-400 transition">
                                    {{ $contactEmail }}
                                </a>
                                <p class="text-xs text-gray-400 mt-0.5">Replies typically within 2-4 business hours.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Direct Hotline</p>
                                <a href="tel:{{ $contactPhone }}" class="font-semibold text-gray-900 dark:text-white hover:text-brand-700 dark:hover:text-brand-400 transition">
                                    {{ $contactPhone }}
                                </a>
                                <p class="text-xs text-gray-400 mt-0.5">Saturday – Thursday: 10:00 AM – 8:00 PM</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Boutique & Showroom</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $contactAddress }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Blocked Account Notice --}}
                    <div class="mt-8 p-4 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50">
                        <div class="flex items-start gap-3">
                            <span class="text-amber-600 dark:text-amber-400 text-lg">🔒</span>
                            <div class="text-xs leading-relaxed text-amber-900 dark:text-amber-200">
                                <strong class="block font-semibold">Account Blocked or Suspended?</strong>
                                You can use this verified contact form without logging in. Enter your account email, verify the 6-digit OTP, and submit your inquiry so our administrators can review and assist you.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Interactive Contact Form with OTP Verification --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 sm:p-10 border border-cream-200 dark:border-gray-800 shadow-sm relative">
                    {{-- Error banner --}}
                    <div x-show="errorMessage" x-cloak class="mb-6 p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-xs text-red-700 dark:text-red-300 flex items-center justify-between">
                        <span x-text="errorMessage"></span>
                        <button type="button" @click="errorMessage = ''" class="text-red-400 hover:text-red-600">&times;</button>
                    </div>

                    {{-- Step 1: Initial Form --}}
                    <div x-show="step === 'form'" x-transition>
                        <h2 class="font-serif font-bold text-xl text-gray-900 dark:text-white mb-2">Send Us a Verified Message</h2>
                        <p class="text-xs text-gray-500 mb-6">
                            For security and authenticity, an OTP will be sent to your email to verify your inquiry before submission.
                        </p>

                        <form @submit.prevent="requestOtp()" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Your Name *</label>
                                    <input type="text" x-model="form.name" required placeholder="e.g., Alexandra Vance"
                                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-cream-300 dark:border-gray-700 bg-cream-50/30 dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Email Address *</label>
                                    <input type="email" x-model="form.email" required placeholder="name@example.com"
                                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-cream-300 dark:border-gray-700 bg-cream-50/30 dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Subject *</label>
                                <input type="text" x-model="form.subject" required placeholder="e.g., Order Inquiry #ORD-XXXX or Account Assistance"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-cream-300 dark:border-gray-700 bg-cream-50/30 dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Message *</label>
                                <textarea x-model="form.message" rows="5" required placeholder="Provide details regarding your request..."
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-cream-300 dark:border-gray-700 bg-cream-50/30 dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                            </div>

                            <button type="submit" :disabled="loading"
                                class="w-full py-3 bg-brand-700 hover:bg-brand-800 text-white rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2 shadow-sm disabled:opacity-50">
                                <template x-if="loading">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                </template>
                                <span>Verify Email & Send Code →</span>
                            </button>
                        </form>
                    </div>

                    {{-- Step 2: OTP Verification Screen --}}
                    <div x-show="step === 'otp'" x-cloak x-transition class="text-center py-4">
                        <div class="w-14 h-14 rounded-2xl bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-400 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h2 class="font-serif font-bold text-xl text-gray-900 dark:text-white">Verify Your Email Address</h2>
                        <p class="text-xs text-gray-500 mt-2 max-w-md mx-auto">
                            We've dispatched a 6-digit verification code to <strong class="text-gray-900 dark:text-white" x-text="form.email"></strong>. Please enter the code below to confirm your message.
                        </p>

                        <div class="my-6 max-w-xs mx-auto">
                            <input type="text" x-model="form.otp" maxlength="6" autofocus placeholder="••••••"
                                class="w-full text-center tracking-[0.5em] font-mono text-2xl font-bold py-3 px-4 rounded-xl border border-cream-300 dark:border-gray-700 bg-cream-50/50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                            <button @click="submitInquiry()" :disabled="loading || form.otp.length !== 6" type="button"
                                class="w-full sm:w-auto px-8 py-2.5 bg-brand-700 hover:bg-brand-800 text-white rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2 shadow-sm disabled:opacity-50">
                                <template x-if="loading">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                </template>
                                <span>Confirm & Submit Message</span>
                            </button>

                            <button @click="step = 'form'" type="button" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 py-2">
                                Edit Form
                            </button>
                        </div>

                        <div class="mt-6 text-xs text-gray-400">
                            <template x-if="!canResend">
                                <span>Resend code in <strong x-text="timer + 's'"></strong></span>
                            </template>
                            <template x-if="canResend">
                                <button @click="resendOtp()" type="button" class="text-brand-700 dark:text-brand-400 font-semibold hover:underline">
                                    Resend Verification Code
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Step 3: Success Screen --}}
                    <div x-show="step === 'success'" x-cloak x-transition class="text-center py-8">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h2 class="font-serif font-bold text-2xl text-gray-900 dark:text-white">Message Verified & Sent</h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 max-w-md mx-auto" x-text="successMessage"></p>
                        <div class="mt-8">
                            <button @click="step = 'form'; form.subject = ''; form.message = ''; form.otp = '';" type="button"
                                class="px-6 py-2.5 rounded-xl border border-cream-300 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-cream-100 dark:hover:bg-gray-800 transition">
                                Send Another Message
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FAQs Section --}}
        <div class="mt-20 border-t border-cream-200 dark:border-gray-800 pt-16">
            <div class="text-center max-w-xl mx-auto mb-10">
                <span class="text-xs font-bold uppercase tracking-widest text-brand-700 dark:text-brand-400">✦ Answers & Knowledge ✦</span>
                <h2 class="text-2xl md:text-3xl font-serif font-bold text-gray-900 dark:text-white mt-1">
                    Frequently Asked Questions
                </h2>
                <p class="text-gray-500 text-xs mt-2">
                    Instant answers regarding shipping, payments, authenticity, and account access.
                </p>
            </div>

            <div class="max-w-3xl mx-auto space-y-4" x-data="{ activeAccordion: null }">
                @forelse($faqs as $faq)
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-cream-200 dark:border-gray-800 overflow-hidden shadow-sm transition">
                        <button type="button"
                            @click="activeAccordion = (activeAccordion === {{ $faq->id }} ? null : {{ $faq->id }})"
                            class="w-full text-left px-6 py-4 flex items-center justify-between gap-4 font-semibold text-sm text-gray-900 dark:text-white hover:text-brand-700 dark:hover:text-brand-400 transition">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cream-100 text-brand-800 dark:bg-gray-800 dark:text-brand-300">
                                    {{ $faq->category }}
                                </span>
                                <span>{{ $faq->question }}</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-300 shrink-0"
                                :class="activeAccordion === {{ $faq->id }} ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="activeAccordion === {{ $faq->id }}" x-cloak x-collapse>
                            <div class="px-6 pb-5 pt-1 text-xs text-gray-600 dark:text-gray-400 leading-relaxed border-t border-cream-100 dark:border-gray-800/60">
                                {{ $faq->answer }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-400 text-xs">
                        No FAQs currently published.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
