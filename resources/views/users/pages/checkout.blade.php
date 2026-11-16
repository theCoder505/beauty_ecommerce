@extends('users.layouts.app')
@php
    $settings = app(\App\Services\SettingService::class)->all();
    $currency = $settings['currency_symbol'] ?? '৳';
    $codEnabled = ($settings['cod_enabled'] ?? '0') == '1';
    $manualEnabled = ($settings['manual_payment_enabled'] ?? '0') == '1';
    $stripeEnabled = ($settings['stripe_enabled'] ?? '0') == '1';
    $manualInstr = $settings['manual_payment_instructions'] ?? '';
    $stripePk = $settings['stripe_publishable_key'] ?? '';
    $cartItems = $cartItems ?? ($items ?? collect());
    $cartTotal = $cartTotal ?? ($total ?? 0);
    $addresses = $addresses ?? (auth()->check() ? auth()->user()->addresses : collect());
    $primaryAddress = $primaryAddress ?? ($addresses->firstWhere('is_primary', true) ?? $addresses->first());
    $defaultAddrId = $primaryAddress ? $primaryAddress->id : 'custom';
@endphp

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-10" x-data="checkoutManager({
        paymentMethod: '{{ $codEnabled ? 'cod' : ($manualEnabled ? 'manual' : 'stripe') }}',
        addresses: @js($addresses),
        selectedAddressId: @js($defaultAddrId),
        initialAddress: @js(
    $primaryAddress
        ? [
            'phone' => $primaryAddress->phone ?? (auth()->user()?->phone ?? ''),
            'address' => $primaryAddress->address_line ?? '',
            'city' => $primaryAddress->city ?? '',
            'state' => $primaryAddress->state ?? '',
            'zip' => $primaryAddress->postal_code ?? '',
            'country' => $primaryAddress->country ?? 'BD',
        ]
        : [
            'phone' => auth()->user()?->phone ?? '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country' => 'BD',
        ],
)
    })">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Secure Checkout</h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Review your items and complete your luxury
                beauty order</p>
        </div>

        {{-- Error Banner --}}
        @if (isset($errors) && $errors->any())
            <div
                class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-2xl px-5 py-4 mb-6 text-sm shadow-xs">
                <div class="flex items-center gap-2 font-semibold mb-1">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Please review the highlighted details:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('checkout.place') }}" method="POST" id="checkout-form">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ session()->getId() . '_' . time() }}">
            <input type="hidden" name="payment_method" :value="paymentMethod">
            <input type="hidden" name="address_id" :value="selectedAddressId !== 'custom' ? selectedAddressId : ''">

            @if (!empty($isBuyNow) && $items->isNotEmpty())
                <input type="hidden" name="buy_now" value="1">
                <input type="hidden" name="product_id" value="{{ $items->first()->product_id }}">
                <input type="hidden" name="quantity" value="{{ $items->first()->quantity }}">
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                {{-- Left 2 Columns: Shipping Info + Saved Addresses + Payment --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- 1. Account Details (Name & Email CANNOT be changed) --}}
                    <div
                        class="bg-white dark:bg-gray-800/90 rounded-2xl p-6 sm:p-7 border border-stone-200/90 dark:border-gray-700 shadow-sm">
                        <div
                            class="flex items-center justify-between pb-4 mb-5 border-b border-stone-100 dark:border-gray-700/80">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="w-7 h-7 rounded-full bg-brand-700 text-white font-bold text-xs flex items-center justify-center">1</span>
                                <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-white">Customer Account</h2>
                            </div>
                            <span
                                class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 flex items-center gap-1 bg-stone-100 dark:bg-gray-700 px-2.5 py-1 rounded-lg">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Verified Account Details
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Name (Read-Only) --}}
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5 flex items-center justify-between">
                                    <span>Full Name</span>
                                    <span class="text-[10px] text-gray-400 font-normal lowercase">(cannot be changed)</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="shipping_name" value="{{ auth()->user()?->name ?? '' }}"
                                        readonly class="checkout-input-readonly pl-10"
                                        title="User name cannot be changed here">
                                    <svg class="w-3.5 h-3.5 text-gray-400 absolute right-3.5 top-3.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Email (Read-Only) --}}
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5 flex items-center justify-between">
                                    <span>Email Address</span>
                                    <span class="text-[10px] text-gray-400 font-normal lowercase">(cannot be changed)</span>
                                </label>
                                <div class="relative">
                                    <input type="email" name="shipping_email" value="{{ auth()->user()?->email ?? '' }}"
                                        readonly class="checkout-input-readonly pl-10"
                                        title="User email cannot be changed here">
                                    <svg class="w-3.5 h-3.5 text-gray-400 absolute right-3.5 top-3.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Delivery Address Selection & Details --}}
                    <div
                        class="bg-white dark:bg-gray-800/90 rounded-2xl p-6 sm:p-7 border border-stone-200/90 dark:border-gray-700 shadow-sm">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-5 border-b border-stone-100 dark:border-gray-700/80">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="w-7 h-7 rounded-full bg-brand-700 text-white font-bold text-xs flex items-center justify-center">2</span>
                                <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-white">Shipping Address</h2>
                            </div>
                            <a href="{{ route('user.profile') }}"
                                class="text-xs font-semibold text-brand-700 dark:text-brand-400 hover:underline flex items-center gap-1 self-start sm:self-auto">
                                <span>Manage in Profile (Google Map)</span>
                                <span>→</span>
                            </a>
                        </div>

                        {{-- Saved Address Cards (if user has any saved) --}}
                        @if ($addresses->isNotEmpty())
                            <div class="mb-6 space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                        Select Delivery Address:
                                    </p>
                                    <span class="text-[11px] text-gray-500">Primary address is selected by default</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach ($addresses as $addr)
                                        <div @click="selectAddress({{ $addr->id }})"
                                            class="relative p-4 rounded-2xl border-2 transition-all duration-200 cursor-pointer flex flex-col justify-between"
                                            :class="selectedAddressId === {{ $addr->id }} ?
                                                'border-brand-700 bg-brand-50/40 dark:bg-brand-900/20 ring-1 ring-brand-700/30 shadow-xs' :
                                                'border-stone-200 dark:border-gray-700 bg-stone-50/40 dark:bg-gray-800/40 hover:border-stone-300 dark:hover:border-gray-600'">

                                            <div class="flex items-start justify-between gap-2 mb-2">
                                                <div class="flex items-center gap-1.5">
                                                    <input type="radio" name="address_choice" value="{{ $addr->id }}"
                                                        :checked="selectedAddressId === {{ $addr->id }}"
                                                        class="text-brand-700 focus:ring-brand-700">
                                                    <span
                                                        class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-md {{ strtolower($addr->label) === 'home' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                                        {{ $addr->label }}
                                                    </span>
                                                </div>
                                                @if ($addr->is_primary)
                                                    <span
                                                        class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100/80 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md">
                                                        ★ Primary
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">
                                                {{ $addr->recipient_name ?: auth()->user()?->name }}</p>
                                            <p class="text-[11px] text-gray-500 mb-1.5">
                                                {{ $addr->phone ?: auth()->user()?->phone }}</p>
                                            <p
                                                class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-snug line-clamp-2">
                                                {{ $addr->address_line }}</p>
                                            <p class="text-[11px] text-gray-500 mt-1">
                                                {{ $addr->city }}{{ $addr->state ? ', ' . $addr->state : '' }}{{ $addr->postal_code ? ' ' . $addr->postal_code : '' }}
                                            </p>
                                        </div>
                                    @endforeach

                                    {{-- Option to type a custom / one-time address --}}
                                    <div @click="selectAddress('custom')"
                                        class="p-4 rounded-2xl border-2 transition-all duration-200 cursor-pointer flex flex-col items-center justify-center text-center border-dashed"
                                        :class="selectedAddressId === 'custom' ?
                                            'border-brand-700 bg-brand-50/40 dark:bg-brand-900/20 ring-1 ring-brand-700/30' :
                                            'border-stone-300 dark:border-gray-700 hover:border-stone-400 bg-stone-50/20'">
                                        <input type="radio" name="address_choice" value="custom"
                                            :checked="selectedAddressId === 'custom'" class="text-brand-700 sr-only">
                                        <span
                                            class="w-8 h-8 rounded-full bg-stone-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 mb-1.5 font-bold text-sm">+</span>
                                        <p class="text-xs font-bold text-gray-900 dark:text-white">Ship to a different
                                            address</p>
                                        <p class="text-[11px] text-gray-400">Enter custom delivery details below</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div
                                class="mb-5 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>No saved addresses found. Enter your shipping address below, or save multiple
                                        locations with Google Maps in your profile.</span>
                                </div>
                                <a href="{{ route('user.profile') }}"
                                    class="font-bold text-brand-700 dark:text-brand-400 hover:underline shrink-0">Open
                                    Profile →</a>
                            </div>
                        @endif

                        {{-- Address Form Fields with Luxury Border Design --}}
                        <div class="space-y-4 pt-1">
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Phone
                                    Number *</label>
                                <input type="text" name="shipping_phone" x-model="form.phone" required
                                    class="checkout-input" placeholder="+880 1XXXXXXXXX">
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Street
                                    Address / House / Road *</label>
                                <textarea name="shipping_address" x-model="form.address" rows="2" required class="checkout-input"
                                    placeholder="House/Apartment #, Road #, Sector or Area"></textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">City
                                        *</label>
                                    <input type="text" name="shipping_city" x-model="form.city" required
                                        class="checkout-input" placeholder="e.g. Dhaka">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">State/Division</label>
                                    <input type="text" name="shipping_state" x-model="form.state"
                                        class="checkout-input" placeholder="e.g. Dhaka">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Postal
                                        Code</label>
                                    <input type="text" name="shipping_zip" x-model="form.zip" class="checkout-input"
                                        placeholder="e.g. 1205">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Country</label>
                                    <input type="text" name="shipping_country" x-model="form.country"
                                        class="checkout-input" placeholder="BD">
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Order
                                    Notes (Optional)</label>
                                <textarea name="notes" rows="2" class="checkout-input"
                                    placeholder="Any special delivery instructions or requests…">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Payment Method Selection --}}
                    <div
                        class="bg-white dark:bg-gray-800/90 rounded-2xl p-6 sm:p-7 border border-stone-200/90 dark:border-gray-700 shadow-sm">
                        <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-stone-100 dark:border-gray-700/80">
                            <span
                                class="w-7 h-7 rounded-full bg-brand-700 text-white font-bold text-xs flex items-center justify-center">3</span>
                            <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-white">Payment Method</h2>
                        </div>

                        <div class="space-y-3">
                            {{-- COD --}}
                            @if ($codEnabled)
                                <label
                                    class="flex items-start gap-4 cursor-pointer p-4 rounded-2xl border-2 transition-all duration-200"
                                    :class="paymentMethod === 'cod' ?
                                        'border-brand-700 bg-brand-50/40 dark:bg-brand-900/20 ring-1 ring-brand-700/30 shadow-xs' :
                                        'border-stone-200 dark:border-gray-700 hover:border-stone-300 dark:hover:border-gray-600 bg-stone-50/30 dark:bg-gray-800/30'">
                                    <input type="radio" value="cod" x-model="paymentMethod"
                                        class="mt-1 text-brand-700 focus:ring-brand-700">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="font-bold text-sm text-gray-900 dark:text-white">Cash on Delivery
                                                (COD)</p>
                                            <span
                                                class="text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md">Popular</span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pay with cash upon
                                            physical delivery of your order right at your doorstep.</p>
                                    </div>
                                </label>
                            @endif

                            {{-- Manual Bank Transfer --}}
                            @if ($manualEnabled)
                                <label
                                    class="flex items-start gap-4 cursor-pointer p-4 rounded-2xl border-2 transition-all duration-200"
                                    :class="paymentMethod === 'manual' ?
                                        'border-brand-700 bg-brand-50/40 dark:bg-brand-900/20 ring-1 ring-brand-700/30 shadow-xs' :
                                        'border-stone-200 dark:border-gray-700 hover:border-stone-300 dark:hover:border-gray-600 bg-stone-50/30 dark:bg-gray-800/30'">
                                    <input type="radio" value="manual" x-model="paymentMethod"
                                        class="mt-1 text-brand-700 focus:ring-brand-700">
                                    <div class="flex-1">
                                        <p class="font-bold text-sm text-gray-900 dark:text-white">Manual Bank Transfer /
                                            Mobile Banking (bKash / Nagad / Rocket)</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Transfer directly to our
                                            merchant account and provide transaction ID.</p>

                                        <div x-show="paymentMethod === 'manual'" x-cloak class="mt-3 space-y-3">
                                            <div
                                                class="p-3.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-900 dark:text-amber-300 leading-relaxed whitespace-pre-line">
                                                {{ $manualInstr }}</div>
                                            <div>
                                                <label
                                                    class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 uppercase tracking-wide">Transaction
                                                    ID / Reference Number *</label>
                                                <input type="text" name="transaction_id"
                                                    placeholder="e.g. TRX9876543210" class="checkout-input"
                                                    :required="paymentMethod === 'manual'">
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endif

                            {{-- Stripe Online Payment --}}
                            @if ($stripeEnabled)
                                <label
                                    class="flex items-start gap-4 cursor-pointer p-4 rounded-2xl border-2 transition-all duration-200"
                                    :class="paymentMethod === 'stripe' ?
                                        'border-brand-700 bg-brand-50/40 dark:bg-brand-900/20 ring-1 ring-brand-700/30 shadow-xs' :
                                        'border-stone-200 dark:border-gray-700 hover:border-stone-300 dark:hover:border-gray-600 bg-stone-50/30 dark:bg-gray-800/30'">
                                    <input type="radio" value="stripe" x-model="paymentMethod"
                                        class="mt-1 text-brand-700 focus:ring-brand-700">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="font-bold text-sm text-gray-900 dark:text-white">Credit / Debit Card
                                                (Stripe)</p>
                                            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                                <span
                                                    class="font-mono text-[10px] bg-stone-200 dark:bg-gray-700 px-1.5 py-0.5 rounded font-bold">VISA</span>
                                                <span
                                                    class="font-mono text-[10px] bg-stone-200 dark:bg-gray-700 px-1.5 py-0.5 rounded font-bold">MC</span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Instant and encrypted
                                            checkout with Visa, MasterCard, or American Express.</p>
                                    </div>
                                </label>
                            @endif

                            @if (!$codEnabled && !$manualEnabled && !$stripeEnabled)
                                <div
                                    class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl text-amber-700 dark:text-amber-400 text-xs">
                                    No payment gateways are currently enabled. Please contact support.
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Right Column: Order Summary --}}
                <div class="lg:sticky lg:top-24">
                    <div
                        class="bg-white dark:bg-gray-800/90 rounded-2xl p-6 border border-stone-200/90 dark:border-gray-700 shadow-sm">
                        <div
                            class="flex items-center justify-between pb-4 mb-4 border-b border-stone-100 dark:border-gray-700/80">
                            <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-white">Order Summary</h2>
                            @if (!empty($isBuyNow))
                                <span
                                    class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 px-2.5 py-1 rounded-full">
                                    ⚡ Buy Now
                                </span>
                            @endif
                        </div>

                        @if (!empty($isBuyNow) && !empty($cartCount) && $cartCount > 0)
                            <div
                                class="mb-4 p-3 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/40 text-xs text-gray-600 dark:text-gray-300 flex items-center justify-between gap-2">
                                <span>Checking out this item only. ({{ $cartCount }} other in cart)</span>
                                <a href="{{ route('checkout.index') }}"
                                    class="text-brand-700 dark:text-brand-400 font-bold hover:underline shrink-0">Checkout
                                    All →</a>
                            </div>
                        @endif

                        {{-- Items List --}}
                        <div class="space-y-3.5 mb-5 max-h-60 overflow-y-auto pr-1">
                            @foreach ($cartItems as $item)
                                <div class="flex gap-3 items-center">
                                    <img src="{{ $item->product->thumbnail_url }}"
                                        class="w-12 h-12 rounded-xl object-cover shrink-0 border border-stone-200 dark:border-gray-700">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate">
                                            {{ $item->product->name }}</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">Qty: {{ $item->quantity }} ×
                                            {{ $currency }}{{ number_format($item->product->current_price) }}</p>
                                    </div>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white shrink-0">
                                        {{ $currency }}{{ number_format($item->line_total) }}</p>
                                </div>
                            @endforeach
                        </div>

                        {{-- Financial Breakdown --}}
                        <div
                            class="border-t border-stone-200/90 dark:border-gray-700 pt-4 space-y-2.5 text-xs text-gray-600 dark:text-gray-400">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span
                                    class="font-bold text-gray-900 dark:text-white">{{ $currency }}{{ number_format($cartTotal) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Estimated Shipping</span>
                                <span
                                    class="font-bold {{ $cartTotal >= 1000 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-900 dark:text-white' }}">
                                    {{ $cartTotal >= 1000 ? 'FREE' : $currency . '60' }}
                                </span>
                            </div>
                            <div
                                class="border-t border-stone-200/90 dark:border-gray-700 pt-3 flex justify-between items-baseline font-bold text-base text-gray-900 dark:text-white">
                                <span>Total Payable</span>
                                <span class="font-serif text-xl text-brand-700 dark:text-brand-400">
                                    {{ $currency }}{{ number_format($cartTotal + ($cartTotal >= 1000 ? 0 : 60)) }}
                                </span>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" id="place-order-button"
                            class="mt-6 w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-3.5 px-4 rounded-xl transition shadow-lg shadow-brand-700/25 active:scale-[0.99] flex items-center justify-center gap-2 text-sm">
                            <span>Complete Order</span>
                            <span>→</span>
                        </button>

                        <div
                            class="mt-4 pt-3 border-t border-stone-100 dark:border-gray-700/60 flex items-center justify-center gap-3 text-[11px] text-gray-400 text-center">
                            <span class="flex items-center gap-1">🔒 256-bit SSL</span>
                            <span>•</span>
                            <span>✓ Verified Purchase</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
        <style>
            .checkout-input {
                width: 100%;
                padding: 0.75rem 1rem;
                border-radius: 0.75rem;
                border: 1px solid rgba(214, 211, 209, 0.9);
                background-color: rgba(255, 255, 255, 0.9);
                font-size: 0.875rem;
                color: #111827;
                transition: all 0.2s ease;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
            }

            .dark .checkout-input {
                border-color: rgba(75, 85, 99, 0.8);
                background-color: rgba(17, 24, 39, 0.8);
                color: #f9fafb;
            }

            .checkout-input:focus {
                outline: none;
                border-color: #8b263e;
                box-shadow: 0 0 0 2px rgba(139, 38, 62, 0.2);
            }

            .checkout-input-readonly {
                width: 100%;
                padding: 0.75rem 1rem;
                border-radius: 0.75rem;
                border: 1px solid rgba(229, 231, 235, 1);
                background-color: rgba(243, 244, 246, 0.85);
                font-size: 0.875rem;
                color: #6b7280;
                cursor: not-allowed;
                user-select: none;
            }

            .dark .checkout-input-readonly {
                border-color: rgba(55, 65, 81, 1);
                background-color: rgba(31, 41, 55, 0.6);
                color: #9ca3af;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function checkoutManager(config) {
                return {
                    paymentMethod: config.paymentMethod || 'cod',
                    addresses: config.addresses || [],
                    selectedAddressId: config.selectedAddressId || 'custom',
                    form: {
                        phone: config.initialAddress?.phone || '',
                        address: config.initialAddress?.address || '',
                        city: config.initialAddress?.city || '',
                        state: config.initialAddress?.state || '',
                        zip: config.initialAddress?.zip || '',
                        country: config.initialAddress?.country || 'BD',
                    },

                    selectAddress(id) {
                        this.selectedAddressId = id;
                        if (id === 'custom') {
                            return;
                        }
                        const found = this.addresses.find(a => a.id === id);
                        if (found) {
                            this.form.phone = found.phone || '';
                            this.form.address = found.address_line || '';
                            this.form.city = found.city || '';
                            this.form.state = found.state || '';
                            this.form.zip = found.postal_code || '';
                            this.form.country = found.country || 'BD';
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection
