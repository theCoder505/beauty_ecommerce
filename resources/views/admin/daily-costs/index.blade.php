@extends('admin.layouts.app')

@section('title', 'Daily Costs & Profit/Loss Report')

@section('content')
@php
    $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳');
@endphp

<div x-data="dailyCostsManager()" class="space-y-6">

    {{-- Header Banner --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-teal-950 text-brand-700 dark:text-brand-300 flex items-center justify-center font-bold shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold font-serif text-gray-900 dark:text-white">Daily Costs & Profit/Loss</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Record inventory and operating costs, track sales automatically, and monitor real-time profit and loss.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.reports.profit-loss', ['period' => $period, 'date_from' => $startDate ? $startDate->format('Y-m-d') : null, 'date_to' => $endDate ? $endDate->format('Y-m-d') : null]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 transition shadow-2xs">
                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Full P&L Analytics</span>
            </a>

            <a href="{{ route('admin.daily-costs.export', ['period' => $period, 'date_from' => $startDate ? $startDate->format('Y-m-d') : null, 'date_to' => $endDate ? $endDate->format('Y-m-d') : null]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 transition shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </a>

            <button type="button" @click="openCreateModal()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-brand-700 hover:bg-brand-800 text-white shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Daily Cost</span>
            </button>
        </div>
    </div>

    {{-- Period Filter Toolbar --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 flex-wrap">
        {{-- Quick Pills --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider mr-1 hidden sm:inline">Period:</span>
            
            <a href="{{ route('admin.daily-costs.index', ['period' => 'today']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition whitespace-nowrap {{ $period === 'today' ? 'bg-brand-700 text-white shadow-xs' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                Today
            </a>

            <a href="{{ route('admin.daily-costs.index', ['period' => 'monthly']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition whitespace-nowrap {{ $period === 'monthly' ? 'bg-brand-700 text-white shadow-xs' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                This Month
            </a>

            <a href="{{ route('admin.daily-costs.index', ['period' => 'yearly']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition whitespace-nowrap {{ $period === 'yearly' ? 'bg-brand-700 text-white shadow-xs' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                This Year
            </a>

            <a href="{{ route('admin.daily-costs.index', ['period' => 'alltime']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition whitespace-nowrap {{ $period === 'alltime' ? 'bg-brand-700 text-white shadow-xs' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                All Time
            </a>
        </div>

        {{-- Custom Date Form --}}
        <form action="{{ route('admin.daily-costs.index') }}" method="GET" class="flex items-center gap-2 flex-wrap">
            <input type="hidden" name="period" value="custom">
            @if($categoryFilter)
                <input type="hidden" name="category" value="{{ $categoryFilter }}">
            @endif
            <div class="flex items-center gap-1.5 text-xs">
                <span class="text-gray-400">From:</span>
                <input type="date" name="date_from" value="{{ request('date_from', $startDate ? $startDate->format('Y-m-d') : '') }}"
                       class="px-2.5 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs focus:ring-1 focus:ring-brand-600 focus:outline-none">
            </div>
            <div class="flex items-center gap-1.5 text-xs">
                <span class="text-gray-400">To:</span>
                <input type="date" name="date_to" value="{{ request('date_to', $endDate ? $endDate->format('Y-m-d') : '') }}"
                       class="px-2.5 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs focus:ring-1 focus:ring-brand-600 focus:outline-none">
            </div>
            <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-gray-900 hover:bg-black text-white dark:bg-gray-100 dark:hover:bg-white dark:text-gray-900 transition">
                Apply Range
            </button>
            @if($period === 'custom' || request()->filled('date_from'))
                <a href="{{ route('admin.daily-costs.index') }}" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 underline">Reset</a>
            @endif
        </form>
    </div>

    {{-- Current Period Badge --}}
    <div class="flex items-center justify-between text-xs text-gray-500">
        <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
            <span>Active Financial Window:</span>
            <strong class="font-semibold text-gray-900 dark:text-white">{{ $periodLabel }}</strong>
        </span>
    </div>

    {{-- Executive Summary Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Sales Revenue --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>Product Sales (Revenue)</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold font-serif text-gray-900 dark:text-white">{{ $currency }}{{ number_format($summary['gross_sales'], 2) }}</p>
            <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                <span>{{ $summary['fulfilled_count'] }} fulfilled {{ Str::plural('order', $summary['fulfilled_count']) }}</span>
                @if($summary['shipping_collected'] > 0)
                    <span class="text-emerald-600 font-mono">+{{ $currency }}{{ number_format($summary['shipping_collected'], 2) }} ship</span>
                @endif
            </div>
        </div>

        {{-- Card 2: Daily Costs (Inventory & Operations) --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>Total Recorded Costs</span>
                <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold font-serif text-indigo-700 dark:text-indigo-400">{{ $currency }}{{ number_format($summary['total_costs'], 2) }}</p>
            <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                <span>Inventory: {{ $currency }}{{ number_format($summary['inventory_costs'], 2) }}</span>
                <span class="font-mono text-gray-400">{{ $summary['costs_count'] }} entries</span>
            </div>
        </div>

        {{-- Card 3: Refunds & Returns Loss --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>Refunds / Returns Loss</span>
                <span class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold font-serif text-rose-600 dark:text-rose-400">-{{ $currency }}{{ number_format($summary['total_refunds'], 2) }}</p>
            <div class="mt-2 text-xs text-gray-500">
                <span>{{ $summary['refunded_count'] }} refunded {{ Str::plural('order', $summary['refunded_count']) }} deducted</span>
            </div>
        </div>

        {{-- Card 4: Net Profit / Loss --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border {{ $summary['net_profit'] >= 0 ? 'border-emerald-300 dark:border-emerald-800' : 'border-rose-300 dark:border-rose-800' }} p-5 shadow-sm">
            <div class="flex items-center justify-between text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>Net Profit / Loss</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $summary['net_profit'] >= 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200' }}">
                    {{ $summary['net_profit'] >= 0 ? 'NET PROFIT' : 'NET LOSS' }}
                </span>
            </div>
            <p class="text-2xl font-bold font-serif {{ $summary['net_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                {{ $currency }}{{ number_format($summary['net_profit'], 2) }}
            </p>
            <div class="mt-2 text-xs text-gray-500 flex items-center justify-between">
                <span>Margin: <strong class="{{ $summary['profit_margin'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($summary['profit_margin'], 1) }}%</strong></span>
                <span class="text-[11px] text-gray-400">Sales - Costs - Refunds</span>
            </div>
        </div>
    </div>

    {{-- Simple P&L Statement Card --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 mb-4">
            <div>
                <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-white">Simple Profit & Loss Statement</h2>
                <p class="text-xs text-gray-500 mt-0.5">Summary calculation based on sales fulfilled and inventory costs recorded for {{ $periodLabel }}.</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-mono text-gray-400">Formula: Net Revenue - Total Costs</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Left column: Revenue Breakdown --}}
            <div class="space-y-3 bg-gray-50 dark:bg-gray-800/40 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">1. Sales & Revenue</h3>
                
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-700 dark:text-gray-300">Gross Product Sales</span>
                    <span class="font-mono font-semibold text-gray-900 dark:text-white">{{ $currency }}{{ number_format($summary['gross_sales'], 2) }}</span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Promotional Discounts</span>
                    <span class="font-mono text-gray-500">- {{ $currency }}{{ number_format($summary['total_discounts'], 2) }}</span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Refunds Payout (Returns Loss)</span>
                    <span class="font-mono text-rose-600">- {{ $currency }}{{ number_format($summary['total_refunds'], 2) }}</span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Shipping Fees Collected</span>
                    <span class="font-mono text-emerald-600">+ {{ $currency }}{{ number_format($summary['shipping_collected'], 2) }}</span>
                </div>

                <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between font-bold text-sm">
                    <span class="text-gray-900 dark:text-white">Net Sales Revenue</span>
                    <span class="font-mono text-brand-700 dark:text-brand-400">{{ $currency }}{{ number_format($summary['net_sales'] + $summary['shipping_collected'], 2) }}</span>
                </div>
            </div>

            {{-- Right column: Cost Breakdown & Net P&L --}}
            <div class="space-y-3 bg-gray-50 dark:bg-gray-800/40 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">2. Inventory & Recorded Expenses</h3>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-700 dark:text-gray-300">Inventory Procurement Costs</span>
                    <span class="font-mono font-semibold text-indigo-700 dark:text-indigo-400">{{ $currency }}{{ number_format($summary['inventory_costs'], 2) }}</span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Packaging Materials</span>
                    <span class="font-mono text-gray-500">{{ $currency }}{{ number_format($summary['packaging_costs'], 2) }}</span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Shipping & Logistics Costs</span>
                    <span class="font-mono text-gray-500">{{ $currency }}{{ number_format($summary['shipping_costs'], 2) }}</span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Operational & Marketing</span>
                    <span class="font-mono text-gray-500">{{ $currency }}{{ number_format($summary['operational_costs'] + $summary['other_costs'], 2) }}</span>
                </div>

                <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between font-bold text-sm">
                    <span class="text-gray-900 dark:text-white">Total Recorded Daily Costs</span>
                    <span class="font-mono text-indigo-700 dark:text-indigo-400">{{ $currency }}{{ number_format($summary['total_costs'], 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Bottom Final Result Box --}}
        <div class="mt-5 p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $summary['net_profit'] >= 0 ? 'bg-emerald-50/70 border-emerald-300 dark:bg-emerald-950/20 dark:border-emerald-800' : 'bg-rose-50/70 border-rose-300 dark:bg-rose-950/20 dark:border-rose-800' }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $summary['net_profit'] >= 0 ? 'bg-emerald-200 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200' : 'bg-rose-200 dark:bg-rose-900 text-rose-800 dark:text-rose-200' }} flex items-center justify-center font-bold text-lg shrink-0">
                    {{ $summary['net_profit'] >= 0 ? '✓' : '!' }}
                </div>
                <div>
                    <h4 class="font-bold text-base {{ $summary['net_profit'] >= 0 ? 'text-emerald-900 dark:text-emerald-200' : 'text-rose-900 dark:text-rose-200' }}">
                        Final Net Result: {{ $summary['net_profit'] >= 0 ? 'Net Profit' : 'Net Loss' }}
                    </h4>
                    <p class="text-xs {{ $summary['net_profit'] >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                        Net Sales Revenue ({{ $currency }}{{ number_format($summary['net_sales'] + $summary['shipping_collected'], 2) }}) minus Recorded Costs ({{ $currency }}{{ number_format($summary['total_costs'], 2) }}).
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs uppercase tracking-wider font-semibold {{ $summary['net_profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    Net {{ $summary['net_profit'] >= 0 ? 'Profit' : 'Loss' }}
                </span>
                <p class="text-2xl font-black font-serif {{ $summary['net_profit'] >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                    {{ $currency }}{{ number_format($summary['net_profit'], 2) }}
                </p>
            </div>
        </div>
    </div>

    {{-- Day-by-Day Timeline Breakdown (If multiple days in period) --}}
    @if(count($dailyTimeline) > 1)
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-4">
            <div>
                <h3 class="font-serif text-base font-bold text-gray-900 dark:text-white">Daily Performance Breakdown</h3>
                <p class="text-xs text-gray-500">Compare daily sales revenue against recorded daily costs and refunds day by day.</p>
            </div>
            <span class="text-xs text-gray-400 font-mono">{{ count($dailyTimeline) }} Active Days</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-400 uppercase tracking-wider">
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3 text-right">Sales Revenue</th>
                        <th class="py-2.5 px-3 text-right">Refunds Loss</th>
                        <th class="py-2.5 px-3 text-right">Daily Costs</th>
                        <th class="py-2.5 px-3 text-right">Net Profit / Loss</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-mono">
                    @foreach($dailyTimeline as $day)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                        <td class="py-2.5 px-3 font-medium text-gray-800 dark:text-gray-200 font-sans">{{ $day['date'] }}</td>
                        <td class="py-2.5 px-3 text-right text-gray-900 dark:text-white font-semibold">{{ $currency }}{{ number_format($day['sales'], 2) }}</td>
                        <td class="py-2.5 px-3 text-right text-rose-600">{{ $day['refunds'] > 0 ? '-' . $currency . number_format($day['refunds'], 2) : '—' }}</td>
                        <td class="py-2.5 px-3 text-right text-indigo-600 dark:text-indigo-400">{{ $day['costs'] > 0 ? '-' . $currency . number_format($day['costs'], 2) : '—' }}</td>
                        <td class="py-2.5 px-3 text-right font-bold {{ $day['net'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $day['net'] >= 0 ? '+' : '' }}{{ $currency }}{{ number_format($day['net'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Daily Cost Entries Table --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h3 class="font-serif text-lg font-bold text-gray-900 dark:text-white">Daily Cost Entries & Inventory Log</h3>
                <p class="text-xs text-gray-500 mt-0.5">List of all recorded expenses, supplier purchases, and costs for {{ $periodLabel }}.</p>
            </div>
            
            {{-- Category Filter & Search --}}
            <div class="flex items-center gap-2 flex-wrap">
                <form action="{{ route('admin.daily-costs.index') }}" method="GET" class="flex items-center gap-2 flex-wrap">
                    <input type="hidden" name="period" value="{{ $period }}">
                    @if(request('date_from')) <input type="hidden" name="date_from" value="{{ request('date_from') }}"> @endif
                    @if(request('date_to')) <input type="hidden" name="date_to" value="{{ request('date_to') }}"> @endif

                    <select name="category" onchange="this.form.submit()"
                            class="px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs focus:ring-1 focus:ring-brand-600 focus:outline-none">
                        <option value="">All Categories</option>
                        <option value="inventory" {{ request('category') === 'inventory' ? 'selected' : '' }}>Inventory Procurement</option>
                        <option value="packaging" {{ request('category') === 'packaging' ? 'selected' : '' }}>Packaging Materials</option>
                        <option value="shipping_logistics" {{ request('category') === 'shipping_logistics' ? 'selected' : '' }}>Shipping & Logistics</option>
                        <option value="operational" {{ request('category') === 'operational' ? 'selected' : '' }}>Operational</option>
                        <option value="marketing" {{ request('category') === 'marketing' ? 'selected' : '' }}>Marketing</option>
                        <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>

                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, PO, product..."
                               class="pl-8 pr-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs focus:ring-1 focus:ring-brand-600 focus:outline-none w-48 sm:w-56">
                        <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <button type="submit" class="px-2.5 py-1.5 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 transition">
                        Filter
                    </button>
                    @if(request()->anyFilled(['category', 'search']))
                        <a href="{{ route('admin.daily-costs.index', ['period' => $period]) }}" class="text-xs text-gray-400 hover:text-gray-600 underline">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Table Content --}}
        @if($dailyCosts->isEmpty())
            <div class="text-center py-12">
                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">No daily cost records found</h4>
                <p class="text-xs text-gray-500 mt-1">There are no expense or inventory costs logged for this filter criteria.</p>
                <button type="button" @click="openCreateModal()" class="mt-4 px-4 py-2 rounded-xl text-xs font-bold bg-brand-700 text-white hover:bg-brand-800 transition">
                    + Add Cost for This Date
                </button>
            </div>
        @else
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Date</th>
                            <th class="py-3 px-3">Title / Description</th>
                            <th class="py-3 px-3">Category</th>
                            <th class="py-3 px-3">Product Link</th>
                            <th class="py-3 px-3 text-right">Qty &times; Unit</th>
                            <th class="py-3 px-3 text-right">Cost Amount</th>
                            <th class="py-3 px-3">Logged By</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($dailyCosts as $cost)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                            <td class="py-3 px-3 font-mono font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $cost->cost_date->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-3">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $cost->title }}</p>
                                @if($cost->reference_no)
                                    <p class="text-[11px] font-mono text-gray-400 mt-0.5">Ref: #{{ $cost->reference_no }}</p>
                                @endif
                                @if($cost->notes)
                                    <p class="text-[11px] text-gray-500 italic mt-0.5 line-clamp-1">{{ $cost->notes }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $cost->category_badge_classes }}">
                                    {{ $cost->category_label }}
                                </span>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                @if($cost->product)
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $cost->product->name }}</span>
                                    <span class="text-[10px] font-mono text-gray-400 block">SKU: {{ $cost->product->sku }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                @if($cost->quantity && $cost->unit_cost)
                                    <span>{{ $cost->quantity }} &times; {{ $currency }}{{ number_format($cost->unit_cost, 2) }}</span>
                                @elseif($cost->quantity)
                                    <span>{{ $cost->quantity }} units</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $currency }}{{ number_format($cost->amount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-gray-500 whitespace-nowrap">
                                <span class="text-[11px]">{{ $cost->created_by ?? 'Admin' }}</span>
                            </td>
                            <td class="py-3 px-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openEditModal({{ json_encode($cost) }})"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-brand-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>

                                    <form action="{{ route('admin.daily-costs.destroy', $cost) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this cost entry?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-gray-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" title="Delete">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                {{ $dailyCosts->links() }}
            </div>
        @endif
    </div>

    {{-- Create / Edit Cost Entry Modal --}}
    <div x-cloak x-show="modalOpen" 
         @keydown.escape.window="modalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="absolute inset-0" @click="modalOpen = false"></div>

        <div class="relative w-full max-w-xl bg-white dark:bg-gray-900 rounded-3xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-800 z-10 p-6 sm:p-8"
             @click.stop>
            
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-teal-950 text-brand-700 dark:text-brand-300 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-serif text-lg font-bold text-gray-900 dark:text-white" x-text="isEdit ? 'Edit Cost Entry' : 'Record Daily Cost'"></h3>
                        <p class="text-xs text-gray-500">Record inventory purchase or business expense.</p>
                    </div>
                </div>
                <button type="button" @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm">✕</button>
            </div>

            <form :action="formAction" method="POST" class="mt-5 space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="period" value="{{ $period }}">
                @if(request('date_from')) <input type="hidden" name="date_from" value="{{ request('date_from') }}"> @endif
                @if(request('date_to')) <input type="hidden" name="date_to" value="{{ request('date_to') }}"> @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Cost Date --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="cost_date" x-model="form.cost_date" required
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select name="category" x-model="form.category" required
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                            <option value="inventory">Inventory Procurement</option>
                            <option value="packaging">Packaging Materials</option>
                            <option value="shipping_logistics">Shipping & Logistics</option>
                            <option value="operational">Operational Overhead</option>
                            <option value="marketing">Marketing & Ads</option>
                            <option value="other">Other Expense</option>
                        </select>
                    </div>
                </div>

                {{-- Title / Description --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                        Title / Description <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" x-model="form.title" placeholder="e.g. Restock 50 units Vitamin C Serum" required
                           class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </div>

                {{-- Product Link --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                        Linked Product (Optional)
                    </label>
                    <select name="product_id" x-model="form.product_id" @change="onProductSelect($event.target.value)"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                        <option value="">-- No specific product / General Cost --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-cost="{{ $p->cost_price }}">
                                {{ $p->name }} (SKU: {{ $p->sku }}) @if($p->cost_price > 0) - Cost: {{ $currency }}{{ number_format($p->cost_price, 2) }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Quantity, Unit Cost, Amount --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-gray-50 dark:bg-gray-800/40 p-3 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase mb-1">
                            Quantity (Units)
                        </label>
                        <input type="number" name="quantity" x-model="form.quantity" @input="recalcAmount()" min="1" step="1" placeholder="e.g. 50"
                               class="w-full px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-1 focus:ring-brand-600 focus:outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase mb-1">
                            Unit Cost ({{ $currency }})
                        </label>
                        <input type="number" name="unit_cost" x-model="form.unit_cost" @input="recalcAmount()" min="0" step="0.01" placeholder="e.g. 15.00"
                               class="w-full px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-1 focus:ring-brand-600 focus:outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-brand-700 dark:text-brand-300 uppercase mb-1">
                            Total Amount <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="amount" x-model="form.amount" required min="0" step="0.01" placeholder="0.00"
                               class="w-full px-2.5 py-1.5 rounded-lg border-2 border-brand-500/40 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none font-mono font-bold text-gray-900 dark:text-white">
                    </div>
                </div>

                {{-- Reference No & Notes --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                            Invoice / PO Reference #
                        </label>
                        <input type="text" name="reference_no" x-model="form.reference_no" placeholder="e.g. PO-8891 / INV-402"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                            Notes / Supplier Info
                        </label>
                        <input type="text" name="notes" x-model="form.notes" placeholder="e.g. Supplier: Apex Chem Labs"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3">
                    <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-xs font-bold bg-brand-700 hover:bg-brand-800 text-white transition shadow-sm">
                        <span x-text="isEdit ? 'Update Cost Entry' : 'Save Cost Entry'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.dailyCostsManager = function() {
    return {
        modalOpen: false,
        isEdit: false,
        formAction: '{{ route("admin.daily-costs.store") }}',
        todayStr: '{{ now()->format("Y-m-d") }}',
        form: {
            id: null,
            cost_date: '{{ now()->format("Y-m-d") }}',
            title: '',
            category: 'inventory',
            product_id: '',
            quantity: '',
            unit_cost: '',
            amount: '',
            reference_no: '',
            notes: ''
        },
        openCreateModal() {
            this.isEdit = false;
            this.formAction = '{{ route("admin.daily-costs.store") }}';
            this.form = {
                id: null,
                cost_date: this.todayStr,
                title: '',
                category: 'inventory',
                product_id: '',
                quantity: '',
                unit_cost: '',
                amount: '',
                reference_no: '',
                notes: ''
            };
            this.modalOpen = true;
        },
        openEditModal(item) {
            this.isEdit = true;
            this.formAction = '{{ url("admin/daily-costs") }}/' + item.id;
            this.form = {
                id: item.id,
                cost_date: item.cost_date ? item.cost_date.substring(0, 10) : this.todayStr,
                title: item.title,
                category: item.category || 'inventory',
                product_id: item.product_id || '',
                quantity: item.quantity || '',
                unit_cost: item.unit_cost || '',
                amount: item.amount || '',
                reference_no: item.reference_no || '',
                notes: item.notes || ''
            };
            this.modalOpen = true;
        },
        onProductSelect(productId) {
            if (!productId) return;
            const selectEl = event.target;
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            const cost = selectedOpt.getAttribute('data-cost');
            if (cost && parseFloat(cost) > 0 && !this.form.unit_cost) {
                this.form.unit_cost = parseFloat(cost).toFixed(2);
                this.recalcAmount();
            }
        },
        recalcAmount() {
            const q = parseFloat(this.form.quantity);
            const u = parseFloat(this.form.unit_cost);
            if (!isNaN(q) && q > 0 && !isNaN(u) && u >= 0) {
                this.form.amount = (q * u).toFixed(2);
            }
        }
    };
};
</script>
@endpush
