@extends('admin.layouts.app')
@section('title', 'Order #' . $order->order_number)
@php
    $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳');
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.orders.index') }}" class="hover:underline">Orders</a>
                <span>/</span>
                <span class="font-mono">#{{ $order->order_number }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                Order #{{ $order->order_number }}
                @php
                    $statusClasses = [
                        'pending'          => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300',
                        'confirmed'        => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300',
                        'processing'       => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-300',
                        'shipped'          => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300',
                        'out-for-delivery' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/20 dark:text-cyan-300',
                        'delivered'        => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-300',
                        'completed'        => 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300',
                        'cancelled'        => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300',
                        'returned'         => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/20 dark:text-rose-300',
                        'refunded'         => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300',
                    ];
                @endphp
                <span class="text-xs font-semibold px-3 py-1 rounded-full border {{ $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst(str_replace('-', ' ', $order->status)) }}
                </span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">Placed on {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            &larr; Back to Orders
        </a>
    </div>

    @if(isset($errors) && $errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl p-4 text-sm">
        @foreach($errors->all() as $err)
            <p>{{ $err }}</p>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Items & Calculation -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-base">Items Ordered ({{ $order->items->count() }})</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($order->items as $item)
                    @php
                        $itemSku = $item->product_sku ?: $item->product?->sku;
                    @endphp
                    <div class="p-5 flex items-center gap-4">
                        <img src="{{ $item->product?->thumbnail_url ?? asset('images/placeholder-product.jpg') }}" alt="{{ $item->product_name }}" class="w-16 h-16 rounded-xl object-cover border border-gray-200 dark:border-gray-700 bg-gray-50 shrink-0">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-sm text-gray-900 dark:text-white truncate">
                                @if($item->product)
                                    <a href="{{ route('admin.products.edit', $item->product) }}" class="hover:text-brand-600 transition" title="Edit Product">
                                        {{ $item->product_name }}@if(!empty($itemSku)) <span class="text-xs font-mono font-normal text-gray-500 dark:text-gray-400">({{ $itemSku }})</span>@endif
                                    </a>
                                @else
                                    {{ $item->product_name }}@if(!empty($itemSku)) <span class="text-xs font-mono font-normal text-gray-500 dark:text-gray-400">({{ $itemSku }})</span>@endif
                                @endif
                            </h4>
                            @if(!empty($item->options_summary))
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    @if(!empty($item->selected_size))
                                        <span class="inline-flex items-center text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900 px-2 py-0.5 rounded-md">
                                            Size: {{ $item->selected_size }}
                                        </span>
                                    @endif
                                    @if(!empty($item->selected_color))
                                        <span class="inline-flex items-center text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900 px-2 py-0.5 rounded-md">
                                            Color: {{ $item->selected_color }}
                                        </span>
                                    @endif
                                    @if(!empty($item->selected_shade))
                                        <span class="inline-flex items-center text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900 px-2 py-0.5 rounded-md">
                                            Shade: {{ $item->selected_shade }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">Price: {{ $currency }}{{ number_format($item->unit_price ?? $item->price, 2) }} &times; {{ $item->quantity }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-sm text-gray-900 dark:text-white">{{ $currency }}{{ number_format($item->total ?? $item->subtotal, 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="p-5 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Subtotal</span>
                        <span>{{ $currency }}{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if(($order->discount ?? $order->discount_amount ?? 0) > 0)
                    <div class="flex justify-between text-green-600 dark:text-green-400">
                        <span>Discount @if($order->coupon_code)<span class="font-mono text-xs font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 px-1.5 py-0.5 rounded">({{ $order->coupon_code }})</span>@endif</span>
                        <span>-{{ $currency }}{{ number_format($order->discount ?? $order->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Shipping Cost @if($order->shipping_title)<span class="text-xs text-gray-400">({{ $order->shipping_title }})</span>@endif</span>
                        <span>{{ $currency }}{{ number_format($order->shipping_cost, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-base text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                        <span>Grand Total</span>
                        <span>{{ $currency }}{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Status Transition History -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-base flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Status & Audit History</span>
                    </h3>
                    <span class="text-xs text-gray-400">Chronological Progress</span>
                </div>

                <div class="relative pl-1">
                    @forelse($order->statusHistories as $history)
                    @php
                        $isLatest = $loop->last;
                    @endphp
                    <div class="relative flex items-start gap-4 pb-8 last:pb-1">
                        {{-- Connecting vertical line from one node to the next --}}
                        @if(!$loop->last)
                            <div class="absolute left-3.5 top-7 -bottom-1 w-0.5 bg-gradient-to-b from-brand-600 via-brand-400 to-stone-200 dark:from-brand-500 dark:via-brand-700 dark:to-gray-700"></div>
                        @endif

                        {{-- Timeline Dot / Badge --}}
                        <div class="relative z-10 w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-all
                            {{ $isLatest ? 'bg-brand-700 text-white ring-4 ring-brand-700/25 shadow-md' : 'bg-emerald-600 text-white shadow-xs' }}">
                            @if($isLatest)
                                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            @else
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </div>

                        {{-- Event Details --}}
                        <div class="flex-1 min-w-0 pt-0.5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-sm text-gray-900 dark:text-white capitalize">
                                        {{ str_replace('-', ' ', $history->status) }}
                                    </span>
                                    @if($isLatest)
                                        <span class="text-[10px] font-bold text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-slate-950 border border-brand-200 dark:border-brand-800 px-2 py-0.5 rounded-full">
                                            Current Status
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400 font-mono">
                                    {{ $history->created_at->format('M j, Y • g:i A') }}
                                </span>
                            </div>

                            @if($history->note)
                                <div class="mt-1.5 p-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 text-xs text-gray-700 dark:text-gray-300">
                                    {{ $history->note }}
                                </div>
                            @endif

                            <div class="mt-1 text-[11px] text-gray-400 flex items-center gap-1">
                                <span>Updated by:</span>
                                <span class="font-semibold text-gray-600 dark:text-gray-300">{{ $history->changed_by }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                        <p class="text-xs text-gray-500">No status changes recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Side: Actions, Payment, Customer -->
        <div class="space-y-6">
            <!-- Update Order Status -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-base">Update Order Status</h3>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border {{ $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst(str_replace('-', ' ', $order->status)) }}
                    </span>
                </div>
                
                <form action="{{ route('admin.orders.change-status', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="status" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Status</span>
                            <span class="text-[11px] font-mono text-brand-600 dark:text-brand-400">Current: {{ ucfirst(str_replace('-', ' ', $order->status)) }}</span>
                        </label>
                        <select name="status" id="status" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                            <option value="">-- Choose New Status --</option>
                            <optgroup label="Fulfillment Pipeline">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>
                                    Pending {{ $order->status === 'pending' ? '✓ (Current)' : '' }}
                                </option>
                                <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>
                                    Confirmed {{ $order->status === 'confirmed' ? '✓ (Current)' : '' }}
                                </option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>
                                    Processing {{ $order->status === 'processing' ? '✓ (Current)' : '' }}
                                </option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>
                                    Shipped {{ $order->status === 'shipped' ? '✓ (Current)' : '' }}
                                </option>
                                <option value="out-for-delivery" {{ $order->status === 'out-for-delivery' ? 'selected' : '' }}>
                                    Out for delivery {{ $order->status === 'out-for-delivery' ? '✓ (Current)' : '' }}
                                </option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>
                                    Delivered {{ $order->status === 'delivered' ? '✓ (Current)' : '' }}
                                </option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>
                                    Completed {{ $order->status === 'completed' ? '✓ (Current)' : '' }}
                                </option>
                            </optgroup>
                            <optgroup label="Exceptions & Returns">
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>
                                    Cancelled {{ $order->status === 'cancelled' ? '✓ (Current)' : '' }}
                                </option>
                                <option value="returned" {{ $order->status === 'returned' ? 'selected' : '' }}>
                                    Returned {{ $order->status === 'returned' ? '✓ (Current)' : '' }}
                                </option>
                                <option value="refunded" {{ $order->status === 'refunded' ? 'selected' : '' }}>
                                    Refunded {{ $order->status === 'refunded' ? '✓ (Current)' : '' }}
                                </option>
                            </optgroup>
                        </select>
                    </div>

                    {{-- Delivery Options Configured in Settings --}}
                    @if(!empty($deliveryCharges) && count($deliveryCharges) > 0)
                    <div>
                        <label for="delivery_option" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Delivery Option</span>
                            <span class="text-[10px] text-gray-400 font-normal lowercase">(from settings)</span>
                        </label>
                        <select name="delivery_option" id="delivery_option" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                            <option value="">Keep Current ({{ $order->shipping_title ?? 'Standard Delivery' }})</option>
                            @foreach($deliveryCharges as $charge)
                                @php
                                    $cTitle = is_array($charge) ? ($charge['title'] ?? '') : $charge;
                                    $cAmount = is_array($charge) ? ($charge['amount'] ?? '') : '';
                                @endphp
                                @if(!empty($cTitle))
                                    <option value="{{ $cTitle }}" {{ ($order->shipping_title === $cTitle) ? 'selected' : '' }}>
                                        {{ $cTitle }} {{ $cAmount !== '' ? '(' . $currency . number_format((float)$cAmount) . ')' : '' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Configured in Admin Settings &rarr; Shipping & Delivery.</p>
                    </div>
                    @endif

                    <button type="submit" class="w-full bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold py-2.5 rounded-xl transition shadow-sm">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- Payment Details & Verification -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm space-y-4">
                <h3 class="font-semibold text-gray-900 dark:text-white text-base border-b border-gray-100 dark:border-gray-800 pb-3">Payment Information</h3>

                @if($order->payment)
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Method</span>
                        <span class="font-semibold text-gray-900 dark:text-white uppercase">{{ $order->payment->method }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="font-semibold text-brand-600">{{ ucfirst($order->payment->status) }}</span>
                    </div>
                    @if($order->payment->transaction_id)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Transaction ID</span>
                        <span class="font-mono text-xs text-gray-700 dark:text-gray-300">{{ $order->payment->transaction_id }}</span>
                    </div>
                    @endif
                    @if($order->payment->customer_reference)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Reference / Trx</span>
                        <span class="font-mono text-xs text-gray-700 dark:text-gray-300">{{ $order->payment->customer_reference }}</span>
                    </div>
                    @endif
                    @if($order->payment->admin_note)
                    <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-300">
                        <strong class="block text-gray-500 mb-0.5">Admin Note:</strong>
                        {{ $order->payment->admin_note }}
                    </div>
                    @endif
                </div>

                @if($order->payment->status === 'pending')
                <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-2">
                    <form action="{{ route('admin.orders.verify-payment', $order) }}" method="POST" onsubmit="return confirm('Verify and confirm this payment?');">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-2 rounded-xl transition">
                            ✓ Verify Payment
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.orders.reject-payment', $order) }}" method="POST" class="space-y-2" onsubmit="return confirm('Reject this payment?');">
                        @csrf
                        <input type="text" name="admin_note" required placeholder="Reason for rejection..." class="w-full px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-xs font-semibold py-2 rounded-xl transition">
                            ✕ Reject Payment
                        </button>
                    </form>
                </div>
                @endif
                @else
                <p class="text-xs text-gray-500">No payment record attached.</p>
                @endif
            </div>

            <!-- Customer & Shipping -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-base">Shipping Address</h3>
                    @if($order->has_coordinates)
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 px-2.5 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            GPS Pinned
                        </span>
                    @endif
                </div>
                
                <div class="text-sm space-y-1 text-gray-700 dark:text-gray-300">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $order->shipping_name }}</p>
                    <p class="text-xs text-gray-500">
                        <a href="mailto:{{ $order->shipping_email }}">{{ $order->shipping_email }}</a>    
                        &bull;
                        <a href="tel:+{{ $order->shipping_phone }}">{{ $order->shipping_phone }}</a>
                    </p>
                    <p class="pt-2 text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                        {{ $order->shipping_address }}<br>
                        {{ $order->shipping_city }}{{ $order->shipping_state ? ', ' . $order->shipping_state : '' }} {{ $order->shipping_zip ?? $order->shipping_postal_code }}<br>
                        {{ $order->shipping_country }}
                    </p>
                </div>

                {{-- Map Location --}}
                <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            Delivery Map Location
                        </span>
                        @if($order->has_coordinates)
                            <span class="font-mono text-[11px] text-gray-400">
                                {{ number_format($order->effective_latitude, 5) }}, {{ number_format($order->effective_longitude, 5) }}
                            </span>
                        @endif
                    </div>

                    @if($order->has_coordinates)
                        {{-- Interactive Mini Map for Admin --}}
                        <div id="admin-order-map" class="w-full h-44 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-xs relative z-0"></div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ $order->google_maps_url }}" target="_blank"
                               class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>Google Maps</span>
                            </a>
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $order->effective_latitude }},{{ $order->effective_longitude }}" target="_blank"
                               class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                <span>Directions</span>
                            </a>
                        </div>
                    @else
                        <a href="{{ $order->google_maps_url }}" target="_blank"
                           class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold text-xs border border-gray-200 dark:border-gray-700 transition">
                            <svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            <span>Search Address on Google Maps ↗</span>
                        </a>
                    @endif
                </div>

                {{-- Customer Order Notes Section --}}
                @if(!empty($order->notes) || !empty($order->order_notes))
                <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                    <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80">
                        <span class="text-xs font-bold text-amber-900 dark:text-amber-300 flex items-center gap-1.5 mb-1">
                            <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            Customer Delivery Note:
                        </span>
                        <p class="text-xs text-amber-950 dark:text-amber-100 font-medium whitespace-pre-line leading-relaxed">
                            {{ $order->notes ?? $order->order_notes }}
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($order->has_coordinates)
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mapEl = document.getElementById('admin-order-map');
                if (!mapEl) return;

                const lat = {{ (float) $order->effective_latitude }};
                const lng = {{ (float) $order->effective_longitude }};

                const map = L.map('admin-order-map', {
                    scrollWheelZoom: false
                }).setView([lat, lng], 16);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);

                const icon = L.icon({
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                const marker = L.marker([lat, lng], { icon: icon }).addTo(map);
                marker.bindPopup('<strong>{{ addslashes($order->shipping_name) }}</strong><br>{{ addslashes($order->shipping_address) }}').openPopup();

                setTimeout(() => map.invalidateSize(), 300);
            });
        </script>
    @endpush
@endif
@endsection
