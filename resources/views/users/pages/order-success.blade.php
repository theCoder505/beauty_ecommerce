@extends('users.layouts.app')
@php $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); @endphp
@section('content')
<div class="max-w-2xl mx-auto px-4 py-16 text-center">
    <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="font-serif text-4xl font-bold text-gray-900 dark:text-white mb-3">Order Placed!</h1>
    <p class="text-gray-500 mb-2">Thank you for your purchase, <strong>{{ $order->shipping_name }}</strong></p>
    <p class="text-gray-400 text-sm mb-8">Order number: <strong class="text-brand-700 dark:text-brand-400">{{ $order->order_number }}</strong></p>

    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-cream-200 dark:border-gray-700 text-left mb-8">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Order Items</h2>
        <div class="space-y-3">
            @foreach($order->items as $item)
            <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">{{ $item->product_name }} × {{ $item->quantity }}</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $currency }}{{ number_format($item->total) }}</span>
            </div>
            @endforeach
        </div>
        <div class="border-t border-cream-200 dark:border-gray-700 mt-4 pt-4 flex justify-between font-bold">
            <span class="text-gray-900 dark:text-white">Total Paid</span>
            <span class="text-brand-700 dark:text-brand-400">{{ $currency }}{{ number_format($order->total) }}</span>
        </div>
        @if($order->payment)
        <div class="mt-3 flex items-center gap-2 text-sm">
            <span class="text-gray-500">Payment:</span>
            <span class="font-semibold text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $order->payment->method) }}</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $order->payment->status === 'verified' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                {{ ucfirst($order->payment->status) }}
            </span>
        </div>
        @endif
    </div>

    <div class="flex gap-4 justify-center">
        <a href="{{ route('user.orders.show', $order->id) }}" class="bg-brand-700 text-white font-semibold px-6 py-3 rounded-xl hover:bg-brand-800 transition">View Order Details</a>
        <a href="{{ route('shop.index') }}" class="border border-brand-700 text-brand-700 dark:text-brand-400 font-semibold px-6 py-3 rounded-xl hover:bg-brand-50 dark:hover:bg-brand-900/20 transition">Continue Shopping</a>
    </div>
</div>
@endsection

