@extends('users.layouts.app')
@php
    $settings = app(\App\Services\SettingService::class)->all();
    $currency = $settings['currency_symbol'] ?? '৳';

    // Payment methods enabled logic matching SettingController & CheckoutController
    $codEnabled = ($settings['cod_enabled'] ?? '1') == '1' || !empty($settings['payment_cod_enabled']);
    $manualEnabled = ($settings['manual_payment_enabled'] ?? '1') == '1' || !empty($settings['payment_bank_transfer_enabled']);
    $stripeEnabled = ($settings['stripe_enabled'] ?? '0') == '1' || !empty($settings['payment_stripe_enabled']);
    $manualInstr = $settings['manual_payment_instructions'] ?? '';

    $cartItems = $cartItems ?? ($items ?? collect());
    $cartTotal = $cartTotal ?? ($total ?? 0);
    $addresses = $addresses ?? (auth()->check() ? auth()->user()->addresses : collect());
    $primaryAddress = $primaryAddress ?? ($addresses->firstWhere('is_primary', true) ?? $addresses->first());
    $defaultAddrId = $primaryAddress ? $primaryAddress->id : ($addresses->first()?->id ?? null);
    $deliveryCharges = $deliveryCharges ?? app(\App\Services\SettingService::class)->getDeliveryCharges();
    $defaultDelivery = $defaultDelivery ?? (collect($deliveryCharges)->firstWhere('is_default', true) ?? ($deliveryCharges[0] ?? ['id' => 'inside_dhaka', 'title' => 'Inside Dhaka', 'amount' => 60]));

    // Determine initial selected payment method
    $initialMethod = 'cod';
    if ($codEnabled) {
        $initialMethod = 'cod';
    } elseif ($manualEnabled) {
        $initialMethod = 'manual';
    } elseif ($stripeEnabled) {
        $initialMethod = 'stripe';
    }
@endphp

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8 sm:py-12" x-data="checkoutManager({
        paymentMethod: @js($initialMethod),
        addresses: @js($addresses),
        selectedAddressId: @js($defaultAddrId),
        deliveryCharges: @js($deliveryCharges),
        defaultDelivery: @js($defaultDelivery),
        subtotal: {{ (float) $cartTotal }},
        currency: @js($currency),
        checkoutUrl: @js(route('checkout.place')),
        applyCouponUrl: @js(route('checkout.apply-coupon')),
        ordersUrl: @js(route('user.orders')),
        csrfToken: @js(csrf_token()),
        isBuyNow: {{ !empty($isBuyNow) ? 'true' : 'false' }},
        buyNowProductId: {{ !empty($isBuyNow) && $items->isNotEmpty() ? $items->first()->product_id : 'null' }},
        buyNowQuantity: {{ !empty($isBuyNow) && $items->isNotEmpty() ? $items->first()->quantity : 'null' }}
    })">
        {{-- Header --}}
        <div class="mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 dark:bg-gray-800/90 dark:text-brand-300 border border-brand-200/60 dark:border-brand-800/50 mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-600 animate-pulse"></span>
                <span>Final Step • Fast & Secure Checkout</span>
            </div>
            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Checkout</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">Select your saved delivery address, choose your delivery zone, and complete your order seamlessly.</p>
        </div>

        {{-- AJAX & Server Error Banner --}}
        <div x-show="errorMessage" x-cloak class="mb-6 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-2xl p-4 sm:p-5 shadow-xs transition-all">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <h3 class="font-bold text-sm" x-text="errorMessage"></h3>
                    <template x-if="errorList.length > 0">
                        <ul class="list-disc list-inside mt-1.5 space-y-0.5 text-xs text-red-600 dark:text-red-400">
                            <template x-for="(err, idx) in errorList" :key="idx">
                                <li x-text="err"></li>
                            </template>
                        </ul>
                    </template>
                </div>
                <button type="button" @click="errorMessage = ''; errorList = []" class="text-red-400 hover:text-red-600 text-sm font-bold p-1">✕</button>
            </div>
        </div>

        @if (isset($errors) && $errors->any())
            <div class="mb-6 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-2xl p-4 sm:p-5 text-sm shadow-xs">
                <div class="flex items-center gap-2 font-semibold mb-1">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Please check the following:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form @submit.prevent="submitCheckout()" id="checkout-form">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                {{-- Left 2 Columns: Shipping Address + Delivery Charge + Payment + Notes --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- 1. Delivery Address Selection (Only saved addresses, no manual address inputs) --}}
                    <div class="bg-white dark:bg-gray-800/90 rounded-2xl p-6 sm:p-7 border border-stone-200/90 dark:border-gray-700 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-5 border-b border-stone-100 dark:border-gray-700/80">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-full bg-brand-700 text-white font-bold text-xs flex items-center justify-center shadow-xs">1</span>
                                <div>
                                    <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-white">Delivery Address</h2>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Select where you want your order delivered</p>
                                </div>
                            </div>
                            <a href="{{ route('user.profile') }}" target="_blank" class="text-xs font-semibold text-brand-700 dark:text-brand-400 hover:underline flex items-center gap-1 self-start sm:self-auto py-1 px-2.5 rounded-lg bg-stone-50 dark:bg-gray-700/60 hover:bg-stone-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <span>Manage Addresses in Profile</span>
                                <span>→</span>
                            </a>
                        </div>

                        {{-- Saved Address Cards --}}
                        @if ($addresses->isNotEmpty())
                            <div class="space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    @foreach ($addresses as $addr)
                                        <div @click="selectedAddressId = {{ $addr->id }}"
                                            class="relative p-4 rounded-2xl border-2 transition-all duration-200 cursor-pointer flex flex-col justify-between"
                                            :class="selectedAddressId === {{ $addr->id }} ?
                                                'active-checkout-card shadow-sm' :
                                                'inactive-checkout-card hover:border-brand-400 dark:hover:border-gray-500'">

                                            <div class="flex items-start justify-between gap-2 mb-2.5">
                                                <div class="flex items-center gap-2">
                                                    <input type="radio"
                                                           name="address_choice"
                                                           value="{{ $addr->id }}"
                                                           :checked="selectedAddressId === {{ $addr->id }}"
                                                           @change="selectedAddressId = {{ $addr->id }}"
                                                           class="text-brand-700 focus:ring-brand-700 h-4 w-4">
                                                    <span class="text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md {{ strtolower($addr->label) === 'home' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' }}">
                                                        {{ $addr->label }}
                                                    </span>
                                                </div>
                                                @if ($addr->is_primary)
                                                    <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-200/80 dark:bg-emerald-950/70 px-2 py-0.5 rounded-md flex items-center gap-1 border border-emerald-300 dark:border-emerald-800">
                                                        ★ Primary
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between text-xs">
                                                    <p class="font-bold text-gray-900 dark:text-white truncate">
                                                        {{ $addr->recipient_name ?: auth()->user()?->name }}
                                                    </p>
                                                    <span class="font-mono text-gray-600 dark:text-gray-300 text-[11px] font-semibold">
                                                        {{ $addr->phone ?: auth()->user()?->phone }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-800 dark:text-gray-200 font-medium leading-relaxed line-clamp-2">
                                                    {{ $addr->address_line }}
                                                </p>
                                                <p class="text-[11px] text-gray-600 dark:text-gray-400">
                                                    {{ $addr->city }}{{ $addr->state ? ', ' . $addr->state : '' }}{{ $addr->postal_code ? ' - ' . $addr->postal_code : '' }}
                                                </p>
                                            </div>

                                            {{-- Active Deliver-here indicator --}}
                                            <div class="mt-3 pt-2 border-t border-brand-200/70 dark:border-gray-700/80 flex items-center justify-between text-[11px]">
                                                <span :class="selectedAddressId === {{ $addr->id }} ? 'text-brand-800 dark:text-brand-300 font-bold flex items-center gap-1' : 'text-gray-400'">
                                                    <span x-show="selectedAddressId === {{ $addr->id }}" class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-brand-700 dark:text-brand-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span>Selected to Receive</span>
                                                    </span>
                                                    <span x-show="selectedAddressId !== {{ $addr->id }}">Click to receive here</span>
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            {{-- No Address Saved Warning with Direct Profile Link --}}
                            <div class="p-6 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border-2 border-dashed border-amber-300 dark:border-amber-800 text-center">
                                <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-1">No Delivery Address Found</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400 max-w-md mx-auto mb-4">
                                    You don't have any saved delivery address in your account. Please add your delivery address with Google Maps in your profile to complete this purchase.
                                </p>
                                <a href="{{ route('user.profile') }}" target="_blank"
                                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs shadow-md transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <span>Add Delivery Address in Profile</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- 2. Delivery Zone & Shipping Charge (User MUST choose one) --}}
                    <div class="bg-white dark:bg-gray-800/90 rounded-2xl p-6 sm:p-7 border border-stone-200/90 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center justify-between pb-4 mb-5 border-b border-stone-100 dark:border-gray-700/80">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-full bg-brand-700 text-white font-bold text-xs flex items-center justify-center shadow-xs">2</span>
                                <div>
                                    <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-white">Delivery Zone & Charge</h2>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Select your destination area</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 px-2.5 py-1 rounded-md border border-brand-200/50 dark:border-brand-800/50">
                                Required *
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <template x-for="(charge, index) in deliveryCharges" :key="charge.id || index">
                                    <div @click="selectDelivery(charge)"
                                         class="p-4 rounded-2xl border-2 transition-all duration-200 cursor-pointer flex items-center justify-between"
                                         :class="selectedDeliveryId === charge.id ?
                                             'active-checkout-card shadow-sm' :
                                             'inactive-checkout-card hover:border-brand-400 dark:hover:border-gray-500'">
                                        <div class="flex items-center gap-3">
                                            <input type="radio"
                                                   name="delivery_selection"
                                                   :value="charge.id"
                                                   :checked="selectedDeliveryId === charge.id"
                                                   class="text-brand-700 focus:ring-brand-700 h-4 w-4">
                                            <div>
                                                <p class="font-bold text-sm text-gray-900 dark:text-white" x-text="charge.title"></p>
                                                <p class="text-[11px] text-gray-600 dark:text-gray-300">Doorstep express courier</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-serif font-bold text-base text-brand-800 dark:text-brand-300"
                                                  x-text="currency + Number(charge.amount).toFixed(0)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Payment Method Selection --}}
                    <div class="bg-white dark:bg-gray-800/90 rounded-2xl p-6 sm:p-7 border border-stone-200/90 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-stone-100 dark:border-gray-700/80">
                            <span class="w-7 h-7 rounded-full bg-brand-700 text-white font-bold text-xs flex items-center justify-center shadow-xs">3</span>
                            <div>
                                <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-white">Payment Method</h2>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Choose your preferred payment gateway</p>
                            </div>
                        </div>

                        <div class="space-y-3.5">
                            {{-- COD --}}
                            @if ($codEnabled)
                                <label class="flex items-start gap-4 cursor-pointer p-4 rounded-2xl border-2 transition-all duration-200"
                                    :class="paymentMethod === 'cod' ?
                                        'active-checkout-card shadow-sm' :
                                        'inactive-checkout-card hover:border-brand-400 dark:hover:border-gray-500'">
                                    <input type="radio" value="cod" x-model="paymentMethod" class="mt-1 text-brand-700 focus:ring-brand-700 h-4 w-4">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="font-bold text-sm text-gray-900 dark:text-white">Cash on Delivery (COD)</p>
                                            <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-200/80 dark:bg-emerald-950/70 border border-emerald-300 dark:border-emerald-800 px-2 py-0.5 rounded-md">Popular</span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">Pay with cash upon physical delivery of your order right at your doorstep.</p>
                                    </div>
                                </label>
                            @endif

                            {{-- Manual Bank Transfer / Mobile Banking --}}
                            @if ($manualEnabled)
                                <label class="flex items-start gap-4 cursor-pointer p-4 rounded-2xl border-2 transition-all duration-200"
                                    :class="paymentMethod === 'manual' ?
                                        'active-checkout-card shadow-sm' :
                                        'inactive-checkout-card hover:border-brand-400 dark:hover:border-gray-500'">
                                    <input type="radio" value="manual" x-model="paymentMethod" class="mt-1 text-brand-700 focus:ring-brand-700 h-4 w-4">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="font-bold text-sm text-gray-900 dark:text-white">Mobile Banking / Bank Transfer (bKash / Nagad / Rocket)</p>
                                            <span class="text-[10px] font-bold text-blue-800 dark:text-blue-300 bg-blue-100 dark:bg-blue-950/70 border border-blue-200 dark:border-blue-800 px-2 py-0.5 rounded-md">Instant</span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">Transfer directly to our account and submit the Transaction ID below.</p>

                                        {{-- Manual Payment Instructions & Transaction ID Input with High-End Border Design --}}
                                        <div x-show="paymentMethod === 'manual'" x-cloak class="mt-3.5 space-y-3">
                                            @if (!empty($manualInstr))
                                                <div class="p-3.5 bg-white/95 dark:bg-gray-900/90 border border-amber-300 dark:border-amber-700/80 rounded-xl text-xs text-amber-950 dark:text-amber-200 leading-relaxed whitespace-pre-line shadow-xs">{{ $manualInstr }}</div>
                                            @endif
                                            <div>
                                                <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5 uppercase tracking-wide">
                                                    Transaction ID / Reference Number <span class="text-red-500">*</span>
                                                </label>
                                                <div class="relative">
                                                    <input type="text"
                                                           x-model="transactionId"
                                                           placeholder="e.g. 9J4K7LM2 or TRX-123456"
                                                           class="custom-form-input pl-10"
                                                           :required="paymentMethod === 'manual'">
                                                    <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endif

                            {{-- Stripe Online Payment --}}
                            @if ($stripeEnabled)
                                <label class="flex items-start gap-4 cursor-pointer p-4 rounded-2xl border-2 transition-all duration-200"
                                    :class="paymentMethod === 'stripe' ?
                                        'active-checkout-card shadow-sm' :
                                        'inactive-checkout-card hover:border-brand-400 dark:hover:border-gray-500'">
                                    <input type="radio" value="stripe" x-model="paymentMethod" class="mt-1 text-brand-700 focus:ring-brand-700 h-4 w-4">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="font-bold text-sm text-gray-900 dark:text-white">Credit / Debit Card (Stripe)</p>
                                            <div class="flex items-center gap-1 text-[10px] font-bold text-gray-400">
                                                <span class="bg-stone-200 dark:bg-gray-700 px-1.5 py-0.5 rounded font-mono">VISA</span>
                                                <span class="bg-stone-200 dark:bg-gray-700 px-1.5 py-0.5 rounded font-mono">MC</span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">Instant, secure and encrypted checkout with Visa, MasterCard, or American Express.</p>
                                    </div>
                                </label>
                            @endif

                            @if (!$codEnabled && !$manualEnabled && !$stripeEnabled)
                                <div class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl text-amber-700 dark:text-amber-400 text-xs">
                                    No payment gateways are currently enabled. Please contact support.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 4. Order Notes (Optional) --}}
                    <div class="bg-white dark:bg-gray-800/90 rounded-2xl p-6 sm:p-7 border border-stone-200/90 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-2.5 pb-4 mb-4 border-b border-stone-100 dark:border-gray-700/80">
                            <span class="w-7 h-7 rounded-full bg-stone-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs flex items-center justify-center">4</span>
                            <div>
                                <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-white">Order Notes <span class="text-xs font-normal text-gray-400">(Optional)</span></h2>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Any special courier instructions or delivery requests</p>
                            </div>
                        </div>

                        <div>
                            <textarea x-model="notes" rows="2" class="custom-form-input resize-none" placeholder="e.g. Please call before arrival, deliver after 3 PM, or leave with reception..."></textarea>
                        </div>
                    </div>

                </div>

                {{-- Right Column: Order Summary & Promo Code (Sticky) --}}
                <div class="lg:sticky lg:top-24 space-y-6">
                    <div class="bg-white dark:bg-gray-800/90 rounded-2xl p-6 border border-stone-200/90 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-stone-100 dark:border-gray-700/80">
                            <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-white">Order Summary</h2>
                            @if (!empty($isBuyNow))
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 px-2.5 py-1 rounded-full">
                                    ⚡ Buy Now
                                </span>
                            @endif
                        </div>

                        @if (!empty($isBuyNow) && !empty($cartCount) && $cartCount > 0)
                            <div class="mb-4 p-3 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/40 text-xs text-gray-600 dark:text-gray-300 flex items-center justify-between gap-2">
                                <span>Purchasing this item only. ({{ $cartCount }} other in cart)</span>
                                <a href="{{ route('checkout.index') }}" class="text-brand-700 dark:text-brand-400 font-bold hover:underline shrink-0">Checkout All →</a>
                            </div>
                        @endif

                        {{-- Items List --}}
                        <div class="space-y-3.5 mb-5 max-h-56 overflow-y-auto pr-1">
                            @foreach ($cartItems as $item)
                                <div class="flex gap-3 items-center">
                                    <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-stone-200 dark:border-gray-700">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate">
                                            {{ $item->product->name }}
                                        </p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">
                                            Qty: {{ $item->quantity }} × {{ $currency }}{{ number_format($item->product->current_price) }}
                                        </p>
                                    </div>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white shrink-0">
                                        {{ $currency }}{{ number_format($item->line_total ?? ($item->quantity * ($item->product->current_price ?? 0))) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        {{-- Promo Code Section --}}
                        <div class="py-4 border-t border-b border-stone-100 dark:border-gray-700/80 space-y-2.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Have a Promo Code?
                            </label>

                            {{-- If coupon not applied yet --}}
                            <template x-if="!appliedCoupon">
                                <div class="space-y-2">
                                    <div class="flex gap-2">
                                        <input type="text"
                                               x-model="promoCodeInput"
                                               placeholder="Enter Code (e.g. SUMMER20)"
                                               @keydown.enter.prevent="applyCoupon()"
                                               @input="promoCodeInput = promoCodeInput.toUpperCase()"
                                               class="custom-form-input font-mono text-xs font-bold uppercase">
                                        <button type="button"
                                                @click="applyCoupon()"
                                                :disabled="!promoCodeInput.trim() || isApplyingCoupon"
                                                class="px-4 py-2 bg-brand-700 hover:bg-brand-800 disabled:opacity-50 text-white font-bold text-xs rounded-xl transition shadow-xs shrink-0 flex items-center gap-1.5">
                                            <span x-show="isApplyingCoupon" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                            <span x-text="isApplyingCoupon ? 'Checking…' : 'Apply'"></span>
                                        </button>
                                    </div>
                                    <p x-show="couponError" x-text="couponError" class="text-xs text-red-500 font-medium"></p>
                                </div>
                            </template>

                            {{-- If coupon successfully applied --}}
                            <template x-if="appliedCoupon">
                                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">✓</span>
                                        <div>
                                            <p class="text-xs font-bold font-mono text-emerald-800 dark:text-emerald-300" x-text="appliedCoupon.code"></p>
                                            <p class="text-[11px] text-emerald-600 dark:text-emerald-400" x-text="'Saved ' + currency + Number(appliedCoupon.discount).toFixed(0)"></p>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeCoupon()" class="text-gray-400 hover:text-red-500 text-sm p-1 rounded transition" title="Remove promo code">
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Financial Breakdown --}}
                        <div class="pt-4 space-y-2.5 text-xs text-gray-600 dark:text-gray-400">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span class="font-bold text-gray-900 dark:text-white" x-text="currency + Number(subtotal).toLocaleString()"></span>
                            </div>

                            {{-- Promo discount line --}}
                            <template x-if="appliedCoupon && discountAmount > 0">
                                <div class="flex justify-between items-center text-emerald-600 dark:text-emerald-400 font-bold">
                                    <span class="flex items-center gap-1">
                                        <span>Promo Discount</span>
                                        <span class="text-[10px] font-mono bg-emerald-100 dark:bg-emerald-950 px-1.5 py-0.5 rounded" x-text="'(' + appliedCoupon.code + ')'"></span>
                                    </span>
                                    <span x-text="'-' + currency + Number(discountAmount).toLocaleString()"></span>
                                </div>
                            </template>

                            {{-- Delivery Charge line --}}
                            <div class="flex justify-between items-center">
                                <div>
                                    <span>Delivery Charge</span>
                                    <span class="block text-[10px] text-gray-400" x-text="selectedDeliveryTitle"></span>
                                </div>
                                <span class="font-bold text-gray-900 dark:text-white"
                                      x-text="currency + Number(selectedDeliveryAmount).toLocaleString()">
                                </span>
                            </div>

                            <div class="border-t border-stone-200/90 dark:border-gray-700 pt-3 flex justify-between items-baseline font-bold text-base text-gray-900 dark:text-white">
                                <span>Total Payable</span>
                                <span class="font-serif text-xl text-brand-700 dark:text-brand-400"
                                      x-text="currency + Number(totalPayable).toLocaleString()">
                                </span>
                            </div>
                        </div>

                        {{-- Submit Button with AJAX Loading Indicator --}}
                        <button type="submit"
                                id="place-order-button"
                                :disabled="isSubmitting || !selectedAddressId"
                                class="mt-6 w-full bg-brand-700 hover:bg-brand-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3.5 px-4 rounded-xl transition shadow-lg shadow-brand-700/25 active:scale-[0.99] flex items-center justify-center gap-2 text-sm">
                            <svg x-show="isSubmitting" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-text="isSubmitting ? 'Processing Order...' : (selectedAddressId ? 'Place Order →' : 'Select an Address to Order')"></span>
                        </button>

                        <div class="mt-4 pt-3 border-t border-stone-100 dark:border-gray-700/60 flex items-center justify-center gap-3 text-[11px] text-gray-400 text-center">
                            <span class="flex items-center gap-1">🔒 256-bit SSL</span>
                            <span>•</span>
                            <span>✓ Verified Purchase</span>
                            <span>•</span>
                            <span>Instant Guarantee</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
        <style>
            .custom-form-input {
                width: 100%;
                padding: 0.75rem 1rem;
                border-radius: 0.75rem;
                border: 1.5px solid rgba(214, 211, 209, 0.9);
                background-color: #ffffff;
                font-size: 0.875rem;
                color: #111827;
                transition: all 0.2s ease-in-out;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            }

            .dark .custom-form-input {
                border-color: rgba(75, 85, 99, 0.8);
                background-color: rgba(17, 24, 39, 0.9);
                color: #f9fafb;
            }

            .custom-form-input:focus {
                outline: none;
                border-color: #15803d;
                box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.2);
            }

            .custom-form-input:hover:not(:focus) {
                border-color: rgba(168, 162, 158, 1);
            }

            .dark .custom-form-input:hover:not(:focus) {
                border-color: rgba(107, 114, 128, 1);
            }

            /* Prominent Active Checkout Cards */
            .active-checkout-card {
                border-color: #15803d !important;
                background-color: #dcfce7 !important;
                box-shadow: 0 4px 14px -1px rgba(21, 128, 61, 0.25), 0 0 0 2.5px rgba(21, 128, 61, 0.45) !important;
                transform: translateY(-1px);
            }

            .dark .active-checkout-card {
                border-color: #4ade80 !important;
                background-color: rgba(20, 83, 45, 0.75) !important;
                box-shadow: 0 4px 18px -1px rgba(0, 0, 0, 0.6), 0 0 0 2.5px rgba(74, 222, 128, 0.5) !important;
                transform: translateY(-1px);
            }

            .inactive-checkout-card {
                border-color: #e5e7eb;
                background-color: #ffffff;
            }

            .dark .inactive-checkout-card {
                border-color: #374151;
                background-color: rgba(31, 41, 55, 0.5);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function checkoutManager(config) {
                return {
                    paymentMethod: config.paymentMethod || 'cod',
                    addresses: config.addresses || [],
                    selectedAddressId: config.selectedAddressId || null,
                    deliveryCharges: config.deliveryCharges || [],
                    selectedDeliveryId: config.defaultDelivery?.id || (config.deliveryCharges?.[0]?.id || ''),
                    selectedDeliveryAmount: parseFloat(config.defaultDelivery?.amount || (config.deliveryCharges?.[0]?.amount || 0)),
                    selectedDeliveryTitle: config.defaultDelivery?.title || (config.deliveryCharges?.[0]?.title || 'Delivery'),
                    subtotal: parseFloat(config.subtotal || 0),
                    currency: config.currency || '৳',
                    promoCodeInput: '',
                    appliedCoupon: null,
                    isApplyingCoupon: false,
                    couponError: '',
                    transactionId: '',
                    notes: '',
                    isSubmitting: false,
                    errorMessage: '',
                    errorList: [],

                    get discountAmount() {
                        return this.appliedCoupon ? parseFloat(this.appliedCoupon.discount) : 0;
                    },

                    get totalPayable() {
                        return Math.max(0, this.subtotal - this.discountAmount) + this.selectedDeliveryAmount;
                    },

                    selectDelivery(charge) {
                        this.selectedDeliveryId = charge.id;
                        this.selectedDeliveryAmount = parseFloat(charge.amount || 0);
                        this.selectedDeliveryTitle = charge.title || 'Delivery';
                    },

                    applyCoupon() {
                        const code = this.promoCodeInput.trim().toUpperCase();
                        if (!code) return;

                        this.isApplyingCoupon = true;
                        this.couponError = '';

                        const payload = {
                            code: code,
                            _token: config.csrfToken
                        };

                        if (config.isBuyNow && config.buyNowProductId) {
                            payload.buy_now = 1;
                            payload.product_id = config.buyNowProductId;
                            payload.quantity = config.buyNowQuantity || 1;
                        }

                        fetch(config.applyCouponUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(async res => {
                            const data = await res.json();
                            this.isApplyingCoupon = false;
                            if (res.ok && data.success) {
                                this.appliedCoupon = data;
                                this.couponError = '';
                                if (window.toast) toast(data.message || 'Promo code applied!', 'success');
                            } else {
                                this.appliedCoupon = null;
                                this.couponError = data.message || 'Invalid promo code.';
                                if (window.toast) toast(this.couponError, 'error');
                            }
                        })
                        .catch(() => {
                            this.isApplyingCoupon = false;
                            this.couponError = 'Failed to apply promo code. Please try again.';
                            if (window.toast) toast(this.couponError, 'error');
                        });
                    },

                    removeCoupon() {
                        this.appliedCoupon = null;
                        this.promoCodeInput = '';
                        this.couponError = '';
                        if (window.toast) toast('Promo code removed.', 'info');
                    },

                    async submitCheckout() {
                        // 1. Client-side checks
                        this.errorMessage = '';
                        this.errorList = [];

                        if (!this.selectedAddressId) {
                            this.errorMessage = 'Please select a delivery address from your profile before continuing.';
                            if (window.toast) toast(this.errorMessage, 'error');
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            return;
                        }

                        if (this.paymentMethod === 'manual' && !this.transactionId.trim()) {
                            this.errorMessage = 'Please enter the Transaction ID / Reference Number for your mobile banking or bank transfer.';
                            if (window.toast) toast(this.errorMessage, 'error');
                            return;
                        }

                        this.isSubmitting = true;

                        const payload = {
                            _token: config.csrfToken,
                            address_id: this.selectedAddressId,
                            delivery_charge_id: this.selectedDeliveryId,
                            payment_method: this.paymentMethod,
                            transaction_id: this.paymentMethod === 'manual' ? this.transactionId.trim() : null,
                            notes: this.notes.trim() || null,
                            coupon_code: this.appliedCoupon ? this.appliedCoupon.code : null,
                        };

                        if (config.isBuyNow && config.buyNowProductId) {
                            payload.buy_now = 1;
                            payload.product_id = config.buyNowProductId;
                            payload.quantity = config.buyNowQuantity || 1;
                        }

                        try {
                            const res = await fetch(config.checkoutUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': config.csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify(payload)
                            });

                            const data = await res.json();

                            if (res.ok && data.success) {
                                if (window.toast) {
                                    toast(data.message || 'Order placed successfully!', 'success');
                                }
                                // Redirect to orders page (or Stripe checkout URL)
                                if (data.redirect_url) {
                                    window.location.href = data.redirect_url;
                                } else {
                                    window.location.href = config.ordersUrl;
                                }
                            } else {
                                this.isSubmitting = false;
                                this.errorMessage = data.message || 'Unable to place order. Please review the highlighted issues.';
                                if (data.errors) {
                                    this.errorList = Object.values(data.errors).flat();
                                }
                                if (window.toast) {
                                    toast(this.errorMessage, 'error');
                                }
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            }
                        } catch (err) {
                            this.isSubmitting = false;
                            this.errorMessage = 'A network error occurred while placing your order. Please check your connection and try again.';
                            if (window.toast) {
                                toast(this.errorMessage, 'error');
                            }
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection
