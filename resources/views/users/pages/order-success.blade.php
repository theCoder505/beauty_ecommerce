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
            <div class="flex justify-between items-start text-sm py-1 border-b border-gray-100 dark:border-gray-700/50 last:border-0">
                <div class="flex-1 min-w-0 pr-2">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $item->product_name }} × {{ $item->quantity }}</p>
                    @if(!empty($item->options_summary))
                        <p class="text-xs font-semibold text-brand-700 dark:text-brand-400 mt-0.5">{{ $item->options_summary }}</p>
                    @endif
                </div>
                <span class="font-semibold text-gray-900 dark:text-white shrink-0">{{ $currency }}{{ number_format($item->total) }}</span>
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

    <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
        <a href="{{ route('user.orders.show', $order) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-brand-700 text-white font-semibold px-8 py-3.5 rounded-xl hover:bg-brand-800 transition shadow-lg shadow-brand-700/25">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <span>Track Order</span>
        </a>
        <a href="{{ route('shop.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center border border-brand-700 text-brand-700 dark:text-brand-400 font-semibold px-7 py-3.5 rounded-xl hover:bg-brand-50 dark:hover:bg-brand-900/20 transition">
            Continue Shopping
        </a>
    </div>
</div>
@endsection

