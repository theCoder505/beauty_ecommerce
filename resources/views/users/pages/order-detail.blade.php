@extends('users.layouts.app')
@php $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); @endphp
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8">
    @include('users.partials.dashboard-sidebar')
    <main class="flex-1 min-w-0">
        <div class="flex items-center gap-3 mb-6"><a href="{{ route('user.orders') }}" class="text-brand-700 hover:underline text-sm">← My Orders</a></div>
        <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-6">Order {{ $order->order_number }}</h1>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-5">
                {{-- Items --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Order Items</h2>
                    <div class="divide-y divide-cream-100 dark:divide-gray-700">
                        @foreach($order->items as $item)
                        <div class="py-3 flex items-center gap-4">
                            @if($item->product)<img src="{{ $item->product->thumbnail_url }}" class="w-14 h-14 rounded-xl object-cover shrink-0">@endif
                            <div class="flex-1 min-w-0"><p class="font-semibold text-sm text-gray-900 dark:text-white truncate">{{ $item->product_name }}</p><p class="text-xs text-gray-400">SKU: {{ $item->product_sku }} × {{ $item->quantity }}</p></div>
                            <p class="font-bold text-sm text-gray-900 dark:text-white shrink-0">{{ $currency }}{{ number_format($item->total) }}</p>
                        </div>
                        @endforeach
                    </div>
                    <div class="border-t border-cream-200 dark:border-gray-700 pt-3 mt-3 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between"><span>Subtotal</span><span>{{ $currency }}{{ number_format($order->subtotal) }}</span></div>
                        <div class="flex justify-between"><span>Shipping</span><span>{{ $currency }}{{ number_format($order->shipping_cost) }}</span></div>
                        <div class="flex justify-between font-bold text-gray-900 dark:text-white text-base pt-1"><span>Total</span><span>{{ $currency }}{{ number_format($order->total) }}</span></div>
                    </div>
                </div>
                {{-- Status History --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Status History</h2>
                    <div class="space-y-3">
                        @foreach($order->statusHistories as $h)
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-brand-700 mt-1.5 shrink-0"></div>
                            <div><p class="text-sm font-semibold text-gray-900 dark:text-white capitalize">{{ str_replace('-',' ',$h->status) }}</p><p class="text-xs text-gray-400">{{ $h->created_at->format('M j, Y H:i') }}@if($h->note) — {{ $h->note }}@endif</p></div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="space-y-5">
                {{-- Status --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Current Status</p>
                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-{{ $order->status_color }}-100 dark:bg-{{ $order->status_color }}-900/30 text-{{ $order->status_color }}-700">{{ $order->status_label }}</span>
                </div>
                {{-- Payment --}}
                @if($order->payment)
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Payment</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200 capitalize font-semibold">{{ $order->payment->method }}</p>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $order->payment->status==='verified'?'bg-green-100 text-green-700':'bg-amber-100 text-amber-700' }}">{{ ucfirst($order->payment->status) }}</span>
                </div>
                @endif
                {{-- Shipping --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Ship To</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200 font-semibold">{{ $order->shipping_name }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $order->shipping_address }}, {{ $order->shipping_city }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $order->shipping_phone }}</p>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

