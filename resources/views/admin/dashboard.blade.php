@extends('admin.layouts.app')
@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css" />
<style>
    .jvm-tooltip {
        background-color: #111827 !important;
        color: #f9fafb !important;
        font-family: inherit !important;
        font-size: 0.75rem !important;
        border-radius: 0.5rem !important;
        padding: 0.35rem 0.65rem !important;
        border: 1px solid #374151 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2) !important;
    }
    .dark .jvm-container {
        background-color: transparent !important;
    }
    .jvm-zoom-btn {
        display: none !important;
    }
</style>
@endpush

@section('content')

@php
    $currency        = app(\App\Services\SettingService::class)->get('currency_symbol', '৳');
    $lowStockItems   = $lowStockProducts ?? $lowStock ?? collect();
    $topSellingItems = $topSellingProducts ?? $topSelling ?? collect();
    $mostViewedItems = $mostViewedProducts ?? $mostViewed ?? collect();
@endphp

{{-- Top KPI Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
    {{-- Total Revenue --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 shadow-xs hover:border-gray-300 dark:hover:border-gray-700 transition">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Revenue</p>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="font-bold text-2xl text-gray-900 dark:text-white">{{ $currency }}{{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $currency }}{{ number_format($stats['today_revenue'] ?? 0, 2) }} today</p>
    </div>

    {{-- Total Orders --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 shadow-xs hover:border-gray-300 dark:hover:border-gray-700 transition">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Orders</p>
            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>
        <p class="font-bold text-2xl text-gray-900 dark:text-white">{{ number_format($stats['total_orders'] ?? 0) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['pending_orders'] ?? 0 }} pending · {{ $stats['today_orders'] ?? 0 }} today</p>
    </div>

    {{-- Customers --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 shadow-xs hover:border-gray-300 dark:hover:border-gray-700 transition">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Customers</p>
            <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
        <p class="font-bold text-2xl text-gray-900 dark:text-white">{{ number_format($stats['total_customers'] ?? 0) }}</p>
        <p class="text-xs text-gray-400 mt-1">Registered users</p>
    </div>

    {{-- Site Visits --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 shadow-xs hover:border-gray-300 dark:hover:border-gray-700 transition">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Site Visits</p>
            <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
        </div>
        <p class="font-bold text-2xl text-gray-900 dark:text-white">{{ number_format($stats['today_visits'] ?? 0) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ number_format($stats['month_visits'] ?? 0) }} this month</p>
    </div>

    {{-- LOW STOCK ALERT CARD (CLICKABLE TO OPEN MODAL & MANAGE) --}}
    <div id="top-low-stock-card" onclick="openLowStockModal()" role="button" tabindex="0" title="Click to view and manage low stock inventory"
        class="bg-white dark:bg-gray-900 rounded-2xl p-5 border-2 {{ ($stats['low_stock'] ?? 0) > 0 ? 'border-red-400/80 dark:border-red-600/80 bg-red-50/20 dark:bg-red-950/10' : 'border-gray-200 dark:border-gray-800' }} shadow-sm hover:shadow-md hover:scale-[1.02] cursor-pointer transition transform duration-200 group relative overflow-hidden">
        <span id="top-low-stock-ping" class="absolute top-3 right-3 flex h-3 w-3 {{ ($stats['low_stock'] ?? 0) > 0 ? '' : 'hidden' }}">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
        </span>
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wide flex items-center gap-1.5">
                Low Stock Alert
            </p>
            <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300 flex items-center justify-center group-hover:bg-red-600 group-hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
        <p id="top-low-stock-count" class="font-bold text-2xl text-red-600 dark:text-red-400">{{ number_format($stats['low_stock'] ?? 0) }}</p>
        <div class="flex items-center justify-between mt-1">
            <p class="text-xs text-red-500/80 font-medium">≤10 units remaining</p>
            <span class="text-xs font-semibold text-red-600 dark:text-red-400 group-hover:translate-x-0.5 transition">Manage →</span>
        </div>
    </div>
</div>

{{-- SECTION 1: ORDERS ANALYTICS (BAR & PIE CHARTS WITH TODAY / MONTH / YEAR / ALL TIME) --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-6 mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>Orders & Revenue Analytics</span>
                <span id="current-period-badge" class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-teal-900">
                    {{ $orderCharts['month']['period_name'] }}
                </span>
            </h2>
            <p class="text-xs text-gray-500 mt-1">Real-time order volume, gross revenue trends, and fulfillment status distribution.</p>
        </div>

        {{-- Period Switcher Buttons --}}
        <div class="inline-flex items-center p-1 bg-gray-100 dark:bg-gray-800/80 rounded-xl border border-gray-200/80 dark:border-gray-700 text-xs font-medium">
            <button type="button" onclick="switchOrderPeriod('today')" id="btn-period-today"
                class="period-tab-btn px-3 py-1.5 rounded-lg transition hover:text-gray-900 dark:hover:text-white text-gray-500 dark:text-gray-400">
                Today
            </button>
            <button type="button" onclick="switchOrderPeriod('month')" id="btn-period-month"
                class="period-tab-btn px-3 py-1.5 rounded-lg transition text-gray-900 dark:text-white bg-white dark:bg-gray-700 shadow-xs font-semibold">
                Current Month
            </button>
            <button type="button" onclick="switchOrderPeriod('year')" id="btn-period-year"
                class="period-tab-btn px-3 py-1.5 rounded-lg transition hover:text-gray-900 dark:hover:text-white text-gray-500 dark:text-gray-400">
                Current Year
            </button>
            <button type="button" onclick="switchOrderPeriod('all_time')" id="btn-period-all_time"
                class="period-tab-btn px-3 py-1.5 rounded-lg transition hover:text-gray-900 dark:hover:text-white text-gray-500 dark:text-gray-400">
                All Time
            </button>
        </div>
    </div>

    {{-- Dynamic Period KPI Stats Strip --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 my-5 p-4 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Orders in Period</p>
                <p id="kpi-period-orders" class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($orderCharts['month']['summary']['total_orders']) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Gross Revenue</p>
                <p id="kpi-period-revenue" class="text-lg font-bold text-gray-900 dark:text-white">{{ $currency }}{{ number_format($orderCharts['month']['summary']['total_revenue'], 2) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Average Order Value</p>
                <p id="kpi-period-avg" class="text-lg font-bold text-gray-900 dark:text-white">{{ $currency }}{{ number_format($orderCharts['month']['summary']['avg_value'], 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Dual Charts Grid: Bar Chart & Pie Chart --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Bar Chart: Order Volume & Revenue Trend --}}
        <div class="lg:col-span-2 bg-gray-50/50 dark:bg-gray-800/30 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Order Volume & Trends (Bar Chart)</h3>
                <span class="text-[11px] text-gray-400">Total orders & revenue plotted over time</span>
            </div>
            <div class="relative h-72">
                <canvas id="ordersBarChart"></canvas>
            </div>
        </div>

        {{-- Pie / Doughnut Chart: Order Status Breakdown --}}
        <div class="bg-gray-50/50 dark:bg-gray-800/30 rounded-xl p-4 border border-gray-100 dark:border-gray-800 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status Distribution (Pie Chart)</h3>
                <span class="text-[11px] text-gray-400">By fulfillment state</span>
            </div>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="ordersPieChart"></canvas>
            </div>
            <div id="pie-legend-summary" class="mt-2 text-center text-xs text-gray-500"></div>
        </div>
    </div>
</div>

{{-- SECTION 2: GEOGRAPHIC VISITOR TRAFFIC (WORLD MAP - FULL WIDTH & MINIMIZE CONTROLS) --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-6 mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>Visitor Geographic Distribution (World Map)</span>
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900">
                    {{ $countryStats['total_countries'] }} Countries Recorded
                </span>
            </h2>
            <p class="text-xs text-gray-500 mt-1">Live customer traffic and visitor locations automatically mapped from client IP addresses.</p>
        </div>
        <div class="text-xs text-gray-500">
            Top origin: <strong class="text-gray-900 dark:text-white">{{ $countryStats['top_country'] }}</strong> ({{ $countryStats['top_count'] }} visits)
        </div>
    </div>

    {{-- Vector World Map Container with Interactive Controls (Full Width & Enlarged Height) --}}
    <div class="bg-gray-50/50 dark:bg-gray-800/20 rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-800 relative mt-6">
        <div class="flex flex-wrap items-center justify-between text-xs text-gray-400 mb-3 gap-2">
            <span class="flex items-center gap-2">
                <span class="font-medium text-gray-700 dark:text-gray-300">Expansive World Map</span>
                <span class="hidden sm:inline text-gray-400">· Full-width Mercator projection · Hover country for live visitor metrics</span>
            </span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="mapZoomIn()" class="px-3 py-1.5 text-xs rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition shadow-2xs" title="Zoom In">
                    Zoom In
                </button>
                <button type="button" onclick="mapZoomOut()" class="px-3 py-1.5 text-xs rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition shadow-2xs" title="Zoom Out">
                    Zoom Out
                </button>
                <button type="button" onclick="mapReset()" class="px-3 py-1.5 text-xs rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition shadow-2xs" title="Fit Map to View">
                    Fit
                </button>
            </div>
        </div>
        <div id="world-map-container" class="h-[750px] w-full rounded-xl overflow-hidden transition-all duration-300" style="min-height: 750px;"></div>
    </div>

    {{-- Countries Leaderboard Grid (Full Width Responsive Cards) --}}
    <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-800">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-2">
                <span>Top Visitor Countries & Regional Traffic</span>
                <span class="text-gray-400 text-[11px] font-normal">({{ $countryStats['total_visitors'] }} total logged visits)</span>
            </h3>
        </div>
        <div id="countries-leaderboard-list" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            @forelse($countryStats['country_list'] as $c)
            <div class="p-3 rounded-xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/80 hover:border-teal-300 dark:hover:border-teal-700 transition space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold">{{ $c['country_code'] }}</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $c['count'] }} <span class="text-gray-400 text-[11px] font-normal">({{ $c['percentage'] }}%)</span></span>
                </div>
                <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate" title="{{ $c['country'] }}">
                    {{ $c['country'] }}
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700/60 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-teal-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $c['percentage'] }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-400 text-center py-4 col-span-full">No visitor country records yet.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- SECTION 3: VISITOR DEVICES & PLATFORMS STATS --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Device Breakdown with Chart --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-xs font-bold text-gray-800 dark:text-white uppercase tracking-wider">Device Types</h3>
            <span class="text-[11px] text-gray-400">Desktop vs Mobile vs Tablet</span>
        </div>
        <div class="h-44 relative flex items-center justify-center mb-4">
            <canvas id="deviceDoughnutChart"></canvas>
        </div>
        <div class="space-y-2.5">
            @foreach($deviceStats['devices'] as $dev)
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600 dark:text-gray-400 flex items-center gap-2">
                    @if($dev['name'] === 'Desktop')
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    @elseif($dev['name'] === 'Mobile')
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @else
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @endif
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $dev['name'] }}</span>
                </span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $dev['count'] }} ({{ $dev['percentage'] }}%)</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Browsers Breakdown --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-xs font-bold text-gray-800 dark:text-white uppercase tracking-wider">Top Browsers</h3>
            <span class="text-[11px] text-gray-400">Logged client agents</span>
        </div>
        <div class="space-y-3.5 mt-2">
            @forelse($deviceStats['browsers'] as $b)
            <div>
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $b['name'] }}</span>
                    <span class="text-gray-900 dark:text-white font-semibold">{{ $b['count'] }} <span class="text-gray-400 font-normal">({{ $b['percentage'] }}%)</span></span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $b['percentage'] }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-400 py-6 text-center">No browser data logged yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Operating Systems / Platforms --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-xs font-bold text-gray-800 dark:text-white uppercase tracking-wider">Operating Systems</h3>
            <span class="text-[11px] text-gray-400">Visitor platforms</span>
        </div>
        <div class="space-y-3.5 mt-2">
            @forelse($deviceStats['platforms'] as $p)
            <div>
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $p['name'] }}</span>
                    <span class="text-gray-900 dark:text-white font-semibold">{{ $p['count'] }} <span class="text-gray-400 font-normal">({{ $p['percentage'] }}%)</span></span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ $p['percentage'] }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-400 py-6 text-center">No platform data logged yet.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- SECTION 4: VISITOR ACTIVITY LOGS TABLE (JQUERY DATATABLE - 10 PER PAGE FRONTEND PAGINATION & AJAX DELETION) --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h2 class="font-bold text-gray-900 dark:text-white text-sm flex items-center gap-2">
                <span>Recent Visitor Activity Logs</span>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-900">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> 10 Per Page · Frontend Paginated
                </span>
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">Instant client-side search, sorting, and seamless AJAX deletion without page reloads.</p>
        </div>

        {{-- Bulk Delete Action for Visitor Logs (AJAX) --}}
        <div>
            <button type="button" id="bulk-delete-visitor-logs-btn" disabled onclick="bulkDeleteVisitorLogsAjax(this)"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-red-50 text-red-700 hover:bg-red-600 hover:text-white dark:bg-red-950/40 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white border border-red-200 dark:border-red-900 transition shadow-xs disabled:opacity-40 disabled:cursor-not-allowed">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Bulk Delete (<span id="selected-visitor-logs-count">0</span>)</span>
            </button>
        </div>
    </div>

    <div class="p-4 overflow-x-auto">
        <table id="visitor-logs-datatable" class="w-full text-sm text-left">
            <thead>
                <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-gray-50/60 dark:bg-gray-800/40 border-b border-gray-100 dark:border-gray-800">
                    <th class="w-10 px-4 py-3.5 text-center">
                        <input type="checkbox" id="select-all-visitor-logs" class="rounded border-gray-300 dark:border-gray-700 text-teal-600 focus:ring-teal-500 cursor-pointer" title="Select All">
                    </th>
                    <th class="px-5 py-3.5">IP Address</th>
                    <th class="px-5 py-3.5">Location</th>
                    <th class="px-5 py-3.5">Device & OS</th>
                    <th class="px-5 py-3.5">Browser</th>
                    <th class="px-5 py-3.5">Visited URL</th>
                    <th class="px-5 py-3.5">Time</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($visitorLogs as $log)
                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                    <td class="w-10 px-4 py-3 text-center">
                        <input type="checkbox" value="{{ $log->id }}" class="visitor-log-row-checkbox rounded border-gray-300 dark:border-gray-700 text-teal-600 focus:ring-teal-500 cursor-pointer">
                    </td>
                    <td class="px-5 py-3 font-mono text-xs text-gray-900 dark:text-white">
                        {{ $log->ip_address }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-800 dark:text-gray-200">
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 font-mono text-[10px] font-bold">{{ $log->country_code ?? 'XX' }}</span>
                            <span>{{ $log->country ?? 'Unknown' }}</span>
                        </span>
                        @if($log->city)
                        <span class="text-gray-400 block text-[11px]">{{ $log->city }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium">
                            {{ $log->device ?? 'Desktop' }} · {{ $log->platform ?? 'OS' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-700 dark:text-gray-300">
                        <span class="font-medium">{{ $log->browser ?? 'Browser' }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500 max-w-xs truncate" title="{{ $log->url }}">
                        {{ parse_url($log->url, PHP_URL_PATH) ?: '/' }}
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400 whitespace-nowrap">
                        <span class="hidden">{{ $log->updated_at?->timestamp ?? 0 }}</span>
                        <span>{{ $log->updated_at ? $log->updated_at->diffForHumans() : 'Just now' }}</span>
                        <span class="text-[10px] text-gray-400 block">{{ $log->updated_at?->format('M j, Y H:i') }}</span>
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <button type="button" onclick="deleteVisitorLogAjax({{ $log->id }}, this)" class="inline-flex items-center justify-center w-7 h-7 text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 rounded-lg transition" title="Delete Log">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- SECTION 5: PRODUCTS PERFORMANCE & LOW STOCK MANAGEMENT (ALL CLICKABLE!) --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    {{-- Top Selling Products (CLICKABLE) --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col justify-between">
        <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h2 class="font-bold text-gray-900 dark:text-white text-sm">Top Selling Products</h2>
                <p class="text-[11px] text-gray-500 mt-0.5">Click any product to manage or edit</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">All Products</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800 flex-1">
            @forelse($topSellingItems->take(6) as $item)
            @php $p = $item->product; @endphp
            @if($p)
            <div class="p-4 flex items-center justify-between gap-3 hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition group">
                <a href="{{ route('admin.products.edit', $p) }}" class="flex items-center gap-3 min-w-0 flex-1" title="Click to edit {{ $p->name }}">
                    <img src="{{ $p->thumbnail_url }}" alt="{{ $p->name }}" class="w-11 h-11 rounded-xl object-cover shrink-0 border border-gray-200 dark:border-gray-700 group-hover:ring-2 group-hover:ring-brand-500 transition">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 truncate transition">{{ $p->name }}</p>
                        <p class="text-[11px] text-gray-400 truncate">{{ $p->category?->name ?? 'Uncategorized' }} · {{ $currency }}{{ number_format($p->price, 2) }}</p>
                    </div>
                </a>
                <div class="flex items-center gap-2 shrink-0">
                    <div class="text-right">
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 block">{{ $item->total_sold }} sold</span>
                        @if(!empty($item->total_revenue))
                        <span class="text-[10px] text-gray-400">{{ $currency }}{{ number_format($item->total_revenue, 2) }}</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.products.edit', $p) }}" class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/30 transition" title="Edit Product">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                </div>
            </div>
            @endif
            @empty
            <p class="p-6 text-sm text-gray-400 text-center">No sales recorded yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Most Viewed Products (CLICKABLE) --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col justify-between">
        <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h2 class="font-bold text-gray-900 dark:text-white text-sm">Most Viewed Products</h2>
                <p class="text-[11px] text-gray-500 mt-0.5">Click any product to inspect details</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">All Products</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800 flex-1">
            @forelse($mostViewedItems->take(6) as $p)
            <div class="p-4 flex items-center justify-between gap-3 hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition group">
                <a href="{{ route('admin.products.edit', $p) }}" class="flex items-center gap-3 min-w-0 flex-1" title="Click to edit {{ $p->name }}">
                    <img src="{{ $p->thumbnail_url }}" alt="{{ $p->name }}" class="w-11 h-11 rounded-xl object-cover shrink-0 border border-gray-200 dark:border-gray-700 group-hover:ring-2 group-hover:ring-purple-500 transition">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 truncate transition">{{ $p->name }}</p>
                        <p class="text-[11px] text-gray-400 truncate">{{ $p->category?->name ?? 'Uncategorized' }} · Stock: {{ $p->stock }}</p>
                    </div>
                </a>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="inline-flex items-center gap-1 text-xs font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/40 px-2.5 py-1 rounded-full border border-purple-200 dark:border-purple-900">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        {{ number_format($p->views_count) }}
                    </span>
                    <a href="{{ route('admin.products.edit', $p) }}" class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition" title="Edit Product">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                </div>
            </div>
            @empty
            <p class="p-6 text-sm text-gray-400 text-center">No view logs recorded yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Low Stock Inventory Alert & Quick Manage Card (CLICKABLE & QUICK MANAGE) --}}
    <div id="low-stock-section" class="bg-white dark:bg-gray-900 rounded-2xl border-2 {{ $lowStockItems->count() > 0 ? 'border-red-300 dark:border-red-900/60' : 'border-gray-200 dark:border-gray-800' }} shadow-sm overflow-hidden flex flex-col justify-between">
        <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800 bg-red-50/40 dark:bg-red-950/20">
            <div>
                <h2 class="font-bold text-red-700 dark:text-red-400 text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Low Stock Alerts
                </h2>
                <p class="text-[11px] text-gray-500 mt-0.5">Click product or use inline stock adjustment</p>
            </div>
            <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="text-xs font-semibold text-red-600 dark:text-red-400 hover:underline">View All</a>
        </div>
        <div id="low-stock-card-list" class="divide-y divide-gray-100 dark:divide-gray-800 flex-1 max-h-96 overflow-y-auto">
            @forelse($lowStockItems->take(6) as $p)
            <div id="low-stock-row-{{ $p->id }}" class="low-stock-card-row p-3.5 flex items-center justify-between gap-3 hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition">
                <a href="{{ route('admin.products.edit', $p) }}" class="flex items-center gap-2.5 min-w-0 flex-1 group" title="Edit {{ $p->name }}">
                    <img src="{{ $p->thumbnail_url }}" alt="{{ $p->name }}" class="w-10 h-10 rounded-lg object-cover shrink-0 border border-gray-200 dark:border-gray-700">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-900 dark:text-white truncate group-hover:text-red-600 dark:group-hover:text-red-400 transition">{{ $p->name }}</p>
                        <span class="product-stock-badge-{{ $p->id }} section-stock-badge inline-block text-[11px] font-bold {{ $p->stock == 0 ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $p->stock == 0 ? 'OUT OF STOCK' : $p->stock . ' units remaining' }}
                        </span>
                    </div>
                </a>
                <div class="flex items-center gap-1.5 shrink-0">
                    <input type="number" id="quick-stock-input-{{ $p->id }}" value="{{ $p->stock }}" min="0" class="w-14 px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-center font-bold text-gray-900 dark:text-white focus:ring-1 focus:ring-red-500">
                    <button type="button" onclick="quickUpdateStock({{ $p->id }}, this)" class="px-2.5 py-1 text-xs font-semibold bg-red-600 text-white rounded-lg hover:bg-red-700 transition" title="Update Stock">
                        Save
                    </button>
                    <a href="{{ route('admin.products.edit', $p) }}" class="p-1 text-gray-400 hover:text-gray-700 dark:hover:text-white" title="Full Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            @empty
            <p id="low-stock-card-empty" class="p-6 text-sm text-gray-400 text-center">All inventory levels are healthy!</p>
            @endforelse
        </div>
    </div>
</div>

{{-- SECTION 6: RECENT ORDERS TABLE --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden mb-8">
    <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h2 class="font-bold text-gray-900 dark:text-white text-sm">Recent Orders</h2>
            <p class="text-xs text-gray-500 mt-0.5">Latest customer checkout transactions</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">View All Orders →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-400 uppercase tracking-wide border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40">
                    <th class="text-left px-5 py-3">Order Number</th>
                    <th class="text-left px-5 py-3">Customer</th>
                    <th class="text-left px-5 py-3">Amount</th>
                    <th class="text-left px-5 py-3">Status</th>
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-right px-5 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($recentOrders as $order)
                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                    <td class="px-5 py-3.5 font-mono text-xs">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-brand-600 dark:text-brand-400 hover:underline font-semibold">{{ $order->order_number }}</a>
                    </td>
                    <td class="px-5 py-3.5 text-gray-800 dark:text-gray-200 text-xs">{{ $order->shipping_name ?? $order->user?->name ?? 'Customer' }}</td>
                    <td class="px-5 py-3.5 font-semibold text-xs text-gray-900 dark:text-white">{{ $currency }}{{ number_format($order->total, 2) }}</td>
                    <td class="px-5 py-3.5">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                            {{ $order->status_label }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $order->created_at->format('M j, Y H:i') }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">Inspect</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-xs text-gray-400">No orders placed yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- LOW STOCK MANAGEMENT MODAL (TRIGGERED BY CLICKING LOW STOCK ALERT CARD) --}}
<div id="lowStockModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden" role="dialog" aria-modal="true">
    <div class="bg-white dark:bg-gray-900 rounded-3xl max-w-2xl w-full border border-gray-200 dark:border-gray-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-red-50/50 dark:bg-red-950/20">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-base">Low Stock Inventory Manager</h3>
                    <p class="text-xs text-gray-500">Products currently at or below 10 units in warehouse.</p>
                </div>
            </div>
            <button type="button" onclick="closeLowStockModal()" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-white flex items-center justify-center transition">
                ✕
            </button>
        </div>

        <div id="low-stock-modal-list" class="p-5 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800 flex-1">
            @forelse($lowStockItems as $p)
            <div id="modal-low-stock-row-{{ $p->id }}" class="modal-low-stock-row py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <img src="{{ $p->thumbnail_url }}" alt="{{ $p->name }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                    <div class="min-w-0">
                        <a href="{{ route('admin.products.edit', $p) }}" class="font-semibold text-sm text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 transition block truncate">
                            {{ $p->name }}
                        </a>
                        <p class="text-xs text-gray-400 truncate">{{ $p->sku ?: 'No SKU' }} · {{ $p->category?->name ?? 'Uncategorized' }} · {{ $currency }}{{ number_format($p->price, 2) }}</p>
                        <span class="product-stock-badge-{{ $p->id }} modal-stock-badge inline-block text-[11px] font-bold mt-0.5 {{ $p->stock == 0 ? 'text-red-600 dark:text-red-400' : 'text-amber-500 dark:text-amber-400' }}">
                            {{ $p->stock == 0 ? 'OUT OF STOCK (0 units)' : $p->stock . ' units in stock' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                    <div class="flex items-center gap-1.5">
                        <label for="modal-stock-input-{{ $p->id }}" class="text-[11px] text-gray-400 font-medium">Stock:</label>
                        <input type="number" id="modal-stock-input-{{ $p->id }}" value="{{ $p->stock }}" min="0" class="w-16 px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-center font-bold text-gray-900 dark:text-white">
                        <button type="button" onclick="quickUpdateStock({{ $p->id }}, this)" class="px-3 py-1 text-xs font-semibold bg-red-600 hover:bg-red-700 text-white rounded-lg transition shadow-xs">
                            Update
                        </button>
                    </div>
                    <a href="{{ route('admin.products.edit', $p) }}" class="px-3 py-1 text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition">
                        Edit Full
                    </a>
                </div>
            </div>
            @empty
            <div id="modal-low-stock-empty" class="py-12 text-center text-sm text-gray-400">
                <svg class="w-12 h-12 text-emerald-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                All products have healthy inventory levels!
            </div>
            @endforelse
        </div>

        <div class="p-4 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <span id="modal-low-stock-count" class="text-xs text-gray-500">{{ $lowStockItems->count() }} low stock items found</span>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                    View in Products Table →
                </a>
                <button type="button" onclick="closeLowStockModal()" class="px-4 py-1.5 text-xs font-semibold bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- JSVectorMap CDN --}}
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"></script>

<script>
    // Embedded chart, geo data, and currency
    const currencySymbol = @json($currency);
    const orderChartsData = @json($orderCharts);
    const countryStatsData = @json($countryStats);
    const deviceStatsData = @json($deviceStats);

    let ordersBarChartInstance = null;
    let ordersPieChartInstance = null;
    let deviceDoughnutChartInstance = null;
    let worldMapInstance = null;
    let visitorLogsTable = null;

    document.addEventListener('DOMContentLoaded', function() {
        initOrdersCharts();
        initDeviceDoughnutChart();
        initWorldMap();
        initVisitorLogsDataTable();
    });

    // 1. ORDERS CHARTS (BAR & PIE)
    function initOrdersCharts() {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? 'rgba(55, 65, 81, 0.4)' : 'rgba(243, 244, 246, 0.9)';

        // Initial period: month
        const initialPeriod = 'month';
        const currentData = orderChartsData[initialPeriod];

        // Bar Chart
        const barCtx = document.getElementById('ordersBarChart').getContext('2d');
        ordersBarChartInstance = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: currentData.labels,
                datasets: [
                    {
                        label: 'Orders Count',
                        data: currentData.counts,
                        backgroundColor: 'rgba(20, 184, 166, 0.85)', // teal
                        borderRadius: 6,
                        maxBarThickness: 32,
                        yAxisID: 'yOrders'
                    },
                    {
                        label: `Revenue (${currencySymbol})`,
                        data: currentData.revenues,
                        type: 'line',
                        borderColor: '#3b82f6', // blue
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        yAxisID: 'yRevenue'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: textColor,
                            font: { family: 'Inter', size: 11, weight: 'bold' },
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.yAxisID === 'yRevenue') {
                                    return ` Revenue: ${currencySymbol}${Number(context.raw).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                }
                                return ` Orders: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { size: 10 } }
                    },
                    yOrders: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            stepSize: 1,
                            precision: 0,
                            font: { size: 10 }
                        },
                        grid: { color: gridColor }
                    },
                    yRevenue: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            callback: (val) => currencySymbol + val,
                            font: { size: 10 }
                        },
                        grid: { display: false }
                    }
                }
            }
        });

        // Pie Chart
        const pieCtx = document.getElementById('ordersPieChart').getContext('2d');
        ordersPieChartInstance = new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: currentData.status_breakdown.labels,
                datasets: [{
                    data: currentData.status_breakdown.counts,
                    backgroundColor: currentData.status_breakdown.colors,
                    borderWidth: 2,
                    borderColor: isDark ? '#111827' : '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            font: { family: 'Inter', size: 10 },
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    }
                }
            }
        });
    }

    // Switch timeframe dynamically across Today, This Month, This Year, All Time
    window.switchOrderPeriod = function(periodKey) {
        if (!orderChartsData[periodKey]) return;

        const data = orderChartsData[periodKey];

        // Update active tab buttons styling
        document.querySelectorAll('.period-tab-btn').forEach(btn => {
            btn.className = 'period-tab-btn px-3 py-1.5 rounded-lg transition hover:text-gray-900 dark:hover:text-white text-gray-500 dark:text-gray-400';
        });
        const activeBtn = document.getElementById(`btn-period-${periodKey}`);
        if (activeBtn) {
            activeBtn.className = 'period-tab-btn px-3 py-1.5 rounded-lg transition text-gray-900 dark:text-white bg-white dark:bg-gray-700 shadow-xs font-semibold';
        }

        // Update badge & KPI strips with settings currency symbol
        document.getElementById('current-period-badge').textContent = data.period_name;
        document.getElementById('kpi-period-orders').textContent = Number(data.summary.total_orders).toLocaleString();
        document.getElementById('kpi-period-revenue').textContent = currencySymbol + Number(data.summary.total_revenue).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('kpi-period-avg').textContent = currencySymbol + Number(data.summary.avg_value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

        // Update Bar Chart
        if (ordersBarChartInstance) {
            ordersBarChartInstance.data.labels = data.labels;
            ordersBarChartInstance.data.datasets[0].data = data.counts;
            ordersBarChartInstance.data.datasets[1].data = data.revenues;
            ordersBarChartInstance.update();
        }

        // Update Pie Chart
        if (ordersPieChartInstance) {
            ordersPieChartInstance.data.labels = data.status_breakdown.labels;
            ordersPieChartInstance.data.datasets[0].data = data.status_breakdown.counts;
            ordersPieChartInstance.data.datasets[0].backgroundColor = data.status_breakdown.colors;
            ordersPieChartInstance.update();
        }
    };

    // 2. DEVICE DOUGHNUT CHART
    function initDeviceDoughnutChart() {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const ctx = document.getElementById('deviceDoughnutChart');
        if (!ctx) return;

        const labels = deviceStatsData.devices.map(d => d.name);
        const counts = deviceStatsData.devices.map(d => d.count);
        const colors = ['#3b82f6', '#10b981', '#a855f7', '#f59e0b'];

        deviceDoughnutChartInstance = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: isDark ? '#111827' : '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            font: { family: 'Inter', size: 10 },
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    }
                }
            }
        });
    }

    // 3. INTERACTIVE WORLD MAP (JSVectorMap - FULL WIDTH ASPECT RATIO & ZOOM CONTROLS)
    function initWorldMap() {
        const mapContainer = document.getElementById('world-map-container');
        if (!mapContainer || typeof jsVectorMap === 'undefined') return;

        const isDark = document.documentElement.classList.contains('dark');
        const mapValues = countryStatsData.map_data || {};

        try {
            worldMapInstance = new jsVectorMap({
                selector: '#world-map-container',
                map: 'world',
                zoomButtons: false,
                zoomOnScroll: false,
                zoomMin: 0.35,  // Allows minimizing smaller than default scale!
                zoomMax: 12,
                zoomStep: 1.4,
                backgroundColor: 'transparent',
                regionStyle: {
                    initial: {
                        fill: isDark ? '#1f2937' : '#e5e7eb',
                        stroke: isDark ? '#111827' : '#ffffff',
                        strokeWidth: 0.5,
                        fillOpacity: 1
                    },
                    hover: {
                        fill: '#0d9488', // teal-600
                        cursor: 'pointer'
                    }
                },
                series: {
                    regions: [{
                        values: mapValues,
                        scale: isDark ? ['#134e4a', '#14b8a6'] : ['#ccfbf1', '#0f766e'],
                        normalizeFunction: 'polynomial'
                    }]
                },
                onRegionTooltipShow(event, tooltip, code) {
                    const countryName = tooltip.text();
                    const count = mapValues[code] || 0;
                    const total = countryStatsData.total_visitors || 1;
                    const pct = ((count / total) * 100).toFixed(1);
                    tooltip.text(
                        `<div style="font-weight: bold; margin-bottom: 2px;">${countryName} (${code})</div>` +
                        `<div>${count} visitors (${pct}%)</div>`,
                        true
                    );
                }
            });
        } catch (err) {
            console.warn('Vector map initialization notice:', err);
        }
    }

    // World map zoom helpers (Zoom In & Zoom Out)
    window.mapZoomIn = function() {
        if (!worldMapInstance) return;
        if (typeof worldMapInstance._setScale === 'function') {
            const step = worldMapInstance.params?.zoomStep || 1.4;
            const w = worldMapInstance._width || worldMapInstance.container?.clientWidth || 0;
            const h = worldMapInstance._height || worldMapInstance.container?.clientHeight || 0;
            worldMapInstance._setScale(worldMapInstance.scale * step, w / 2, h / 2, false, true);
        }
    };

    window.mapZoomOut = function() {
        if (!worldMapInstance) return;
        if (typeof worldMapInstance._setScale === 'function') {
            const step = worldMapInstance.params?.zoomStep || 1.4;
            const w = worldMapInstance._width || worldMapInstance.container?.clientWidth || 0;
            const h = worldMapInstance._height || worldMapInstance.container?.clientHeight || 0;
            worldMapInstance._setScale(worldMapInstance.scale / step, w / 2, h / 2, false, true);
        }
    };

    window.mapReset = function() {
        if (worldMapInstance && typeof worldMapInstance.reset === 'function') {
            worldMapInstance.reset();
        }
    };

    // 4. JQUERY DATATABLE FOR VISITOR LOGS (10 PER PAGE FRONTEND PAGINATION & AJAX DELETION)
    function initVisitorLogsDataTable() {
        $('#visitor-logs-datatable tbody tr td[colspan]').closest('tr').remove();
        visitorLogsTable = $('#visitor-logs-datatable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[6, 'desc']],
            columnDefs: [
                { orderable: false, targets: [0, 7] }
            ],
            language: {
                emptyTable: "No visitor records logged yet.",
                zeroRecords: "No matching visitor logs found.",
                search: "_INPUT_",
                searchPlaceholder: "Search logs (IP, country, device, URL)...",
                paginate: {
                    previous: "‹ Prev",
                    next: "Next ›"
                }
            }
        });

        // Select all rows across the DataTable
        $('#select-all-visitor-logs').on('change', function() {
            const isChecked = $(this).is(':checked');
            visitorLogsTable.$('.visitor-log-row-checkbox').prop('checked', isChecked);
            updateVisitorLogsBulkState();
        });

        // Individual row checkbox change
        $('#visitor-logs-datatable').on('change', '.visitor-log-row-checkbox', function() {
            const total = visitorLogsTable.$('.visitor-log-row-checkbox').length;
            const checked = visitorLogsTable.$('.visitor-log-row-checkbox:checked').length;
            $('#select-all-visitor-logs').prop('checked', total > 0 && checked === total);
            updateVisitorLogsBulkState();
        });

        // Synchronize checkbox state on table redraw (pagination, search, etc.)
        visitorLogsTable.on('draw', function() {
            const total = visitorLogsTable.$('.visitor-log-row-checkbox').length;
            const checked = visitorLogsTable.$('.visitor-log-row-checkbox:checked').length;
            $('#select-all-visitor-logs').prop('checked', total > 0 && checked === total);
            updateVisitorLogsBulkState();
        });

        function updateVisitorLogsBulkState() {
            const count = visitorLogsTable.$('.visitor-log-row-checkbox:checked').length;
            $('#selected-visitor-logs-count').text(count);
            $('#bulk-delete-visitor-logs-btn').prop('disabled', count === 0);
        }
        window.updateVisitorLogsBulkState = updateVisitorLogsBulkState;
    }

    // 5. AJAX DELETE VISITOR LOG (NO PAGE RELOAD)
    window.deleteVisitorLogAjax = function(id, btn) {
        const confirmMsg = "Are you sure you want to permanently delete this visitor log?";

        const doDelete = async () => {
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>';

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/admin/visitor-logs/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    if (window.toast) window.toast(data.message, 'success');
                    const tr = $(btn).closest('tr');
                    visitorLogsTable.row(tr).remove().draw(false);
                    if (typeof window.updateVisitorLogsBulkState === 'function') {
                        window.updateVisitorLogsBulkState();
                    }
                } else {
                    if (window.toast) window.toast(data.message || 'Failed to delete log', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            } catch (err) {
                if (window.toast) window.toast('Error deleting visitor log', 'error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        };

        if (window.Swal) {
            const isDark = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: 'Delete Visitor Log?',
                text: confirmMsg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                background: isDark ? '#111827' : '#ffffff',
                color: isDark ? '#f3f4f6' : '#111827',
                customClass: {
                    popup: 'rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) doDelete();
            });
        } else {
            if (confirm(confirmMsg)) doDelete();
        }
    };

    // 6. AJAX BULK DELETE VISITOR LOGS (NO PAGE RELOAD)
    window.bulkDeleteVisitorLogsAjax = function(btn) {
        const checkedBoxes = visitorLogsTable.$('.visitor-log-row-checkbox:checked');
        const count = checkedBoxes.length;
        if (count === 0) return;

        const ids = [];
        checkedBoxes.each(function() {
            ids.push($(this).val());
        });

        const confirmMsg = `Are you sure you want to permanently delete ${count} selected visitor log${count === 1 ? '' : 's'}?`;

        const doBulkDelete = async () => {
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>';

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/admin/visitor-logs/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ ids: ids })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    if (window.toast) window.toast(data.message, 'success');
                    checkedBoxes.each(function() {
                        const tr = $(this).closest('tr');
                        visitorLogsTable.row(tr).remove();
                    });
                    visitorLogsTable.draw(false);
                    $('#select-all-visitor-logs').prop('checked', false);
                    if (typeof window.updateVisitorLogsBulkState === 'function') {
                        window.updateVisitorLogsBulkState();
                    }
                } else {
                    if (window.toast) window.toast(data.message || 'Failed to bulk delete logs', 'error');
                }
            } catch (err) {
                if (window.toast) window.toast('Error performing bulk delete', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                if (typeof window.updateVisitorLogsBulkState === 'function') {
                    window.updateVisitorLogsBulkState();
                }
            }
        };

        if (window.Swal) {
            const isDark = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: 'Confirm Bulk Deletion',
                text: confirmMsg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                background: isDark ? '#111827' : '#ffffff',
                color: isDark ? '#f3f4f6' : '#111827',
                customClass: {
                    popup: 'rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) doBulkDelete();
            });
        } else {
            if (confirm(confirmMsg)) doBulkDelete();
        }
    };

    // 7. LOW STOCK MODAL HANDLERS
    window.openLowStockModal = function() {
        const modal = document.getElementById('lowStockModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    };

    window.closeLowStockModal = function() {
        const modal = document.getElementById('lowStockModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    };

    // Close modal on click backdrop or escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLowStockModal();
    });
    document.getElementById('lowStockModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeLowStockModal();
    });

    // 8. QUICK STOCK UPDATE VIA AJAX
    window.quickUpdateStock = async function(productId, btnEl) {
        // Find input directly adjacent or within the same container as the button clicked
        const rowEl = btnEl.closest('.flex') || btnEl.parentElement;
        const inputEl = rowEl ? rowEl.querySelector('input[type="number"]') : null;
        const fallbackInput = document.getElementById(`modal-stock-input-${productId}`) || document.getElementById(`quick-stock-input-${productId}`);
        const targetInput = inputEl || fallbackInput;
        const val = targetInput ? targetInput.value : null;

        if (val === null || val === '') return;

        const originalHtml = btnEl.innerHTML;
        btnEl.disabled = true;
        btnEl.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>';

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/admin/products/${productId}/stock`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ stock: parseInt(val, 10) })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                if (window.toast) window.toast(data.message, 'success');

                const newStock = data.product.stock;

                // Sync all inputs on the dashboard for this product
                const cardInput = document.getElementById(`quick-stock-input-${productId}`);
                const modalInput = document.getElementById(`modal-stock-input-${productId}`);
                if (cardInput) cardInput.value = newStock;
                if (modalInput) modalInput.value = newStock;

                // Update all badges for this product across dashboard cards & modal
                document.querySelectorAll(`.product-stock-badge-${productId}`).forEach(badge => {
                    const isModalBadge = badge.classList.contains('modal-stock-badge');
                    if (newStock === 0) {
                        badge.textContent = isModalBadge ? 'OUT OF STOCK (0 units)' : 'OUT OF STOCK';
                        badge.className = `product-stock-badge-${productId} ${isModalBadge ? 'modal-stock-badge' : 'section-stock-badge'} inline-block text-[11px] font-bold mt-0.5 text-red-600 dark:text-red-400`;
                    } else if (newStock <= 10) {
                        badge.textContent = isModalBadge ? `${newStock} units in stock` : `${newStock} units remaining`;
                        badge.className = `product-stock-badge-${productId} ${isModalBadge ? 'modal-stock-badge' : 'section-stock-badge'} inline-block text-[11px] font-bold mt-0.5 text-amber-600 dark:text-amber-400`;
                    } else {
                        badge.textContent = `${newStock} units in stock`;
                        badge.className = `product-stock-badge-${productId} ${isModalBadge ? 'modal-stock-badge' : 'section-stock-badge'} inline-block text-[11px] font-bold mt-0.5 text-emerald-600 dark:text-emerald-400`;
                    }
                });

                // Update low stock KPIs and badges in real-time
                if (typeof data.low_stock_count !== 'undefined') {
                    const topCountEl = document.getElementById('top-low-stock-count');
                    if (topCountEl) topCountEl.textContent = Number(data.low_stock_count).toLocaleString();

                    const modalCountEl = document.getElementById('modal-low-stock-count');
                    if (modalCountEl) modalCountEl.textContent = `${data.low_stock_count} low stock items found`;

                    const topCardEl = document.getElementById('top-low-stock-card');
                    const pingEl = document.getElementById('top-low-stock-ping');
                    if (data.low_stock_count === 0) {
                        if (topCardEl) {
                            topCardEl.classList.remove('border-red-400/80', 'dark:border-red-600/80', 'bg-red-50/20', 'dark:bg-red-950/10');
                            topCardEl.classList.add('border-gray-200', 'dark:border-gray-800');
                        }
                        if (pingEl) pingEl.classList.add('hidden');
                    } else {
                        if (topCardEl) {
                            topCardEl.classList.add('border-red-400/80', 'dark:border-red-600/80', 'bg-red-50/20', 'dark:bg-red-950/10');
                            topCardEl.classList.remove('border-gray-200', 'dark:border-gray-800');
                        }
                        if (pingEl) pingEl.classList.remove('hidden');
                    }
                }

                // If stock has passed the low stock / out of stock threshold (> 10 units), remove the row from Section 5 and Modal
                if (newStock > 10) {
                    const cardRow = document.getElementById(`low-stock-row-${productId}`);
                    if (cardRow) {
                        cardRow.style.transition = 'all 0.3s ease-out';
                        cardRow.style.opacity = '0';
                        cardRow.style.transform = 'translateX(20px)';
                        setTimeout(() => {
                            cardRow.remove();
                            const cardList = document.getElementById('low-stock-card-list');
                            if (cardList && cardList.querySelectorAll('.low-stock-card-row').length === 0) {
                                cardList.innerHTML = '<p id="low-stock-card-empty" class="p-6 text-sm text-gray-400 text-center">All inventory levels are healthy!</p>';
                                const sectionBox = document.getElementById('low-stock-section');
                                if (sectionBox) {
                                    sectionBox.classList.remove('border-red-300', 'dark:border-red-900/60');
                                    sectionBox.classList.add('border-gray-200', 'dark:border-gray-800');
                                }
                            }
                        }, 300);
                    }

                    const modalRow = document.getElementById(`modal-low-stock-row-${productId}`);
                    if (modalRow) {
                        modalRow.style.transition = 'all 0.3s ease-out';
                        modalRow.style.opacity = '0';
                        modalRow.style.transform = 'translateX(20px)';
                        setTimeout(() => {
                            modalRow.remove();
                            const modalList = document.getElementById('low-stock-modal-list');
                            if (modalList && modalList.querySelectorAll('.modal-low-stock-row').length === 0) {
                                modalList.innerHTML = `
                                    <div id="modal-low-stock-empty" class="py-12 text-center text-sm text-gray-400">
                                        <svg class="w-12 h-12 text-emerald-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        All products have healthy inventory levels!
                                    </div>`;
                            }
                        }, 300);
                    }
                }
            } else {
                if (window.toast) window.toast(data.message || 'Validation error updating stock', 'error');
            }
        } catch (err) {
            if (window.toast) window.toast('Failed to update stock', 'error');
        } finally {
            btnEl.disabled = false;
            btnEl.innerHTML = originalHtml;
        }
    };

    // Ensure returning from product edit page displays the freshest values
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>
@endpush

@endsection
