@extends('users.layouts.app')
@php $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); @endphp
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8">
    @include('users.partials.dashboard-sidebar')
    <main class="flex-1 min-w-0">
        <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-6">My Wishlist</h1>
        @if($items->isEmpty())<p class="text-gray-500 text-sm">Your wishlist is empty. <a href="{{ route('shop.index') }}" class="text-brand-700 hover:underline">Browse products</a></p>
        @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            @foreach($items as $wl)
            @if($wl->product)@include('users.partials.product-card', ['product' => $wl->product, 'currency' => $currency])@endif
            @endforeach
        </div>
        @endif
    </main>
</div>
@endsection

