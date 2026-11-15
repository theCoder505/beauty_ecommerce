@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('content')

@php
    $lowStockItems   = $lowStockProducts ?? $lowStock ?? collect();
    $topSellingItems = $topSellingProducts ?? $topSelling ?? collect();
    $mostViewedItems = $mostViewedProducts ?? $mostViewed ?? collect();
@endphp

{{-- Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
    @foreach([
        ['label' => 'Total Revenue',   'value' => '$'.number_format($stats['total_revenue'] ?? 0, 2),  'sub' => '$'.number_format($stats['today_revenue'] ?? 0, 2).' today',  'color' => 'brand',  'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Total Orders',    'value' => number_format($stats['total_orders'] ?? 0),         'sub' => ($stats['pending_orders'] ?? 0).' pending',                   'color' => 'blue',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['label' => 'Customers',       'value' => number_format($stats['total_customers'] ?? 0),      'sub' => 'Registered users',                                    'color' => 'purple', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        ['label' => 'Site Visits',     'value' => number_format($stats['today_visits'] ?? 0),         'sub' => number_format($stats['month_visits'] ?? 0).' this month',   'color' => 'amber',  'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
        ['label' => 'Product Visits',  'value' => number_format($stats['total_product_visits'] ?? 0), 'sub' => 'Total product page views',                                 'color' => 'indigo', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
        ['label' => 'Blog Visits',     'value' => number_format($stats['total_blog_visits'] ?? 0),    'sub' => 'Total article reads',                                        'color' => 'emerald','icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
        ['label' => 'Products',        'value' => number_format($stats['total_products'] ?? 0),       'sub' => ($stats['low_stock'] ?? 0).' low stock',                      'color' => 'rose',   'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ['label' => 'Positive Ratings','value' => number_format($stats['positive_ratings'] ?? 0),     'sub' => '4-5 star reviews',                                    'color' => 'yellow', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
        ['label' => 'Wishlist Adds',   'value' => number_format($stats['total_wishlists'] ?? 0),     'sub' => 'Total wishlist items',                                 'color' => 'pink',   'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
        ['label' => 'Low Stock Alert', 'value' => number_format($stats['low_stock'] ?? 0),            'sub' => '≤10 units remaining',                                  'color' => 'red',    'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    ] as $card)
    <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $card['label'] }}</p>
            <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <svg class="w-4 h-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg>
            </div>
        </div>
        <p class="font-bold text-2xl text-gray-900 dark:text-white">{{ $card['value'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $card['sub'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    {{-- Recent Orders --}}
    <div class="xl:col-span-2 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
            <h2 class="font-semibold text-gray-800 dark:text-white">Recent Orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-xs text-brand-600 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wide border-b border-gray-100 dark:border-gray-800">
                        <th class="text-left px-5 py-3">Order</th>
                        <th class="text-left px-5 py-3">Customer</th>
                        <th class="text-left px-5 py-3">Amount</th>
                        <th class="text-left px-5 py-3">Status</th>
                        <th class="text-left px-5 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                        <td class="px-5 py-3 font-mono text-xs">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-brand-600 hover:underline font-semibold">{{ $order->order_number }}</a>
                        </td>
                        <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $order->shipping_name ?? $order->user?->name ?? 'Customer' }}</td>
                        <td class="px-5 py-3 font-semibold">${{ number_format($order->total, 2) }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-400 text-xs">{{ $order->created_at->format('M j, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-xs text-gray-400">
                            No orders placed yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Low Stock --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
            <h2 class="font-semibold text-gray-800 dark:text-white">Low Stock Alert</h2>
            <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="text-xs text-brand-600 hover:underline">Manage</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($lowStockItems->take(8) as $p)
            <div class="px-5 py-3 flex items-center justify-between gap-3">
                <a href="{{ route('admin.products.edit', $p) }}" class="text-sm text-gray-800 dark:text-gray-200 hover:text-brand-600 truncate transition">{{ $p->name }}</a>
                <span class="text-xs font-bold {{ $p->stock == 0 ? 'text-red-500' : 'text-amber-500' }} shrink-0">{{ $p->stock }} left</span>
            </div>
            @empty
            <p class="px-5 py-6 text-sm text-gray-400 text-center">All products are well stocked!</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Top Selling & Most Viewed --}}
<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800">
            <h2 class="font-semibold text-gray-800 dark:text-white">Top Selling Products</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($topSellingItems->take(5) as $item)
            <div class="px-5 py-3 flex items-center justify-between gap-3">
                <p class="text-sm text-gray-800 dark:text-gray-200 truncate">{{ $item->product?->name ?? 'Deleted Product' }}</p>
                <span class="text-xs font-bold text-brand-600 shrink-0">{{ $item->total_sold }} sold</span>
            </div>
            @empty
            <p class="px-5 py-6 text-sm text-gray-400 text-center">No sales data yet.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800">
            <h2 class="font-semibold text-gray-800 dark:text-white">Most Viewed Products</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($mostViewedItems->take(5) as $p)
            <div class="px-5 py-3 flex items-center justify-between gap-3">
                <a href="{{ route('admin.products.show', $p) }}" class="text-sm text-gray-800 dark:text-gray-200 hover:text-brand-600 truncate transition">{{ $p->name }}</a>
                <span class="text-xs font-bold text-purple-600 shrink-0">{{ number_format($p->views_count) }} views</span>
            </div>
            @empty
            <p class="px-5 py-6 text-sm text-gray-400 text-center">No view logs yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
