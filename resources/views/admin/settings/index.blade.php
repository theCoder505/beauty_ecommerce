@extends('admin.layouts.app')
@section('title', 'Store Settings')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ tab: 'general' }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Store Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Configure global store details, payment gateways, and social links.</p>
        </div>
    </div>

    <!-- Tab navigation -->
    <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 dark:border-gray-800 pb-2 overflow-x-auto">
        <button @click="tab = 'general'" :class="tab === 'general' ? 'bg-brand-700 text-white font-bold shadow-md ring-2 ring-brand-700/40' : 'text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-4 py-2 rounded-xl text-xs font-semibold transition shrink-0">General & Store</button>
        <button @click="tab = 'timezone'" :class="tab === 'timezone' ? 'bg-brand-700 text-white font-bold shadow-md ring-2 ring-brand-700/40' : 'text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-4 py-2 rounded-xl text-xs font-semibold transition shrink-0">Timezone & Clock</button>
        <button @click="tab = 'shipping'" :class="tab === 'shipping' ? 'bg-brand-700 text-white font-bold shadow-md ring-2 ring-brand-700/40' : 'text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-4 py-2 rounded-xl text-xs font-semibold transition shrink-0">Shipping & Delivery</button>
        <button @click="tab = 'payments'" :class="tab === 'payments' ? 'bg-brand-700 text-white font-bold shadow-md ring-2 ring-brand-700/40' : 'text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-4 py-2 rounded-xl text-xs font-semibold transition shrink-0">Payment Methods</button>
        <button @click="tab = 'socialite'" :class="tab === 'socialite' ? 'bg-brand-700 text-white font-bold shadow-md ring-2 ring-brand-700/40' : 'text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-4 py-2 rounded-xl text-xs font-semibold transition shrink-0">Google OAuth</button>
        <button @click="tab = 'seo'" :class="tab === 'seo' ? 'bg-brand-700 text-white font-bold shadow-md ring-2 ring-brand-700/40' : 'text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-4 py-2 rounded-xl text-xs font-semibold transition shrink-0">SEO & Social</button>
        <button @click="tab = 'legal'" :class="tab === 'legal' ? 'bg-brand-700 text-white font-bold shadow-md ring-2 ring-brand-700/40' : 'text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-4 py-2 rounded-xl text-xs font-semibold transition shrink-0">Terms & Privacy</button>
    </div>

    @if(isset($errors) && $errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl p-4 text-sm">
        @foreach($errors->all() as $err)
            <p>{{ $err }}</p>
        @endforeach
    </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Tab 1: General & Store Details -->
        <div x-show="tab === 'general'" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Brand & Store Info</h3>

            {{-- Site Logo & Icon (Point 3 & Point 9) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/20">
                {{-- Logo --}}
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Website Logo</label>
                    <p class="text-xs text-gray-500">Appears in surface web header, admin sidebar & user dashboard.</p>
                    <div class="flex items-center gap-4 pt-1">
                        <div class="w-24 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center p-2 overflow-hidden shadow-inner shrink-0">
                            <img id="logo-preview" src="{{ !empty($settings['site_logo']->value) ? asset('storage/'.$settings['site_logo']->value) : '' }}" 
                                 class="max-h-full max-w-full object-contain {{ empty($settings['site_logo']->value) ? 'hidden' : '' }}" alt="Logo Preview">
                            <span id="logo-placeholder" class="text-xs text-gray-400 {{ !empty($settings['site_logo']->value) ? 'hidden' : '' }}">No Logo</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <input type="file" name="site_logo" accept="image/*" data-preview="#logo-preview"
                                   onchange="document.getElementById('logo-placeholder').classList.add('hidden')"
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300">
                            @if(!empty($settings['site_logo']->value))
                            <label class="inline-flex items-center gap-1.5 mt-2 cursor-pointer">
                                <input type="checkbox" name="remove_site_logo" value="1" class="rounded text-red-600 focus:ring-red-500 text-xs">
                                <span class="text-xs text-red-500 hover:text-red-600">Remove current logo</span>
                            </label>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Icon / Favicon --}}
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Website Icon / Favicon</label>
                    <p class="text-xs text-gray-500">Appears as the browser tab icon and collapsed sidebar logo.</p>
                    <div class="flex items-center gap-4 pt-1">
                        <div class="w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center p-2 overflow-hidden shadow-inner shrink-0">
                            <img id="icon-preview" src="{{ !empty($settings['site_icon']->value) ? asset('storage/'.$settings['site_icon']->value) : '' }}" 
                                 class="w-10 h-10 object-contain {{ empty($settings['site_icon']->value) ? 'hidden' : '' }}" alt="Icon Preview">
                            <span id="icon-placeholder" class="text-xs text-gray-400 {{ !empty($settings['site_icon']->value) ? 'hidden' : '' }}">No Icon</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <input type="file" name="site_icon" accept="image/*" data-preview="#icon-preview"
                                   onchange="document.getElementById('icon-placeholder').classList.add('hidden')"
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300">
                            @if(!empty($settings['site_icon']->value))
                            <label class="inline-flex items-center gap-1.5 mt-2 cursor-pointer">
                                <input type="checkbox" name="remove_site_icon" value="1" class="rounded text-red-600 focus:ring-red-500 text-xs">
                                <span class="text-xs text-red-500 hover:text-red-600">Remove current icon</span>
                            </label>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Store / Brand Name</label>
                    <input type="text" name="site_name" value="{{ $settings['site_name']->value ?? 'Trefume Paris' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tagline</label>
                    <input type="text" name="site_tagline" value="{{ $settings['site_tagline']->value ?? 'Artisan Fragrances & Quiet Luxury' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Support Email</label>
                    <input type="email" name="contact_email" value="{{ $settings['contact_email']->value ?? 'concierge@trefume.com' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Support Phone</label>
                    <input type="text" name="contact_phone" value="{{ $settings['contact_phone']->value ?? '+1 (800) 456-7890' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Boutique Address</label>
                    <input type="text" name="address" value="{{ $settings['address']->value ?? '742 Rue Saint-Honoré, 75001 Paris, France' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>
            </div>

            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3 pt-4">Currency & Shipping Charges</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol']->value ?? '$' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Flat Rate Shipping ($)</label>
                    <input type="number" step="0.01" name="shipping_flat_rate" value="{{ $settings['shipping_flat_rate']->value ?? '10.00' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Free Shipping Over ($)</label>
                    <input type="number" step="0.01" name="free_shipping_threshold" value="{{ $settings['free_shipping_threshold']->value ?? '100.00' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>
            </div>

            {{-- Timezone Quick Link in General Tab --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl border-2 border-teal-300 dark:border-teal-700/80 bg-gradient-to-r from-teal-50 via-emerald-50/70 to-teal-100/60 dark:from-[#042f2e] dark:via-[#022c22] dark:to-[#0f3d39] shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-teal-700 dark:bg-teal-600 text-white flex items-center justify-center shrink-0 shadow-md ring-4 ring-teal-700/20 dark:ring-teal-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-teal-950 dark:text-teal-100 uppercase tracking-wider">Store & System Timezone</h4>
                        <p class="text-xs text-teal-900/80 dark:text-teal-200 mt-0.5">Currently active: <span class="font-black text-teal-800 dark:text-teal-200 font-mono px-2 py-0.5 rounded bg-white dark:bg-teal-950/90 border border-teal-300 dark:border-teal-700">{{ config('app.timezone') }}</span> (PHP & JS Synchronized).</p>
                    </div>
                </div>
                <button type="button" @click="tab = 'timezone'" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal-700 hover:bg-teal-800 dark:bg-teal-600 dark:hover:bg-teal-500 text-white text-xs font-bold shadow-md ring-2 ring-teal-700/30 dark:ring-teal-500/30 transition self-start sm:self-auto shrink-0">
                    <span>Manage Timezone</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- Tab: Timezone & Clock (Dedicated Component View) -->
        <div x-show="tab === 'timezone'" class="space-y-6">
            <x-timezone-picker :selected="$settings['timezone']->value ?? null" />
        </div>

        <!-- Tab 2: Payments -->
        <div x-show="tab === 'payments'" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Payment Methods</h3>

            <div class="space-y-6">
                <!-- COD -->
                <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 space-y-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="cod_enabled" value="1" {{ ($settings['cod_enabled']->value ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        <span class="ml-3 text-sm font-semibold text-gray-900 dark:text-white">Enable Cash On Delivery (COD)</span>
                    </label>
                    <p class="text-xs text-gray-500">Allows customers to pay in cash upon arrival of their parcel.</p>
                </div>

                <!-- Manual / Bank -->
                <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 space-y-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="manual_payment_enabled" value="1" {{ ($settings['manual_payment_enabled']->value ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        <span class="ml-3 text-sm font-semibold text-gray-900 dark:text-white">Enable Manual / Bank Transfer</span>
                    </label>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Transfer Instructions / Bank Details</label>
                        <textarea name="manual_payment_instructions" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none">{{ $settings['manual_payment_instructions']->value ?? "Bank: Chase Manhattan\nAccount: 1234-5678-9012\nRouting: 021000021" }}</textarea>
                    </div>
                </div>

                <!-- Stripe -->
                <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 space-y-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="stripe_enabled" value="1" {{ ($settings['stripe_enabled']->value ?? '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        <span class="ml-3 text-sm font-semibold text-gray-900 dark:text-white">Enable Stripe Online Gateway</span>
                    </label>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Stripe Publishable Key</label>
                            <input type="text" name="stripe_publishable_key" value="{{ $settings['stripe_publishable_key']->value ?? '' }}" placeholder="pk_test_..." class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-mono focus:ring-2 focus:ring-brand-600 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Stripe Secret Key (Encrypted at rest)</label>
                            <input type="password" name="stripe_secret_key" placeholder="sk_test_... (leave empty to keep existing)" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-mono focus:ring-2 focus:ring-brand-600 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Stripe Webhook Secret (Optional)</label>
                            <input type="password" name="stripe_webhook_secret" placeholder="whsec_... (leave empty to keep existing)" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-mono focus:ring-2 focus:ring-brand-600 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Google OAuth -->
        <div x-show="tab === 'socialite'" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Google Social Login</h3>
            <p class="text-xs text-gray-500">Configured at runtime into Laravel Socialite without touching .env.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Google Client ID</label>
                    <input type="text" name="google_client_id" value="{{ $settings['google_client_id']->value ?? '' }}" placeholder="xxxxxxxx.apps.googleusercontent.com" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-mono focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Google Client Secret (Encrypted at rest)</label>
                    <input type="password" name="google_client_secret" placeholder="GOCSPX-... (leave blank to keep current)" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-mono focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Authorized Redirect URI</label>
                    <input type="text" readonly value="{{ url('/auth/google/callback') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs font-mono text-gray-500">
                    <p class="text-[11px] text-gray-400 mt-1">Add this URI to your Google Cloud Console Credentials.</p>
                </div>
            </div>
        </div>

        <!-- Tab 4: SEO & Social -->
        <div x-show="tab === 'seo'" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Default Search Engine Optimization</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Meta Title</label>
                    <input type="text" name="default_meta_title" value="{{ $settings['default_meta_title']->value ?? 'Trefume Paris — Luxury Fragrance House' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Meta Description</label>
                    <textarea name="default_meta_description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">{{ $settings['default_meta_description']->value ?? 'Discover refined perfumes and cosmetics crafted for quiet luxury, intimate presence, and timeless memory.' }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Keywords</label>
                    <input type="text" name="default_meta_keywords" value="{{ $settings['default_meta_keywords']->value ?? 'fragrance, perfume, luxury cosmetics, artisan scent' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>
            </div>

            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3 pt-4">Social Media Channels</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instagram URL</label>
                    <input type="url" name="instagram_url" value="{{ $settings['instagram_url']->value ?? 'https://instagram.com' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Facebook URL</label>
                    <input type="url" name="facebook_url" value="{{ $settings['facebook_url']->value ?? 'https://facebook.com' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Twitter / X URL</label>
                    <input type="url" name="twitter_url" value="{{ $settings['twitter_url']->value ?? 'https://x.com' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">YouTube URL</label>
                    <input type="url" name="youtube_url" value="{{ $settings['youtube_url']->value ?? 'https://youtube.com' }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Tab: Shipping & Delivery Charges -->
        <div x-show="tab === 'shipping'" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm" x-data="deliveryChargesManager(@js($deliveryCharges))">
            <div class="border-b border-gray-100 dark:border-gray-800 pb-3">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Delivery Charges & Regions</h3>
                <p class="text-xs text-gray-500 mt-0.5">Admin can set multiple delivery charge tiers (e.g. 'Inside Dhaka' 60, 'Outside Dhaka' 120). Customers must choose one during checkout.</p>
            </div>

            <div class="space-y-3">
                <template x-for="(charge, index) in charges" :key="index">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
                        <div class="flex-1">
                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Charge Title / Region *</label>
                            <input type="text"
                                   :name="'delivery_charges[' + index + '][title]'"
                                   x-model="charge.title"
                                   required
                                   placeholder="e.g. Inside Dhaka, Outside Dhaka, Express"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-600 focus:outline-none font-medium">
                        </div>

                        <div class="w-full sm:w-40">
                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Amount ({{ $settings['currency_symbol']->value ?? '৳' }}) *</label>
                            <input type="number"
                                   :name="'delivery_charges[' + index + '][amount]'"
                                   x-model="charge.amount"
                                   required
                                   min="0"
                                   step="any"
                                   placeholder="e.g. 60"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-600 focus:outline-none font-bold">
                        </div>

                        <div class="flex items-center justify-between sm:justify-start gap-4 pt-1 sm:pt-4">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-gray-700 dark:text-gray-300">
                                <input type="radio"
                                       name="default_delivery_selection"
                                       :value="index"
                                       :checked="charge.is_default"
                                       @change="setDefault(index)"
                                       class="text-brand-700 focus:ring-brand-600">
                                <span class="text-xs font-semibold">Default</span>
                            </label>
                            <input type="hidden" :name="'delivery_charges[' + index + '][is_default]'" :value="charge.is_default ? 1 : 0">

                            <button type="button"
                                    @click="removeCharge(index)"
                                    :disabled="charges.length <= 1"
                                    class="p-2 rounded-xl text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 disabled:opacity-20 disabled:cursor-not-allowed transition"
                                    title="Delete Tier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div>
                <button type="button"
                        @click="addCharge()"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-brand-700/30 text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 hover:bg-brand-100 dark:hover:bg-brand-900/40 text-xs font-bold transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add Delivery Charge Option</span>
                </button>
            </div>
        </div>

        <!-- Tab: Terms & Privacy Policy -->
        <div x-show="tab === 'legal'" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Terms & Conditions & Privacy Policy</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Admin can format Terms & Conditions and Privacy Policy using the RichEditor component with image support.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.terms.edit') }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
                        <span>Dedicated Terms Page</span> <span>↗</span>
                    </a>
                    <span class="text-gray-300 dark:text-gray-700">|</span>
                    <a href="{{ route('admin.privacy.edit') }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
                        <span>Dedicated Privacy Page</span> <span>↗</span>
                    </a>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Terms & Conditions</label>
                <p class="text-xs text-gray-500">Public URL: <a href="{{ route('terms') }}" target="_blank" class="text-brand-600 font-mono">{{ url('/terms-and-conditions') }}</a></p>
                <x-rich-editor
                    name="terms_and_conditions"
                    id="terms-quill-editor"
                    inputId="hidden-terms-content"
                    :value="old('terms_and_conditions', $settings['terms_and_conditions']->value ?? '')"
                    placeholder="Enter your store Terms and Conditions..."
                    height="320px"
                />
            </div>

            <div class="pt-6 border-t border-gray-100 dark:border-gray-800 space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Privacy Policy</label>
                <p class="text-xs text-gray-500">Public URL: <a href="{{ route('privacy') }}" target="_blank" class="text-brand-600 font-mono">{{ url('/privacy-policy') }}</a></p>
                <x-rich-editor
                    name="privacy_policy"
                    id="privacy-quill-editor"
                    inputId="hidden-privacy-content"
                    :value="old('privacy_policy', $settings['privacy_policy']->value ?? '')"
                    placeholder="Enter your store Privacy Policy..."
                    height="320px"
                />
            </div>
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" class="px-8 py-3 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-sm font-bold shadow-md transition">
                Save All Settings
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function deliveryChargesManager(initialCharges) {
    return {
        charges: (initialCharges && initialCharges.length > 0) ? initialCharges : [
            { title: 'Inside Dhaka', amount: 60, is_default: true },
            { title: 'Outside Dhaka', amount: 120, is_default: false },
        ],
        addCharge() {
            this.charges.push({ title: '', amount: 0, is_default: this.charges.length === 0 });
        },
        removeCharge(index) {
            if (this.charges.length <= 1) return;
            const wasDefault = this.charges[index].is_default;
            this.charges.splice(index, 1);
            if (wasDefault && this.charges.length > 0) {
                this.charges[0].is_default = true;
            }
        },
        setDefault(index) {
            this.charges.forEach((c, idx) => {
                c.is_default = (idx === index);
            });
        }
    };
}
</script>
@endpush
@endsection
