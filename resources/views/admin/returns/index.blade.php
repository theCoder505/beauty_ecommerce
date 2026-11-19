@extends('admin.layouts.app')
@section('title', 'Returns & Refunds')
@php
    $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳');
@endphp

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>Returns & Refunds</span>
                @if(($stats['pending_count'] ?? 0) > 0)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                        {{ $stats['pending_count'] }} Pending
                    </span>
                @endif
            </h1>
            <p class="text-xs text-gray-500 mt-1">Review return requests, process customer refunds, and manage return inventory.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.profit-loss') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-brand-50 text-brand-700 dark:bg-teal-950 dark:text-brand-300 border border-brand-200 dark:border-brand-900/60 hover:bg-brand-100 transition shadow-2xs">
                📊 View P&L Financial Report →
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Refunded Amount --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 shadow-xs">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Money Refunded</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $currency }}{{ number_format($stats['total_refunded_amount'] ?? 0, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['refunded_count'] ?? 0 }} refunded orders</p>
        </div>

        {{-- Pending Requests --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border {{ ($stats['pending_count'] ?? 0) > 0 ? 'border-amber-400 dark:border-amber-700 bg-amber-50/20 dark:bg-amber-950/10' : 'border-gray-200 dark:border-gray-800' }} shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold {{ ($stats['pending_count'] ?? 0) > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-gray-500' }} uppercase tracking-wide">Pending Requests</p>
                @if(($stats['pending_count'] ?? 0) > 0)
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                    </span>
                @endif
            </div>
            <p class="text-2xl font-bold {{ ($stats['pending_count'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }} mt-2">
                {{ $stats['pending_count'] ?? 0 }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Needs admin decision</p>
        </div>

        {{-- Returned Products --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 shadow-xs">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Returned Orders</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['returned_count'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Restocked automatically</p>
        </div>

        {{-- Total Items Refunded --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 shadow-xs">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Items In Refunded Orders</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_items_refunded'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Products returned/refunded</p>
        </div>
    </div>

    {{-- Filter Tabs & Search --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 space-y-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            {{-- Tabs --}}
            <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0">
                <a href="{{ route('admin.returns.index', ['tab' => 'all']) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ $tab === 'all' ? 'bg-brand-700 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    All Records
                </a>
                <a href="{{ route('admin.returns.index', ['tab' => 'requested']) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 flex items-center gap-1.5 {{ $tab === 'requested' ? 'bg-amber-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <span>Pending Requests</span>
                    @if(($stats['pending_count'] ?? 0) > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'requested' ? 'bg-white text-amber-700' : 'bg-amber-100 text-amber-800' }}">{{ $stats['pending_count'] }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.returns.index', ['tab' => 'refunded']) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ $tab === 'refunded' ? 'bg-brand-700 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    Refunded ({{ $stats['refunded_count'] }})
                </a>
                <a href="{{ route('admin.returns.index', ['tab' => 'returned']) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ $tab === 'returned' ? 'bg-brand-700 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    Returned ({{ $stats['returned_count'] }})
                </a>
                <a href="{{ route('admin.returns.index', ['tab' => 'rejected']) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ $tab === 'rejected' ? 'bg-brand-700 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    Declined ({{ $stats['rejected_count'] }})
                </a>
            </div>

            {{-- Search form --}}
            <form action="{{ route('admin.returns.index') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search order #, customer, reason..."
                       class="px-3.5 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-600 w-48 sm:w-64">
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-xs font-semibold transition">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.returns.index', ['tab' => $tab]) }}" class="text-xs text-red-500 hover:underline">Clear</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="py-3 px-4">Order</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Request Type</th>
                        <th class="py-3 px-4">Reason Given</th>
                        <th class="py-3 px-4">Order Total</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Requested / Updated</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        {{-- Order --}}
                        <td class="py-3 px-4">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-brand-700 dark:text-brand-400 hover:underline">
                                {{ $order->order_number }}
                            </a>
                            <p class="text-[11px] text-gray-400">{{ $order->items->count() }} items</p>
                        </td>

                        {{-- Customer --}}
                        <td class="py-3 px-4">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $order->shipping_name ?? $order->user?->name ?? 'Guest' }}</p>
                            <p class="text-[11px] text-gray-400">{{ $order->shipping_email ?? $order->user?->email }}</p>
                            <p class="text-[11px] text-gray-400 font-mono">{{ $order->shipping_phone }}</p>
                        </td>

                        {{-- Request Type --}}
                        <td class="py-3 px-4">
                            @if($order->return_request_type === 'refund' || $order->status === 'refunded')
                                <span class="inline-flex items-center gap-1 font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-800/60">
                                    Refund
                                </span>
                            @elseif($order->return_request_type === 'return' || $order->status === 'returned')
                                <span class="inline-flex items-center gap-1 font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 px-2.5 py-0.5 rounded-lg border border-rose-200 dark:border-rose-800/60">
                                    Return
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        {{-- Reason --}}
                        <td class="py-3 px-4 max-w-xs">
                            @if($order->return_reason)
                                <p class="text-gray-800 dark:text-gray-200 line-clamp-2 leading-relaxed" title="{{ $order->return_reason }}">
                                    {{ $order->return_reason }}
                                </p>
                            @else
                                <span class="text-gray-400 italic">No note provided</span>
                            @endif
                            @if($order->return_admin_note)
                                <p class="text-[10px] text-gray-500 mt-1 italic">Admin: "{{ Str::limit($order->return_admin_note, 30) }}"</p>
                            @endif
                        </td>

                        {{-- Total --}}
                        <td class="py-3 px-4">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $currency }}{{ number_format($order->total, 2) }}</p>
                            @if($order->refund_amount > 0)
                                <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">Refund: {{ $currency }}{{ number_format($order->refund_amount, 2) }}</p>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="py-3 px-4">
                            @if($order->return_status === 'requested')
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                                    Requested
                                </span>
                            @elseif($order->status === 'refunded')
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                                    Refunded
                                </span>
                            @elseif($order->status === 'returned')
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200">
                                    Returned
                                </span>
                            @elseif($order->return_status === 'rejected')
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-200">
                                    Declined
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700">
                                    {{ ucfirst($order->status) }}
                                </span>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td class="py-3 px-4 text-gray-500 font-mono text-[11px]">
                            @if($order->return_requested_at)
                                {{ $order->return_requested_at->format('M j, Y') }}
                            @else
                                {{ $order->updated_at->format('M j, Y') }}
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                @if($order->return_status === 'requested')
                                    <form action="{{ route('admin.returns.approve', $order) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action_type" value="refund">
                                        <input type="hidden" name="refund_amount" value="{{ $order->total }}">
                                        <button type="button" onclick="confirmRefundModal(this, '{{ $order->order_number }}', '{{ $currency }}{{ number_format($order->total, 2) }}')"
                                                class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-2xs transition"
                                                title="Approve refund and restock">
                                            ✓ Refund
                                        </button>
                                    </form>

                                    @if($order->return_request_type === 'return')
                                    <form action="{{ route('admin.returns.approve', $order) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action_type" value="return">
                                        <button type="button" onclick="confirmReturnModal(this, '{{ $order->order_number }}')"
                                                class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] shadow-2xs transition"
                                                title="Accept return and restock">
                                            ✓ Return
                                        </button>
                                    </form>
                                    @endif
                                @endif
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold text-[11px] transition">
                                    View Details →
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-400">
                            No return or refund records found in this view.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-3">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
