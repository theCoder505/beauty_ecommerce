@extends('admin.layouts.app')
@section('title', 'Promo Codes')

@php
    $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳');
@endphp

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Promo Codes & Discounts</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Create coupons for percentage or fixed discounts on all or specific products</p>
        </div>
        <a href="{{ route('admin.promo-codes.create') }}" class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow-sm self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span>Create Promo Code</span>
        </a>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl p-4 border border-gray-200 dark:border-gray-800 shadow-sm">
        <form method="GET" action="{{ route('admin.promo-codes.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search by code or description…"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-600">
            </div>

            <div>
                <select name="type" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-600">
                    <option value="">All Discount Types</option>
                    <option value="fixed" {{ request('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                    <option value="percentage" {{ request('type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                </select>
            </div>

            <div class="flex gap-2">
                <select name="status" class="flex-1 px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-600">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>

                <button type="submit" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-semibold transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto p-4">
            <table id="promo-codes-datatable" class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                <thead class="bg-gray-50/75 dark:bg-gray-800/50 text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wider border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-6 py-4">Promo Code</th>
                        <th class="px-6 py-4">Discount</th>
                        <th class="px-6 py-4">Applies To</th>
                        <th class="px-6 py-4">Usage</th>
                        <th class="px-6 py-4">Validity</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($promoCodes as $code)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        {{-- Code --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-sm bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 px-3 py-1 rounded-lg border border-brand-700/20">
                                    {{ $code->code }}
                                </span>
                            </div>
                            @if($code->description)
                            <p class="text-xs text-gray-400 mt-1 max-w-xs truncate">{{ $code->description }}</p>
                            @endif
                        </td>

                        {{-- Value / Type --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-gray-900 dark:text-white">
                                @if($code->type === 'percentage')
                                    {{ rtrim(rtrim($code->value, '0'), '.') }}% OFF
                                    @if($code->max_discount)
                                    <span class="block text-[11px] font-normal text-gray-400">Max {{ $currency }}{{ number_format($code->max_discount) }}</span>
                                    @endif
                                @else
                                    {{ $currency }}{{ number_format($code->value) }} OFF
                                @endif
                            </div>
                            @if($code->min_spend)
                            <span class="text-[11px] text-gray-400">Min spend: {{ $currency }}{{ number_format($code->min_spend) }}</span>
                            @endif
                        </td>

                        {{-- Applies to --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($code->apply_to === 'all')
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-full">
                                <span>🌐 All Products</span>
                            </span>
                            @else
                            @php $count = count($code->product_ids ?? []); @endphp
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2.5 py-1 rounded-full">
                                <span>🎯 {{ $count }} Specific {{ Str::plural('Product', $count) }}</span>
                            </span>
                            @endif
                        </td>

                        {{-- Usage --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $code->used_count }}</span>
                            <span class="text-xs text-gray-400">/ {{ $code->usage_limit ? $code->usage_limit : '∞' }} uses</span>
                        </td>

                        {{-- Validity / Expiry --}}
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            @if($code->expires_at)
                                @if($code->isExpired())
                                <span class="text-red-500 font-semibold">Expired ({{ $code->expires_at->format('M d, Y') }})</span>
                                @else
                                <span class="text-gray-600 dark:text-gray-300">Until {{ $code->expires_at->format('M d, Y') }}</span>
                                @endif
                            @else
                                <span class="text-gray-400">No expiration</span>
                            @endif
                        </td>

                        {{-- Status & Toggle --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form action="{{ route('admin.promo-codes.toggle', $code) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition {{ $code->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 hover:bg-emerald-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 hover:bg-gray-200' }}" title="Click to toggle status">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $code->is_active ? 'bg-emerald-600' : 'bg-gray-400' }}"></span>
                                    <span>{{ $code->is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </form>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.promo-codes.edit', $code) }}" class="text-gray-600 dark:text-gray-300 hover:text-brand-700 dark:hover:text-brand-400 transition">
                                    Edit
                                </a>
                                <form action="{{ route('admin.promo-codes.destroy', $code) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete promo code {{ $code->code }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 transition">
                                        Delete
                                    </button>
                                </form>
                            </div>
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
        $('#promo-codes-datatable tbody tr td[colspan]').closest('tr').remove();
        $('#promo-codes-datatable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [6] }
            ],
            language: {
                emptyTable: "No promo codes found. Create your first coupon code to start running promotions.",
                zeroRecords: "No matching promo codes found.",
                search: "_INPUT_",
                searchPlaceholder: "Search promo codes...",
                paginate: { previous: "‹ Prev", next: "Next ›" }
            }
        });
    });
</script>
@endpush
