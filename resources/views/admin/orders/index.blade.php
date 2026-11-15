@extends('admin.layouts.app')
@section('title', 'Orders')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Customer Orders</h1>
            <p class="text-sm text-gray-500 mt-1">Review orders, verify payments, and manage fulfillment.</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
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
                @if(request()->anyFilled(['search', 'status', 'payment_method', 'date_from', 'date_to']))
                    <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm transition flex items-center justify-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4">Order #</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Total</th>
                        <th class="px-6 py-4">Payment</th>
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
                            <span class="block text-xs text-gray-400 font-sans font-normal">{{ $o->items_count }} items</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900 dark:text-white block">{{ $o->shipping_name ?? $o->user?->name ?? 'Customer' }}</span>
                            <span class="text-xs text-gray-500">{{ $o->shipping_email ?? $o->user?->email }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">
                            {{ $o->created_at->format('M j, Y') }}
                            <span class="block text-gray-400">{{ $o->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                            ${{ number_format($o->total, 2) }}
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
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusClasses[$o->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst(str_replace('-', ' ', $o->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.orders.show', $o) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 dark:bg-brand-900/20 dark:hover:bg-brand-900/40 rounded-lg transition">Manage</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            No orders found matching your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

