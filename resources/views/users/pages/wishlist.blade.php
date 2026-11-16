@extends('users.layouts.app')
@php $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); @endphp
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8">
    @include('users.partials.dashboard-sidebar')
    <main class="flex-1 min-w-0" id="wishlist-page-container">
        <div class="flex items-center justify-between mb-6">
            <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white">My Wishlist</h1>
            <span id="wishlist-items-counter" class="text-xs font-semibold text-gray-500 {{ $items->isEmpty() ? 'hidden' : '' }}">
                <span id="wishlist-count-number">{{ $items->count() }}</span> items saved
            </span>
        </div>

        {{-- Empty State --}}
        <div id="wishlist-empty-state" class="{{ $items->isEmpty() ? '' : 'hidden' }} py-16 text-center border-2 border-dashed border-cream-200 dark:border-gray-800 rounded-3xl p-8 bg-cream-50/50 dark:bg-gray-800/20">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-cream-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 flex items-center justify-center">
                <svg class="w-8 h-8 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <h3 class="font-serif text-lg font-bold text-gray-900 dark:text-white mb-1">Your wishlist is empty</h3>
            <p class="text-gray-500 text-xs mb-6 max-w-sm mx-auto">Explore our luxury skincare and fragrances to curate your personal collection.</p>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold shadow-md transition">
                <span>Browse Products</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        {{-- Product Grid --}}
        <div id="wishlist-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 {{ $items->isEmpty() ? 'hidden' : '' }}">
            @foreach($items as $wl)
            @if($wl->product)@include('users.partials.product-card', ['product' => $wl->product, 'currency' => $currency])@endif
            @endforeach
        </div>
    </main>
</div>
@endsection
