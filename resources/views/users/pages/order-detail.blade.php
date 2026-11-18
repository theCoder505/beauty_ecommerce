@extends('users.layouts.app')
@php $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); @endphp
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8">
    @include('users.partials.dashboard-sidebar')
    <main class="flex-1 min-w-0">
        <div class="flex items-center gap-3 mb-6"><a href="{{ route('user.orders') }}" class="text-brand-700 hover:underline text-sm">← My Orders</a></div>
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
                                <div class="absolute left-3.5 top-7 -bottom-1 w-0.5 bg-gradient-to-b from-brand-600 via-brand-400 to-stone-200 dark:from-brand-500 dark:via-brand-700 dark:to-gray-700"></div>
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
                                        {{ str_replace('-',' ',$h->status) }}
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
                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-{{ $order->status_color }}-100 dark:bg-{{ $order->status_color }}-900/30 text-{{ $order->status_color }}-700">{{ $order->status_label }}</span>
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

