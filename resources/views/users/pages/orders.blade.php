@extends('users.layouts.app')
@php $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); @endphp
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8">
    @include('users.partials.dashboard-sidebar')
    <main class="flex-1 min-w-0">
        <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-6">My Orders</h1>
        @if($orders->isEmpty())<p class="text-gray-500 text-sm">No orders yet.</p>
        @else
        <div class="space-y-4">
            @foreach($orders as $order)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <p class="font-mono text-xs text-brand-700 font-bold">{{ $order->order_number }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ $order->created_at->format('M j, Y H:i') }}</p>
                    @if($order->items && $order->items->isNotEmpty())
                        <div class="mt-2 text-xs text-gray-600 dark:text-gray-300 space-y-1">
                            @foreach($order->items as $item)
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $item->product_name }}</span>
                                    <span class="text-gray-400">&times;{{ $item->quantity }}</span>
                                    @if(!empty($item->options_summary))
                                        <span class="inline-flex items-center text-[10px] font-semibold bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-300 px-1.5 py-0.2 rounded border border-brand-200 dark:border-brand-900/50">
                                            {{ $item->options_summary }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($order->notes) || !empty($order->order_notes))
                        <p class="text-[11px] text-amber-700 dark:text-amber-300 italic mt-1 line-clamp-1">📝 Note: "{{ $order->notes ?? $order->order_notes }}"</p>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <span class="font-bold text-gray-900 dark:text-white">{{ $currency }}{{ number_format($order->total) }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-{{ $order->status_color }}-100 dark:bg-{{ $order->status_color }}-900/30 text-{{ $order->status_color }}-700">{{ $order->status_label }}</span>
                    <a href="{{ route('user.orders.show', $order->id) }}" class="text-sm text-brand-700 hover:underline font-semibold">View →</a>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-8">{{ $orders->links() }}</div>
        @endif
    </main>
</div>
@endsection

