@extends('admin.layouts.app')
@section('title', 'Orders')
@php
    $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳');
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Customer Orders</h1>
            <p class="text-sm text-gray-500 mt-1">Review orders, verify payments, and manage fulfillment.</p>
        </div>
    </div>

    <!-- Status Quick-Filter Pills -->
    <div class="flex flex-wrap items-center gap-2 overflow-x-auto pb-1 -mt-1 scrollbar-thin">
        @php
            $currentStatus = request('status');
            $allStatusesMap = \App\Services\OrderService::allStatuses();
            $pillBadges = [
                'pending'          => ['dot' => 'bg-amber-500', 'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200'],
                'confirmed'        => ['dot' => 'bg-blue-500', 'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200'],
                'processing'       => ['dot' => 'bg-indigo-500', 'badge' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200'],
                'shipped'          => ['dot' => 'bg-purple-500', 'badge' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200'],
                'out-for-delivery' => ['dot' => 'bg-cyan-500', 'badge' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/50 dark:text-cyan-200'],
                'delivered'        => ['dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200'],
                'completed'        => ['dot' => 'bg-green-500', 'badge' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200'],
                'cancelled'        => ['dot' => 'bg-red-500', 'badge' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200'],
                'returned'         => ['dot' => 'bg-rose-500', 'badge' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-200'],
                'refunded'         => ['dot' => 'bg-gray-500', 'badge' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'],
            ];
        @endphp
        <a href="{{ route('admin.orders.index', request()->except('status')) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all {{ empty($currentStatus) ? 'bg-brand-700 text-white shadow-sm ring-2 ring-brand-700/20' : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
            <span>All Orders</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ empty($currentStatus) ? 'bg-brand-800/80 text-white font-bold' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-bold' }}">
                {{ $totalOrdersCount ?? 0 }}
            </span>
        </a>

        @foreach($allStatusesMap as $stKey => $stLabel)
            @php
                $isActive = $currentStatus === $stKey;
                $count = $statusCounts[$stKey] ?? 0;
                $style = $pillBadges[$stKey] ?? ['dot' => 'bg-gray-400', 'badge' => 'bg-gray-100 text-gray-700'];
            @endphp
            <a href="{{ route('admin.orders.index', array_merge(request()->except('status'), ['status' => $stKey])) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all {{ $isActive ? 'bg-brand-700 text-white shadow-sm ring-2 ring-brand-700/20' : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                <span class="w-2 h-2 rounded-full {{ $isActive ? 'bg-white' : $style['dot'] }}"></span>
                <span>{{ $stLabel }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-brand-800/80 text-white' : ($count > 0 ? $style['badge'] : 'bg-gray-100 dark:bg-gray-800 text-gray-400') }}">
                    {{ $count }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, name, or email..." class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
            </div>
            <div>
                <select name="status" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('-', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="fraud_risk" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <option value="">All Risk Levels</option>
                    <option value="high" {{ request('fraud_risk') === 'high' ? 'selected' : '' }}>High Risk (Score ≥ 60)</option>
                    <option value="medium" {{ request('fraud_risk') === 'medium' ? 'selected' : '' }}>Medium Risk (Score 30-59)</option>
                    <option value="low" {{ request('fraud_risk') === 'low' ? 'selected' : '' }}>Low Risk (Score &lt; 30)</option>
                </select>
            </div>
            <div>
                <select name="payment_method" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <option value="">All Payment Methods</option>
                    <option value="cod" {{ request('payment_method') === 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                    <option value="manual" {{ request('payment_method') === 'manual' ? 'selected' : '' }}>Manual / Bank Transfer</option>
                    <option value="stripe" {{ request('payment_method') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                </select>
            </div>
            <div>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none" title="From Date">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">Filter</button>
                @if(request()->anyFilled(['search', 'status', 'fraud_risk', 'payment_method', 'date_from', 'date_to']))
                    <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm transition flex items-center justify-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto p-4">
            <table id="orders-datatable" class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4">Order #</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Total</th>
                        <th class="px-6 py-4">Payment</th>
                        <th class="px-6 py-4">Fraud Risk</th>
                        <th class="px-6 py-4">Order Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($orders as $o)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        <td class="px-6 py-4 font-mono font-semibold text-gray-900 dark:text-white">
                            <a href="{{ route('admin.orders.show', $o) }}" class="hover:text-brand-600 transition">
                                #{{ $o->order_number }}
                            </a>
                            <span class="block text-xs text-gray-400 font-sans font-normal"
                                @if($o->items->isNotEmpty())
                                    title="{{ $o->items->map(fn($it) => $it->product_name . (!empty($it->product_sku ?: $it->product?->sku) ? ' (' . ($it->product_sku ?: $it->product?->sku) . ')' : ''))->implode(', ') }}"
                                @endif
                            >
                                {{ $o->items_count }} items
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900 dark:text-white block">{{ $o->shipping_name ?? $o->user?->name ?? 'Customer' }}</span>
                            <span class="text-xs text-gray-500">{{ $o->shipping_email ?? $o->user?->email }}</span>
                            <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                @if($o->has_coordinates)
                                    <a href="{{ $o->google_maps_url }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 px-1.5 py-0.5 rounded hover:bg-emerald-100 transition" title="Open GPS Location">
                                        📍 GPS Map
                                    </a>
                                @endif
                                @if(!empty($o->notes) || !empty($o->order_notes))
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 px-1.5 py-0.5 rounded" title="Customer Note: {{ $o->notes ?? $o->order_notes }}">
                                        📝 Note
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">
                            {{ $o->created_at->format('M j, Y') }}
                            <span class="block text-gray-400">{{ $o->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                            {{ $currency }}{{ number_format($o->total, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-medium uppercase block text-gray-700 dark:text-gray-300">
                                {{ $o->payment?->method ?? 'N/A' }}
                            </span>
                            @php
                                $pStatus = $o->payment?->status ?? 'pending';
                                $pColors = [
                                    'verified' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                    'paid'     => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                    'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                    'pending'  => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $pColors[$pStatus] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($pStatus) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $riskLevel = $o->fraud_risk_level ?? 'low';
                                $riskScore = $o->fraud_risk_score ?? 0;
                                $riskBadgeClasses = [
                                    'high'   => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800/60',
                                    'medium' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60',
                                    'low'    => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60',
                                ];
                            @endphp
                            <div class="inline-flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $riskBadgeClasses[$riskLevel] ?? $riskBadgeClasses['low'] }}" title="Risk Score: {{ $riskScore }}/100">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $riskLevel === 'high' ? 'bg-red-500 animate-pulse' : ($riskLevel === 'medium' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                                    {{ ucfirst($riskLevel) }} ({{ $riskScore }})
                                </span>
                                @if(!empty($o->fraud_flags) && count($o->fraud_flags) > 0)
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono truncate max-w-[140px]" title="{{ implode(', ', $o->fraud_flags) }}">
                                        {{ count($o->fraud_flags) }} flag{{ count($o->fraud_flags) > 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $o->status_badge_classes }}">
                                {{ $o->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.orders.show', $o) }}" class="inline-flex items-center justify-center w-8 h-8 text-brand-700 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 dark:bg-brand-900/20 dark:hover:bg-brand-900/40 dark:text-brand-400 rounded-lg transition" title="View Order" aria-label="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#orders-datatable tbody tr td[colspan]').closest('tr').remove();
        $('#orders-datatable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[2, 'desc']],
            columnDefs: [
                { orderable: false, targets: [7] }
            ],
            language: {
                emptyTable: "No orders found matching your filters.",
                zeroRecords: "No matching orders found.",
                search: "_INPUT_",
                searchPlaceholder: "Search orders...",
                paginate: { previous: "‹ Prev", next: "Next ›" }
            }
        });
    });
</script>
@endpush

