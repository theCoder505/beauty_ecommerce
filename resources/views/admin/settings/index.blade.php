@extends('admin.layouts.app')
@section('title', 'Store Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ tab: 'general' }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Store Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Configure global store details, payment gateways, and social links.</p>
        </div>
    </div>

    <!-- Tab navigation -->
    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-800 pb-2 overflow-x-auto">
        <button @click="tab = 'general'" :class="tab === 'general' ? 'bg-brand-700 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-4 py-2 rounded-xl text-xs font-semibold transition shrink-0">General & Store</button>
        <button @click="tab = 'payments'" :class="tab === 'payments' ? 'bg-brand-700 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-4 py-2 rounded-xl text-xs font-semibold transition shrink-0">Payment Methods</button>
        <button @click="tab = 'socialite'" :class="tab === 'socialite' ? 'bg-brand-700 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-4 py-2 rounded-xl text-xs font-semibold transition shrink-0">Google OAuth</button>
        <button @click="tab = 'seo'" :class="tab === 'seo' ? 'bg-brand-700 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'" class="px-4 py-2 rounded-xl text-xs font-semibold transition shrink-0">SEO & Social</button>
    </div>

    @if(isset($errors) && $errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl p-4 text-sm">
        @foreach($errors->all() as $err)
            <p>{{ $err }}</p>
        @endforeach
    </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Tab 1: General & Store Details -->
        <div x-show="tab === 'general'" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Brand & Store Info</h3>

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

        <div class="flex items-center justify-end">
            <button type="submit" class="px-8 py-3 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-sm font-bold shadow-md transition">
                Save All Settings
            </button>
        </div>
    </form>
</div>
@endsection
