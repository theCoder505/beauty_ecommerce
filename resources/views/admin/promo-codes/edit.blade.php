@extends('admin.layouts.app')
@section('title', 'Edit Promo Code')

@php
    $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳');
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="promoCodeForm({
    applyTo: '{{ old('apply_to', $promoCode->apply_to) }}',
    type: '{{ old('type', $promoCode->type) }}'
})">
    {{-- Breadcrumb & Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.promo-codes.index') }}" class="hover:text-brand-700">Promo Codes</a>
                <span>/</span>
                <span>Edit {{ $promoCode->code }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Promo Code: {{ $promoCode->code }}</h1>
        </div>
        <a href="{{ route('admin.promo-codes.index') }}" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
            ← Back to List
        </a>
    </div>

    @if(isset($errors) && $errors->any())
    <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-2xl p-4 text-xs space-y-1">
        <p class="font-bold">Please correct the following errors:</p>
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.promo-codes.update', $promoCode) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Basic Information Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 sm:p-8 border border-gray-200 dark:border-gray-800 shadow-sm space-y-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Coupon Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Promo Code --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Promo Code *</label>
                    <input type="text"
                           name="code"
                           value="{{ old('code', $promoCode->code) }}"
                           required
                           placeholder="e.g. SUMMER50, BEAUTYFREE"
                           @input="$event.target.value = $event.target.value.toUpperCase()"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-mono font-bold uppercase focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <p class="text-[11px] text-gray-400 mt-1">Customers enter this code at checkout.</p>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Description (Internal / Note)</label>
                    <input type="text"
                           name="description"
                           value="{{ old('description', $promoCode->description) }}"
                           placeholder="e.g. 100% off for influencer trial or flash campaign"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>
            </div>

            {{-- Type & Value --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Discount Type *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition text-xs font-semibold"
                               :class="type === 'fixed' ? 'border-brand-700 bg-brand-50/40 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                            <input type="radio" name="type" value="fixed" x-model="type" class="text-brand-700 focus:ring-brand-600">
                            <span>Fixed Amount ({{ $currency }})</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition text-xs font-semibold"
                               :class="type === 'percentage' ? 'border-brand-700 bg-brand-50/40 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                            <input type="radio" name="type" value="percentage" x-model="type" class="text-brand-700 focus:ring-brand-600">
                            <span>Percentage (%)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        <span x-text="type === 'percentage' ? 'Percentage Value (%) *' : 'Discount Amount ({{ $currency }}) *'"></span>
                    </label>
                    <input type="number"
                           name="value"
                           value="{{ old('value', $promoCode->value) }}"
                           required
                           min="0.01"
                           step="any"
                           placeholder="e.g. 20 or 120"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <p class="text-[11px] text-gray-400 mt-1" x-show="type === 'fixed'">
                        Note: If discount exceeds product price (e.g. price 100, promo 120), product is 100% free.
                    </p>
                </div>
            </div>
        </div>

        {{-- Product Applicability Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 sm:p-8 border border-gray-200 dark:border-gray-800 shadow-sm space-y-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Product Applicability</h3>

            <div class="space-y-4">
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition text-xs font-semibold flex-1"
                           :class="applyTo === 'all' ? 'border-brand-700 bg-brand-50/40 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                        <input type="radio" name="apply_to" value="all" x-model="applyTo" class="text-brand-700 focus:ring-brand-600">
                        <div>
                            <p class="font-bold">All Products</p>
                            <p class="text-[11px] font-normal text-gray-400">Coupon applies to all store products</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition text-xs font-semibold flex-1"
                           :class="applyTo === 'specific' ? 'border-brand-700 bg-brand-50/40 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                        <input type="radio" name="apply_to" value="specific" x-model="applyTo" class="text-brand-700 focus:ring-brand-600">
                        <div>
                            <p class="font-bold">Specific Products Only</p>
                            <p class="text-[11px] font-normal text-gray-400">Coupon applies only to selected items</p>
                        </div>
                    </label>
                </div>

                {{-- Specific Products Multi-Selector --}}
                @php
                    $selectedIds = old('product_ids', $promoCode->product_ids ?? []);
                @endphp
                <div x-show="applyTo === 'specific'" x-cloak class="space-y-3 pt-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Select Eligible Products:
                        </label>
                        <input type="text"
                               x-model="productSearch"
                               placeholder="Filter products…"
                               class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    </div>

                    <div class="max-h-60 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-800 p-2 bg-gray-50/50 dark:bg-gray-800/20">
                        @foreach($products as $p)
                        <label class="flex items-center justify-between p-2 rounded-lg hover:bg-white dark:hover:bg-gray-800 cursor-pointer transition text-xs"
                               x-show="!productSearch || '{{ strtolower(addslashes($p->name . ' ' . $p->sku)) }}'.includes(productSearch.toLowerCase())">
                            <div class="flex items-center gap-3">
                                <input type="checkbox"
                                       name="product_ids[]"
                                       value="{{ $p->id }}"
                                       {{ in_array($p->id, $selectedIds) ? 'checked' : '' }}
                                       class="rounded text-brand-700 focus:ring-brand-600">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $p->name }}</p>
                                    <p class="text-[11px] text-gray-400">SKU: {{ $p->sku }}</p>
                                </div>
                            </div>
                            <span class="font-bold text-gray-700 dark:text-gray-300">{{ $currency }}{{ number_format($p->current_price) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Restrictions & Limits Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 sm:p-8 border border-gray-200 dark:border-gray-800 shadow-sm space-y-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Restrictions & Usage Limits</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Min Order Spend ({{ $currency }})</label>
                    <input type="number"
                           name="min_spend"
                           value="{{ old('min_spend', $promoCode->min_spend) }}"
                           min="0"
                           step="any"
                           placeholder="e.g. 500 (optional)"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Max Discount Cap ({{ $currency }})</label>
                    <input type="number"
                           name="max_discount"
                           value="{{ old('max_discount', $promoCode->max_discount) }}"
                           min="0"
                           step="any"
                           placeholder="e.g. 250 (for % type)"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Usage Limit (Total)</label>
                    <input type="number"
                           name="usage_limit"
                           value="{{ old('usage_limit', $promoCode->usage_limit) }}"
                           min="1"
                           placeholder="e.g. 100 (blank for ∞)"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Starts At</label>
                    <input type="datetime-local"
                           name="starts_at"
                           value="{{ old('starts_at', $promoCode->starts_at ? $promoCode->starts_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Expires At</label>
                    <input type="datetime-local"
                           name="expires_at"
                           value="{{ old('expires_at', $promoCode->expires_at ? $promoCode->expires_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>
            </div>

            <div class="pt-2">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promoCode->is_active ? '1' : '0') == '1' ? 'checked' : '' }} class="rounded text-brand-700 focus:ring-brand-600">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Enable this promo code</span>
                </label>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.promo-codes.index') }}" class="px-6 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                Cancel
            </a>
            <button type="submit" class="px-8 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-sm font-bold shadow-md transition">
                Update Promo Code
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function promoCodeForm(config) {
    return {
        applyTo: config.applyTo || 'all',
        type: config.type || 'fixed',
        productSearch: '',
    };
}
</script>
@endpush
@endsection
