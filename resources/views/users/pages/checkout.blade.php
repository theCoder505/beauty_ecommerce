@extends('users.layouts.app')
@php
    $settings = app(\App\Services\SettingService::class)->all();
    $currency = $settings['currency_symbol'] ?? '৳';
    $codEnabled    = ($settings['cod_enabled'] ?? '0') == '1';
    $manualEnabled = ($settings['manual_payment_enabled'] ?? '0') == '1';
    $stripeEnabled = ($settings['stripe_enabled'] ?? '0') == '1';
    $manualInstr   = $settings['manual_payment_instructions'] ?? '';
    $stripePk      = $settings['stripe_publishable_key'] ?? '';
@endphp
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10" x-data="{ paymentMethod: '{{ $codEnabled ? 'cod' : ($manualEnabled ? 'manual' : 'stripe') }}' }">
    <h1 class="font-serif text-3xl font-bold text-gray-900 dark:text-white mb-8">Checkout</h1>
    @if($errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl px-5 py-4 mb-6 text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('checkout.place') }}" method="POST" id="checkout-form">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ session()->getId() . '_' . time() }}">
        <input type="hidden" name="payment_method" :value="paymentMethod">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left: Shipping + Payment --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Shipping --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-cream-200 dark:border-gray-700">
                    <h2 class="font-semibold text-lg text-gray-900 dark:text-white mb-5">Shipping Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Full Name *</label>
                            <input type="text" name="shipping_name" value="{{ old('shipping_name', auth()->user()->name) }}" required class="input-field">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Phone *</label>
                            <input type="text" name="shipping_phone" value="{{ old('shipping_phone', auth()->user()->phone) }}" required class="input-field">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Email *</label>
                            <input type="email" name="shipping_email" value="{{ old('shipping_email', auth()->user()->email) }}" required class="input-field">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Address *</label>
                            <input type="text" name="shipping_address" value="{{ old('shipping_address') }}" required placeholder="Street address, apartment, etc." class="input-field">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">City *</label>
                            <input type="text" name="shipping_city" value="{{ old('shipping_city') }}" required class="input-field">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">State/Division</label>
                            <input type="text" name="shipping_state" value="{{ old('shipping_state') }}" class="input-field">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Postal Code</label>
                            <input type="text" name="shipping_zip" value="{{ old('shipping_zip') }}" class="input-field">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Country</label>
                            <input type="text" name="shipping_country" value="{{ old('shipping_country', 'BD') }}" class="input-field">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Order Notes (optional)</label>
                            <textarea name="notes" rows="2" class="input-field" placeholder="Any special instructions…">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Payment --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-cream-200 dark:border-gray-700">
                    <h2 class="font-semibold text-lg text-gray-900 dark:text-white mb-5">Payment Method</h2>
                    <div class="space-y-3">
                        @if($codEnabled)
                        <label class="flex items-start gap-4 cursor-pointer p-4 rounded-xl border-2 transition" :class="paymentMethod === 'cod' ? 'border-brand-700 bg-brand-50 dark:bg-brand-900/20' : 'border-cream-200 dark:border-gray-600'">
                            <input type="radio" value="cod" x-model="paymentMethod" class="mt-0.5 text-brand-700">
                            <div>
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">Cash on Delivery</p>
                                <p class="text-xs text-gray-500 mt-0.5">Pay when your order arrives</p>
                            </div>
                        </label>
                        @endif

                        @if($manualEnabled)
                        <label class="flex items-start gap-4 cursor-pointer p-4 rounded-xl border-2 transition" :class="paymentMethod === 'manual' ? 'border-brand-700 bg-brand-50 dark:bg-brand-900/20' : 'border-cream-200 dark:border-gray-600'">
                            <input type="radio" value="manual" x-model="paymentMethod" class="mt-0.5 text-brand-700">
                            <div class="flex-1">
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">Manual Bank Transfer</p>
                                <div x-show="paymentMethod === 'manual'" class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $manualInstr }}</div>
                                <div x-show="paymentMethod === 'manual'" class="mt-3">
                                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Transaction Number *</label>
                                    <input type="text" name="transaction_id" placeholder="e.g. TXN123456789" class="input-field" :required="paymentMethod === 'manual'">
                                </div>
                            </div>
                        </label>
                        @endif

                        @if($stripeEnabled)
                        <label class="flex items-start gap-4 cursor-pointer p-4 rounded-xl border-2 transition" :class="paymentMethod === 'stripe' ? 'border-brand-700 bg-brand-50 dark:bg-brand-900/20' : 'border-cream-200 dark:border-gray-600'">
                            <input type="radio" value="stripe" x-model="paymentMethod" class="mt-0.5 text-brand-700">
                            <div>
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">Card Payment (Stripe)</p>
                                <p class="text-xs text-gray-500 mt-0.5">Secure online payment via Stripe</p>
                            </div>
                        </label>
                        @endif

                        @if(!$codEnabled && !$manualEnabled && !$stripeEnabled)
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl text-amber-700 dark:text-amber-400 text-sm">
                            No payment methods are currently available. Please contact us.
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Order Summary --}}
            <div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-cream-200 dark:border-gray-700 sticky top-20">
                    <h2 class="font-semibold text-gray-900 dark:text-white mb-5">Order Summary</h2>
                    <div class="space-y-3 mb-5">
                        @foreach($cartItems as $item)
                        <div class="flex gap-3 items-center">
                            <img src="{{ $item->product->thumbnail_url }}" class="w-12 h-12 rounded-lg object-cover shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $item->product->name }}</p>
                                <p class="text-xs text-gray-400">× {{ $item->quantity }}</p>
                            </div>
                            <p class="text-xs font-bold text-gray-900 dark:text-white shrink-0">{{ $currency }}{{ number_format($item->line_total) }}</p>
                        </div>
                        @endforeach
                    </div>
                    <div class="border-t border-cream-200 dark:border-gray-700 pt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between"><span>Subtotal</span><span class="font-semibold text-gray-900 dark:text-white">{{ $currency }}{{ number_format($cartTotal) }}</span></div>
                        <div class="flex justify-between"><span>Shipping</span><span class="text-green-600 font-semibold">{{ $cartTotal >= 1000 ? 'Free' : $currency.'60' }}</span></div>
                        <div class="border-t border-cream-200 dark:border-gray-700 pt-2 flex justify-between font-bold text-base text-gray-900 dark:text-white">
                            <span>Total</span><span>{{ $currency }}{{ number_format($cartTotal + ($cartTotal >= 1000 ? 0 : 60)) }}</span>
                        </div>
                    </div>
                    <button type="submit" class="mt-6 w-full bg-brand-700 text-white font-bold py-3.5 rounded-xl hover:bg-brand-800 transition shadow-lg shadow-brand-700/25">
                        Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
.input-field {
    @apply w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 text-gray-900 dark:text-white;
}
</style>
@endpush
@endsection

