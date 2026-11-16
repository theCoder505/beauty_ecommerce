@extends('admin.layouts.app')
@section('title', 'User Details – ' . $user->name)
@php
    $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳');
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="p-2 rounded-xl border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Customer Profile & Order History</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.users.toggle-block', $user->id) }}"
                  onsubmit="return confirm('{{ $user->is_blocked ? "Unblock {$user->name}?" : "Block {$user->name}? The user will be logged out and cannot log in until unblocked." }}')">
                @csrf
                @if($user->is_blocked)
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Unblock User
                    </button>
                @else
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-amber-600 hover:bg-amber-700 text-white transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Block User
                    </button>
                @endif
            </form>

            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                  onsubmit="return confirm('Are you sure you want to permanently remove this user? All their details will be deleted.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Remove
                </button>
            </form>
        </div>
    </div>

    {{-- Block Warning Alert --}}
    @if($user->is_blocked)
        <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 rounded-2xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-red-900 dark:text-red-200">Account Is Blocked</p>
                <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">
                    Blocked at: {{ $user->blocked_at ? $user->blocked_at->format('M d, Y h:i A') : 'N/A' }}.
                    @if($user->blocked_reason)
                        <span class="block mt-1">Reason: <em>{{ $user->blocked_reason }}</em></span>
                    @endif
                    The user cannot access the storefront or dashboard and is asked to contact admin via the contact page.
                </p>
            </div>
        </div>
    @endif

    {{-- Top Overview Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Profile Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover border-2 border-brand-500 shadow-sm">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    <div class="mt-2">
                        @if($user->is_blocked)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">BLOCKED</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">ACTIVE</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800 space-y-3 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-500">Phone</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $user->phone ?: 'Not provided' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Member Since</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Auth Provider</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100 capitalize">{{ $user->provider ?: 'Email & Password' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Lifetime Spend</span>
                    <span class="font-bold text-brand-700 dark:text-brand-400">{{ $currency }}{{ number_format($totalSpent) }}</span>
                </div>
            </div>
        </div>

        {{-- Saved Delivery Addresses --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm md:col-span-2">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Saved Delivery Addresses ({{ $user->addresses->count() }})</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @forelse($user->addresses as $address)
                    <div class="p-3.5 rounded-xl border {{ $address->is_primary ? 'border-brand-500 bg-brand-50/20 dark:bg-brand-950/20' : 'border-gray-200 dark:border-gray-800' }} text-xs">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-[10px]">{{ $address->title ?? 'Address' }}</span>
                            @if($address->is_primary)
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-brand-700 text-white">Default</span>
                            @endif
                        </div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ $address->recipient_name }} ({{ $address->phone }})</p>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $address->street_address }}</p>
                        <p class="text-gray-500 text-[11px] mt-0.5">{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 col-span-2 py-4">No saved addresses on file.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Orders History --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <h3 class="font-bold text-sm text-gray-900 dark:text-white">Recent Orders ({{ $user->orders->count() }})</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-gray-800/50 uppercase text-gray-500 border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-6 py-3">Order Number</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Recipient</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">View</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($user->orders as $order)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        <td class="px-6 py-3 font-semibold text-brand-700 dark:text-brand-400">
                            #{{ $order->order_number }}
                        </td>
                        <td class="px-6 py-3 text-gray-500">
                            {{ $order->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                            {{ $order->shipping_name }}
                        </td>
                        <td class="px-6 py-3 font-bold text-gray-900 dark:text-white">
                            {{ $currency }}{{ number_format($order->total) }}
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 uppercase">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-brand-600 hover:text-brand-700 font-semibold">
                                View Order →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            This user has not placed any orders yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
