@extends('users.layouts.app')
@php $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); @endphp
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8"
     x-data="{
        reviewModalOpen: false,
        reviewProductId: null,
        reviewProductName: '',
        reviewProductImage: '',
        reviewRating: 5,
        reviewBody: '',
        isEditReview: false,
        openReviewModal(id, name, image, rating = 5, body = '', isEdit = false) {
            this.reviewProductId = id;
            this.reviewProductName = name;
            this.reviewProductImage = image;
            this.reviewRating = rating || 5;
            this.reviewBody = body || '';
            this.isEditReview = isEdit;
            this.reviewModalOpen = true;
        },
        returnModalOpen: false,
        returnType: 'refund',
        cancelModalOpen: false,
     }">
    @include('users.partials.dashboard-sidebar')
    <main class="flex-1 min-w-0">
        <div class="flex items-center justify-between gap-3 mb-6 flex-wrap">
            <a href="{{ route('user.orders') }}" class="text-brand-700 hover:underline text-sm font-semibold flex items-center gap-1">
                <span>←</span> <span>My Orders</span>
            </a>
            <div class="flex items-center gap-2">
                @if($order->canBeCancelledByUser())
                    <button type="button" @click="cancelModalOpen = true"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Cancel Order</span>
                    </button>
                @endif
                @if($order->canRequestReturn())
                    <button type="button" @click="returnModalOpen = true"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-800 hover:bg-amber-100 transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4"/></svg>
                        <span>Request Return / Refund</span>
                    </button>
                @elseif($order->isCompleted() && empty($order->return_status) && !$order->isWithinReturnWindow())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700" title="Return window expired ({{ $order->return_limit_days }} days limit)">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Return window closed ({{ $order->return_limit_days }}d limit)</span>
                    </span>
                @endif
            </div>
        </div>

        {{-- Order Cancellation Notice Alert (If Cancelled) --}}
        @if($order->status === 'cancelled')
            <div class="mb-6 p-5 rounded-2xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/60 shadow-xs">
                <div class="flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-red-900 dark:text-red-200">
                                This Order Has Been Cancelled
                            </h3>
                            @if($order->cancelled_at)
                                <span class="text-xs text-red-600 dark:text-red-400 font-mono">{{ $order->cancelled_at->format('M j, Y • g:i A') }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-red-700 dark:text-red-400 mt-1">
                            Cancelled by: <strong class="font-semibold">{{ $order->cancelled_by === 'user' ? 'You (Customer)' : 'Store Administration' }}</strong>
                        </p>
                        @if($order->cancellation_reason)
                            <div class="mt-3 p-3.5 rounded-xl bg-white dark:bg-gray-800 border border-red-100 dark:border-red-900/40 text-xs text-red-800 dark:text-red-300 leading-relaxed">
                                <strong class="block font-bold text-red-900 dark:text-red-200 mb-0.5">
                                    {{ $order->cancelled_by === 'user' ? 'Reason for Cancellation:' : 'Notice from Store:' }}
                                </strong>
                                {{ $order->cancellation_reason }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Return / Refund Status Alert Banners --}}
        @if($order->return_status === 'requested')
            <div class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 flex items-start gap-3 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <h3 class="text-sm font-bold text-amber-900 dark:text-amber-200">
                            {{ ucfirst($order->return_request_type ?? 'Return/Refund') }} Request Under Review
                        </h3>
                        @if($order->return_requested_at)
                            <span class="text-xs text-amber-700 dark:text-amber-400 font-mono">{{ $order->return_requested_at->format('M j, Y • g:i A') }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-amber-800 dark:text-amber-300 mt-1 leading-relaxed">
                        <strong class="font-semibold">Reason provided:</strong> {{ $order->return_reason }}
                    </p>
                    <p class="text-[11px] text-amber-700/80 dark:text-amber-400/80 mt-1">Our support team is reviewing your request and will update the status soon.</p>
                </div>
            </div>
        @elseif($order->status === 'refunded')
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 flex items-start gap-3 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-200">Order Refunded</h3>
                    <p class="text-xs text-emerald-800 dark:text-emerald-300 mt-0.5">
                        This order has been refunded. Refund amount: <strong class="font-bold">{{ $currency }}{{ number_format($order->refund_amount > 0 ? $order->refund_amount : $order->total, 2) }}</strong>.
                    </p>
                    @if($order->return_admin_note)
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-1 italic bg-white/60 dark:bg-gray-800/60 p-2 rounded-lg border border-emerald-100 dark:border-emerald-900/40">
                            "{{ $order->return_admin_note }}"
                        </p>
                    @endif
                </div>
            </div>
        @elseif($order->status === 'returned')
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60 flex items-start gap-3 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-rose-900 dark:text-rose-200">Order Returned</h3>
                    <p class="text-xs text-rose-800 dark:text-rose-300 mt-0.5">The return for this order has been accepted and processed.</p>
                    @if($order->return_admin_note)
                        <p class="text-xs text-rose-700 dark:text-rose-400 mt-1 italic bg-white/60 dark:bg-gray-800/60 p-2 rounded-lg border border-rose-100 dark:border-rose-900/40">
                            "{{ $order->return_admin_note }}"
                        </p>
                    @endif
                </div>
            </div>
        @elseif($order->return_status === 'rejected')
            <div class="mb-6 p-4 rounded-2xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/60 flex items-start gap-3 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-red-900 dark:text-red-200">Return / Refund Request Declined</h3>
                    <p class="text-xs text-red-800 dark:text-red-300 mt-0.5">Your request for {{ $order->return_request_type ?? 'return/refund' }} could not be accepted.</p>
                    @if($order->return_admin_note)
                        <p class="text-xs text-red-700 dark:text-red-400 mt-1 italic bg-white/60 dark:bg-gray-800/60 p-2 rounded-lg border border-red-100 dark:border-red-900/40">
                            Reason: "{{ $order->return_admin_note }}"
                        </p>
                    @endif
                </div>
            </div>
        @endif

        <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-6">Order {{ $order->order_number }}</h1>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-5">
                {{-- Items --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Order Items</h2>
                    <div class="divide-y divide-cream-100 dark:divide-gray-700">
                        @foreach($order->items as $item)
                        @php
                            $colorCode = $item->options_data['color_code'] ?? $item->product?->colors?->firstWhere('name', $item->selected_color)?->code ?? null;
                            $shadeCode = $item->options_data['shade_code'] ?? $item->product?->shades?->firstWhere('name', $item->selected_shade)?->code ?? null;
                        @endphp
                        <div class="py-3.5 flex items-start gap-4">
                            @if($item->product)
                                <a href="{{ route('products.show', $item->product->slug) }}" class="shrink-0">
                                    <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product_name }}" class="w-16 h-16 rounded-xl object-cover border border-cream-200 dark:border-gray-700 bg-cream-50">
                                </a>
                            @else
                                <div class="w-16 h-16 rounded-xl bg-cream-100 dark:bg-gray-700 flex items-center justify-center shrink-0 text-gray-400 text-xs">No image</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">
                                    @if($item->product)
                                        <a href="{{ route('products.show', $item->product->slug) }}" class="hover:text-brand-700 dark:hover:text-brand-400 transition">
                                            {{ $item->product_name }}
                                        </a>
                                    @else
                                        {{ $item->product_name }}
                                    @endif
                                </p>

                                {{-- Selected Variants (Size / Color / Shade) --}}
                                @if(!empty($item->selected_size) || !empty($item->selected_color) || !empty($item->selected_shade) || !empty($item->options_summary))
                                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                        @if(!empty($item->selected_size))
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900/60 px-2.5 py-0.5 rounded-lg shadow-2xs">
                                                <span class="text-blue-400 dark:text-blue-400 font-normal text-[10px] uppercase tracking-wider">Size:</span>
                                                <span>{{ $item->selected_size }}</span>
                                            </span>
                                        @endif

                                        @if(!empty($item->selected_color))
                                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 px-2.5 py-0.5 rounded-lg shadow-2xs">
                                                @if($colorCode)
                                                    <span class="w-3 h-3 rounded-full border border-black/15 shadow-xs shrink-0" style="background-color: {{ $colorCode }};"></span>
                                                @endif
                                                <span class="text-rose-400 dark:text-rose-400 font-normal text-[10px] uppercase tracking-wider">Color:</span>
                                                <span>{{ $item->selected_color }}</span>
                                            </span>
                                        @endif

                                        @if(!empty($item->selected_shade))
                                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60 px-2.5 py-0.5 rounded-lg shadow-2xs">
                                                @if($shadeCode)
                                                    <span class="w-3 h-3 rounded-full border border-black/15 shadow-xs shrink-0" style="background-color: {{ $shadeCode }};"></span>
                                                @endif
                                                <span class="text-amber-600 dark:text-amber-400 font-normal text-[10px] uppercase tracking-wider">Shade:</span>
                                                <span>{{ $item->selected_shade }}</span>
                                            </span>
                                        @endif

                                        @if(empty($item->selected_size) && empty($item->selected_color) && empty($item->selected_shade) && !empty($item->options_summary))
                                            <span class="inline-flex items-center text-xs font-semibold bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-900/60 px-2.5 py-0.5 rounded-lg">
                                                {{ $item->options_summary }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 flex items-center gap-2 flex-wrap">
                                    @if(!empty($item->product_sku))
                                        <span class="font-mono bg-stone-100 dark:bg-gray-700/60 px-1.5 py-0.5 rounded text-[11px] text-gray-600 dark:text-gray-300">SKU: {{ $item->product_sku }}</span>
                                    @endif
                                    <span>Qty: <strong class="text-gray-800 dark:text-gray-200">{{ $item->quantity }}</strong></span>
                                    <span>&bull;</span>
                                    <span>Unit Price: <strong class="text-gray-800 dark:text-gray-200">{{ $currency }}{{ number_format($item->unit_price ?? ($item->total / max(1, $item->quantity)), 2) }}</strong></span>
                                </p>

                                {{-- Review Action from Order Page (Req 1) --}}
                                @if($order->status === 'completed' && $item->product)
                                    @php
                                        $existingReview = ($userReviews ?? collect())->get($item->product_id);
                                    @endphp
                                    <div class="mt-3 pt-2.5 border-t border-cream-100 dark:border-gray-700/60 flex items-center justify-between gap-3 flex-wrap">
                                        @if($existingReview)
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-1 rounded-lg border border-amber-200 dark:border-amber-900/50">
                                                    ★ {{ $existingReview->rating }}/5 Reviewed
                                                </span>
                                                @if($existingReview->body)
                                                    <span class="text-xs text-gray-500 dark:text-gray-400 italic line-clamp-1">"{{ Str::limit($existingReview->body, 40) }}"</span>
                                                @endif
                                                @if($allowReviewUpdates ?? true)
                                                    <button type="button"
                                                        @click="openReviewModal({{ $item->product_id }}, '{{ addslashes($item->product_name) }}', '{{ $item->product->thumbnail_url }}', {{ $existingReview->rating }}, '{{ addslashes($existingReview->body ?? '') }}', true)"
                                                        class="inline-flex items-center gap-1 text-xs font-bold text-brand-700 dark:text-brand-400 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 dark:bg-brand-950/40 dark:hover:bg-brand-900/50 px-2.5 py-1 rounded-lg border border-brand-200 dark:border-brand-800 transition">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                        <span>Edit Review</span>
                                                    </button>
                                                @endif
                                            </div>
                                            <a href="{{ route('products.show', $item->product->slug) }}#reviews" class="text-xs font-semibold text-brand-700 dark:text-brand-400 hover:underline">
                                                View on Product Page →
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Order completed! How did you like this item?</span>
                                            <button type="button"
                                                @click="openReviewModal({{ $item->product_id }}, '{{ addslashes($item->product_name) }}', '{{ $item->product->thumbnail_url }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-brand-700 hover:bg-brand-800 text-white shadow-xs transition">
                                                <span class="text-amber-300">★</span>
                                                <span>Write Review</span>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-bold text-sm text-gray-900 dark:text-white">{{ $currency }}{{ number_format($item->total) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="border-t border-cream-200 dark:border-gray-700 pt-3 mt-3 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between"><span>Subtotal</span><span>{{ $currency }}{{ number_format($order->subtotal) }}</span></div>
                        @if($order->discount > 0)
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-medium">
                            <span>Discount @if($order->coupon_code)<span class="font-mono text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">({{ $order->coupon_code }})</span>@endif</span>
                            <span>-{{ $currency }}{{ number_format($order->discount) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span>Shipping @if($order->shipping_title)<span class="text-xs text-gray-400">({{ $order->shipping_title }})</span>@endif</span>
                            <span>{{ $currency }}{{ number_format($order->shipping_cost) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-gray-900 dark:text-white text-base pt-1 border-t border-cream-200 dark:border-gray-700"><span>Total</span><span>{{ $currency }}{{ number_format($order->total) }}</span></div>
                    </div>
                </div>
                {{-- Status History Timeline --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-cream-200 dark:border-gray-700">
                    <div class="flex items-center justify-between pb-3 mb-6 border-b border-cream-100 dark:border-gray-700">
                        <h2 class="font-semibold text-gray-900 dark:text-white text-base flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            <span>Delivery Progress & Status</span>
                        </h2>
                        <span class="text-xs text-gray-400">Chronological</span>
                    </div>

                    <div class="relative pl-1">
                        @forelse($order->statusHistories as $h)
                        @php $isLatest = $loop->last; @endphp
                        <div class="relative flex items-start gap-4 pb-8 last:pb-1">
                            {{-- Connecting vertical line --}}
                            @if(!$loop->last)
                                <div class="absolute left-3.5 top-7 -bottom-1 w-0.5 bg-linear-to-b from-brand-600 via-brand-400 to-stone-200 dark:from-brand-500 dark:via-brand-700 dark:to-gray-700"></div>
                            @endif

                            {{-- Dot / Node --}}
                            <div class="relative z-10 w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-all
                                {{ $isLatest ? 'bg-brand-700 text-white ring-4 ring-brand-700/20 shadow-sm' : 'bg-emerald-600 text-white shadow-xs' }}">
                                @if($isLatest)
                                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                                @else
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0 pt-0.5">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="font-bold text-sm text-gray-900 dark:text-white capitalize">
                                        {{ $h->status_label }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-mono">
                                        {{ $h->created_at->format('M j, Y • g:i A') }}
                                    </span>
                                </div>
                                @if($h->note)
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1.5 bg-stone-50 dark:bg-gray-900/40 p-2.5 rounded-xl border border-stone-100 dark:border-gray-800 leading-relaxed">
                                        {{ $h->note }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        @empty
                            <p class="text-xs text-gray-400">Order placed.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="space-y-5">
                {{-- Status --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Current Status</p>
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <span class="px-3 py-1 rounded-full text-sm font-bold bg-{{ $order->status_color }}-100 dark:bg-{{ $order->status_color }}-900/30 text-{{ $order->status_color }}-700">{{ $order->status_label }}</span>
                        @if($order->canBeCancelledByUser())
                            <button type="button" @click="cancelModalOpen = true" class="text-xs font-semibold text-red-600 dark:text-red-400 hover:underline">
                                Cancel Order
                            </button>
                        @endif
                    </div>
                    @if($order->status === 'processing')
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-2">
                            This order is currently being processed and can no longer be cancelled online.
                        </p>
                    @endif
                </div>
                {{-- Payment --}}
                @if($order->payment)
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Payment</p>
                    <p class="text-sm text-gray-800 dark:text-gray-200 font-semibold uppercase">{{ $order->payment->method }}</p>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $order->payment->status==='verified'?'bg-green-100 text-green-700':'bg-amber-100 text-amber-700' }}">{{ ucfirst($order->payment->status) }}</span>
                </div>
                @endif
                {{-- Shipping --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-cream-100 dark:border-gray-700">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Ship To</p>
                        @if($order->has_coordinates)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Map Location Pinned
                            </span>
                        @endif
                    </div>
                    
                    <div class="text-sm space-y-0.5">
                        <p class="font-bold text-gray-900 dark:text-white">{{ $order->shipping_name }}</p>
                        <p class="text-gray-600 dark:text-gray-300 text-xs leading-relaxed">{{ $order->shipping_address }}, {{ $order->shipping_city }}</p>
                        <p class="text-gray-500 text-xs font-mono pt-1">{{ $order->shipping_phone }}</p>
                    </div>

                    {{-- Map Location --}}
                    @if($order->has_coordinates)
                        <div class="pt-2">
                            <div id="user-order-map" class="w-full h-36 rounded-xl border border-stone-200 dark:border-gray-700 overflow-hidden shadow-xs relative z-0"></div>
                            <a href="{{ $order->google_maps_url }}" target="_blank"
                               class="mt-2.5 inline-flex items-center justify-center gap-1.5 w-full px-3 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs shadow-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>Open in Google Maps ↗</span>
                            </a>
                        </div>
                    @else
                        <a href="{{ $order->google_maps_url }}" target="_blank"
                           class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold text-xs transition">
                            <svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            <span>Search Address on Google Maps ↗</span>
                        </a>
                    @endif
                </div>

                {{-- Delivery Notes --}}
                @if(!empty($order->notes) || !empty($order->order_notes))
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        <span>Delivery Instructions / Note</span>
                    </p>
                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed italic bg-amber-50/70 dark:bg-amber-950/30 p-3 rounded-xl border border-amber-200/70 dark:border-amber-800/50">
                        "{{ $order->notes ?? $order->order_notes }}"
                    </p>
                </div>
                @endif
            </div>
        </div>
    </main>

    {{-- Review Modal (Req 1) --}}
    <div x-show="reviewModalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="reviewModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="reviewModalOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="reviewModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-cream-200 dark:border-gray-700 relative z-10">
                <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <input type="hidden" name="product_id" :value="reviewProductId">
                    <input type="hidden" name="rating" :value="reviewRating">

                    <div class="p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-cream-100 dark:border-gray-700">
                            <h3 class="font-serif text-lg font-bold text-gray-900 dark:text-white" id="modal-title" x-text="isEditReview ? 'Update Your Product Review' : 'Write a Product Review'">Write a Product Review</h3>
                            <button type="button" @click="reviewModalOpen = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Product summary --}}
                        <div class="flex items-center gap-3 my-4 p-3 rounded-xl bg-cream-50 dark:bg-gray-900/60 border border-cream-200 dark:border-gray-700">
                            <img :src="reviewProductImage" alt="Product" class="w-12 h-12 rounded-lg object-cover bg-white">
                            <div>
                                <p class="text-xs text-gray-400" x-text="isEditReview ? 'Editing Review for:' : 'Reviewing:'">Reviewing:</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1" x-text="reviewProductName"></p>
                            </div>
                        </div>

                        {{-- Interactive Star Rating --}}
                        <div class="mb-5 text-center">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Overall Rating *</label>
                            <div class="flex items-center justify-center gap-2">
                                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                    <button type="button" @click="reviewRating = star"
                                            class="text-3xl focus:outline-none transition transform hover:scale-125"
                                            :class="star <= reviewRating ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'">
                                        ★
                                    </button>
                                </template>
                            </div>
                            <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold mt-1"
                               x-text="['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'][reviewRating - 1]"></p>
                        </div>

                        {{-- Review Body --}}
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Your Review</label>
                            <textarea name="body" x-model="reviewBody" rows="4" placeholder="How was the product texture, fragrance, packaging, or performance?"
                                      class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-600"></textarea>
                        </div>

                        {{-- Photos --}}
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Add Photos (optional)</label>
                            <input type="file" name="images[]" multiple accept="image/*"
                                   class="text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/40 dark:file:text-brand-300">
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900/60 px-6 py-4 flex items-center justify-end gap-3 border-t border-cream-100 dark:border-gray-700">
                        <button type="button" @click="reviewModalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl text-xs font-bold bg-brand-700 hover:bg-brand-800 text-white shadow-md transition">
                            <span x-text="isEditReview ? 'Update Review' : 'Submit Review'">Submit Review</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Return / Refund Modal (Req 3) --}}
    <div x-show="returnModalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="return-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="returnModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="returnModalOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="returnModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-cream-200 dark:border-gray-700 relative z-10">
                <form action="{{ route('user.orders.return-request', $order) }}" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-cream-100 dark:border-gray-700">
                            <div>
                                <h3 class="font-serif text-lg font-bold text-gray-900 dark:text-white" id="return-modal-title">Request Order Return or Refund</h3>
                                <p class="text-xs text-gray-500">Order #{{ $order->order_number }} · Total: {{ $currency }}{{ number_format($order->total, 2) }}</p>
                            </div>
                            <button type="button" @click="returnModalOpen = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="mt-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                            <span class="font-bold">Policy Notice:</span> Returns and refunds are allowed within <strong>{{ $order->return_limit_days }} days</strong> of order completion (completed on {{ $order->completed_at?->format('M j, Y') }}).
                        </div>

                        {{-- Choice: Return vs Refund --}}
                        <div class="my-4">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Request Type *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                                       :class="returnType === 'refund' ? 'border-brand-600 bg-brand-50/50 dark:bg-brand-950/30' : 'border-gray-200 dark:border-gray-700'">
                                    <input type="radio" name="request_type" value="refund" x-model="returnType" class="text-brand-700 focus:ring-brand-600">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900 dark:text-white">Refund Payment</p>
                                        <p class="text-[11px] text-gray-500">Ask for money refund</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                                       :class="returnType === 'return' ? 'border-brand-600 bg-brand-50/50 dark:bg-brand-950/30' : 'border-gray-200 dark:border-gray-700'">
                                    <input type="radio" name="request_type" value="return" x-model="returnType" class="text-brand-700 focus:ring-brand-600">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900 dark:text-white">Product Return</p>
                                        <p class="text-[11px] text-gray-500">Return physical products</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Reason Category --}}
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Reason Category</label>
                            <select name="reason_category"
                                    class="w-full px-4 py-2.5 rounded-xl border border-cream-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-600">
                                <option value="Damaged or Defective Item">Damaged or Defective Item</option>
                                <option value="Wrong Item or Shade Received">Wrong Item or Shade Received</option>
                                <option value="Item Does Not Match Description">Item Does Not Match Description</option>
                                <option value="Quality Not as Expected">Quality Not as Expected</option>
                                <option value="Missing Parts or Package Broken">Missing Parts or Package Broken</option>
                                <option value="Changed Mind / Other">Changed Mind / Other</option>
                            </select>
                        </div>

                        {{-- Why ask for return or refund (Required) --}}
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Detailed Reason <span class="text-red-500">*</span>
                            </label>
                            <textarea name="reason" rows="4" required minlength="5" maxlength="2000"
                                      placeholder="Please explain in detail why you want to return or refund this order (e.g. issues observed, package condition)..."
                                      class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-600"></textarea>
                            <p class="text-[11px] text-gray-400 mt-1">Please provide clear details to help our team process your request quickly.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900/60 px-6 py-4 flex items-center justify-end gap-3 border-t border-cream-100 dark:border-gray-700">
                        <button type="button" @click="returnModalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-md transition">
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- User Cancel Order Modal --}}
    @if($order->canBeCancelledByUser())
    <div x-show="cancelModalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="user-cancel-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="cancelModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="cancelModalOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="cancelModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-cream-200 dark:border-gray-700">
                <form action="{{ route('user.orders.cancel', $order) }}" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-cream-100 dark:border-gray-700">
                            <div>
                                <h3 class="font-serif text-lg font-bold text-gray-900 dark:text-white" id="user-cancel-modal-title">Cancel Order #{{ $order->order_number }}</h3>
                                <p class="text-xs text-gray-500">Order status is currently {{ ucfirst($order->status) }}.</p>
                            </div>
                            <button type="button" @click="cancelModalOpen = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="my-4 space-y-4">
                            <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                                You can cancel your order while it is <strong>Pending</strong> or <strong>Confirmed</strong>. Once an order moves to <strong>Processing</strong>, it cannot be cancelled online.
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Reason for Cancellation <span class="text-gray-400 font-normal">(optional)</span>
                                </label>
                                <select name="cancellation_preset"
                                        @change="if($event.target.value) $refs.reasonField.value = $event.target.value"
                                        class="w-full mb-2 px-3.5 py-2 rounded-xl border border-cream-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-600">
                                    <option value="">-- Select a reason (or write below) --</option>
                                    <option value="Changed my mind">Changed my mind</option>
                                    <option value="Ordered by mistake / wrong items selected">Ordered by mistake / wrong items selected</option>
                                    <option value="Found a better price elsewhere">Found a better price elsewhere</option>
                                    <option value="Delivery time is too long">Delivery time is too long</option>
                                    <option value="Need to change shipping address or phone">Need to change shipping address or phone</option>
                                </select>
                                <textarea name="cancellation_reason" x-ref="reasonField" rows="3" maxlength="1000"
                                          placeholder="Tell us why you wish to cancel this order..."
                                          class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-600"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900/60 px-6 py-4 flex items-center justify-end gap-3 border-t border-cream-100 dark:border-gray-700">
                        <button type="button" @click="cancelModalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            Keep Order
                        </button>
                        <button type="submit"
                                onclick="return confirm('Are you sure you want to cancel this order? This cannot be undone.');"
                                class="px-5 py-2 rounded-xl text-xs font-bold bg-red-600 hover:bg-red-700 text-white shadow-md transition">
                            Yes, Cancel Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@if($order->has_coordinates)
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mapEl = document.getElementById('user-order-map');
                if (!mapEl) return;

                const lat = {{ (float) $order->effective_latitude }};
                const lng = {{ (float) $order->effective_longitude }};

                const map = L.map('user-order-map', {
                    scrollWheelZoom: false
                }).setView([lat, lng], 15);

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
                marker.bindPopup('<strong>Delivery Location</strong><br>{{ addslashes($order->shipping_address) }}');

                setTimeout(() => map.invalidateSize(), 300);
            });
        </script>
    @endpush
@endif
@endsection

