@extends('users.layouts.app')
@php $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); @endphp
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8">
    @include('users.partials.dashboard-sidebar')
    <main class="flex-1 min-w-0">
        <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-6">Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Orders</p>
                <p class="font-bold text-3xl text-gray-900 dark:text-white">{{ $totalOrders }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Spent</p>
                <p class="font-bold text-3xl text-brand-700 dark:text-brand-400">{{ $currency }}{{ number_format($totalSpent) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Wishlist Items</p>
                <p class="font-bold text-3xl text-gray-900 dark:text-white">{{ $wishlistCount }}</p>
            </div>
        </div>
        <h2 class="font-semibold text-gray-800 dark:text-white mb-4">Recent Orders</h2>
        @if($recentOrders->isEmpty())
        <p class="text-gray-500 text-sm">No orders yet. <a href="{{ route('shop.index') }}" class="text-brand-700 hover:underline">Start shopping!</a></p>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-cream-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm"><thead><tr class="text-xs text-gray-400 uppercase bg-cream-50 dark:bg-gray-900"><th class="px-5 py-3 text-left">Order</th><th class="px-5 py-3 text-left">Total</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Date</th></tr></thead>
            <tbody class="divide-y divide-cream-100 dark:divide-gray-700">
                @foreach($recentOrders as $order)
                <tr class="hover:bg-cream-50 dark:hover:bg-gray-800/50">
                    <td class="px-5 py-3"><a href="{{ route('user.orders.show', $order->id) }}" class="text-brand-700 font-mono text-xs hover:underline">{{ $order->order_number }}</a></td>
                    <td class="px-5 py-3 font-semibold">{{ $currency }}{{ number_format($order->total) }}</td>
                    <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-{{ $order->status_color }}-100 dark:bg-{{ $order->status_color }}-900/30 text-{{ $order->status_color }}-700">{{ $order->status_label }}</span></td>
                    <td class="px-5 py-3 text-gray-400 text-xs">{{ $order->created_at->format('M j, Y') }}</td>
                </tr>
                @endforeach
            </tbody></table>
        </div>
        @endif
    </main>
</div>
@endsection

