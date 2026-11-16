@extends('admin.layouts.app')
@section('title', 'Order #' . $order->order_number)

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
                    <div class="p-5 flex items-center gap-4">
                        <img src="{{ $item->product?->thumbnail_url ?? asset('images/placeholder-product.jpg') }}" alt="{{ $item->product_name }}" class="w-16 h-16 rounded-xl object-cover border border-gray-200 dark:border-gray-700 bg-gray-50 shrink-0">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-sm text-gray-900 dark:text-white truncate">{{ $item->product_name }}</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Price: ${{ number_format($item->price, 2) }} &times; {{ $item->quantity }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-sm text-gray-900 dark:text-white">${{ number_format($item->subtotal, 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="p-5 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Subtotal</span>
                        <span>${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if(($order->discount ?? $order->discount_amount ?? 0) > 0)
                    <div class="flex justify-between text-green-600 dark:text-green-400">
                        <span>Discount @if($order->coupon_code)<span class="font-mono text-xs font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 px-1.5 py-0.5 rounded">({{ $order->coupon_code }})</span>@endif</span>
                        <span>-${{ number_format($order->discount ?? $order->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Shipping Cost @if($order->shipping_title)<span class="text-xs text-gray-400">({{ $order->shipping_title }})</span>@endif</span>
                        <span>${{ number_format($order->shipping_cost, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Tax</span>
                        <span>${{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-base text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                        <span>Grand Total</span>
                        <span>${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Status Transition History -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white text-base border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">Status & Audit History</h3>
                <div class="space-y-4">
                    @forelse($order->statusHistories as $history)
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-2.5 h-2.5 rounded-full bg-brand-500 mt-1.5 shrink-0"></div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-gray-900 dark:text-white capitalize">{{ str_replace('-', ' ', $history->status) }}</span>
                                <span class="text-xs text-gray-400">{{ $history->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                            @if($history->note)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $history->note }}</p>
                            @endif
                            <span class="text-[10px] text-gray-400">By: {{ $history->changed_by }}</span>
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
                <h3 class="font-semibold text-gray-900 dark:text-white text-base border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">Update Order Status</h3>
                
                @if(count($allowedTransitions) > 0)
                <form action="{{ route('admin.orders.change-status', $order) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label for="status" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">New Status</label>
                        <select name="status" id="status" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                            <option value="">Select Next Status</option>
                            @foreach($allowedTransitions as $trans)
                                <option value="{{ $trans }}">{{ ucfirst(str_replace('-', ' ', $trans)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="note" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Status Note (Optional)</label>
                        <textarea name="note" id="note" rows="2" placeholder="Tracking code, dispatch note, etc." class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold py-2.5 rounded-xl transition">
                        Update Status
                    </button>
                </form>
                @else
                <p class="text-xs text-gray-500">This order is at a terminal state (<span class="font-semibold">{{ $order->status }}</span>) and cannot be transitioned further.</p>
                @endif
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
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm space-y-3">
                <h3 class="font-semibold text-gray-900 dark:text-white text-base border-b border-gray-100 dark:border-gray-800 pb-3">Shipping Address</h3>
                
                <div class="text-sm space-y-1 text-gray-700 dark:text-gray-300">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $order->shipping_name }}</p>
                    <p class="text-xs text-gray-500">{{ $order->shipping_email }} &bull; {{ $order->shipping_phone }}</p>
                    <p class="pt-2 text-xs text-gray-600 dark:text-gray-400">
                        {{ $order->shipping_address }}<br>
                        {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}<br>
                        {{ $order->shipping_country }}
                    </p>
                </div>

                @if($order->order_notes)
                <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-xs font-semibold text-gray-500 block mb-1">Customer Note:</span>
                    <p class="text-xs text-gray-600 dark:text-gray-400 italic">"{{ $order->order_notes }}"</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
