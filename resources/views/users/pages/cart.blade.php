@extends('users.layouts.app')
@php $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); @endphp
@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="font-serif text-3xl font-bold text-gray-900 dark:text-white mb-8">Your Cart</h1>
    @if($items->isEmpty())
    <div class="text-center py-24">
        <svg class="w-16 h-16 text-gray-200 dark:text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        <p class="text-gray-500 font-semibold text-lg mb-4">Your cart is empty</p>
        <a href="{{ route('shop.index') }}" class="bg-brand-700 text-white font-semibold px-8 py-3 rounded-full hover:bg-brand-800 transition">Start Shopping</a>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-4">
            @foreach($items as $item)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700 flex gap-4 items-center">
                <img src="{{ $item->product->thumbnail_url }}" class="w-20 h-20 rounded-xl object-cover shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-gray-900 dark:text-white truncate">{{ $item->product->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->product->category->name ?? '' }}</p>
                    <p class="text-brand-700 dark:text-brand-400 font-bold text-sm mt-1">{{ $currency }}{{ number_format($item->product->current_price) }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center border border-cream-200 dark:border-gray-600 rounded-lg overflow-hidden text-sm">
                        @csrf @method('PATCH')
                        <button type="submit" name="quantity" value="{{ max(1, $item->quantity - 1) }}" class="px-3 py-2 hover:bg-cream-100 dark:hover:bg-gray-700 transition">−</button>
                        <span class="px-3 py-2 font-semibold">{{ $item->quantity }}</span>
                        <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}" class="px-3 py-2 hover:bg-cream-100 dark:hover:bg-gray-700 transition">+</button>
                    </form>
                    <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 p-2 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
                <p class="font-bold text-gray-900 dark:text-white text-sm shrink-0">{{ $currency }}{{ number_format($item->line_total) }}</p>
            </div>
            @endforeach
        </div>
        <div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-cream-200 dark:border-gray-700 sticky top-20">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-5">Order Summary</h2>
                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex justify-between"><span>Subtotal</span><span class="font-semibold text-gray-900 dark:text-white">{{ $currency }}{{ number_format($total) }}</span></div>
                    <div class="flex justify-between"><span>Shipping</span><span class="text-green-600 font-semibold">{{ $total >= 1000 ? 'Free' : $currency.'60' }}</span></div>
                    <div class="border-t border-cream-200 dark:border-gray-700 pt-3 flex justify-between font-bold text-base text-gray-900 dark:text-white">
                        <span>Total</span><span>{{ $currency }}{{ number_format($total + ($total >= 1000 ? 0 : 60)) }}</span>
                    </div>
                </div>
                <a href="{{ route('checkout.index') }}" class="mt-6 block w-full bg-brand-700 text-white font-bold py-3.5 rounded-xl text-center hover:bg-brand-800 transition shadow-lg shadow-brand-700/25">
                    Proceed to Checkout
                </a>
                <a href="{{ route('shop.index') }}" class="mt-3 block text-center text-sm text-brand-700 dark:text-brand-400 hover:underline">Continue Shopping</a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

