@extends('admin.layouts.app')
@section('title', 'Profit & Loss Financial Report')
@php
    $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳');
@endphp

@section('content')
<div class="space-y-6">
    {{-- Header & Period Selector --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <span>Reports</span>
                <span>/</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">Financial Reports</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>Profit & Loss Report (P&L)</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-brand-50 text-brand-700 dark:bg-teal-950 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                    {{ $periodLabel }}
                </span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">Real-time revenue, cost of goods, refund losses, and net margin calculation.</p>
        </div>

        {{-- Actions: Export & Print --}}
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.daily-costs.index', ['period' => $period]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 transition shadow-2xs">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Daily Costs & Inventory</span>
            </a>
            <a href="{{ route('admin.reports.profit-loss.export', ['period' => $period, 'date_from' => $startDate, 'date_to' => $endDate]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export CSV</span>
            </a>
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition shadow-2xs">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Report</span>
            </button>
        </div>
    </div>

    {{-- Filter Nav (Today / Monthly / Yearly / All-time / Custom Range) --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        {{-- Quick Period Pills --}}
        <div class="flex items-center gap-1 overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('admin.reports.profit-loss', ['period' => 'today']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ $period === 'today' ? 'bg-brand-700 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Today
            </a>
            <a href="{{ route('admin.reports.profit-loss', ['period' => 'monthly']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ $period === 'monthly' ? 'bg-brand-700 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Monthly
            </a>
            <a href="{{ route('admin.reports.profit-loss', ['period' => 'yearly']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ $period === 'yearly' ? 'bg-brand-700 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Yearly
            </a>
            <a href="{{ route('admin.reports.profit-loss', ['period' => 'alltime']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ $period === 'alltime' ? 'bg-brand-700 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                All Time
            </a>
        </div>

        {{-- Custom Date Form --}}
        <form action="{{ route('admin.reports.profit-loss') }}" method="GET" class="flex items-center gap-2 flex-wrap">
            <input type="hidden" name="period" value="custom">
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <span>From</span>
                <input type="date" name="date_from" value="{{ $startDate ?? now()->startOfMonth()->toDateString() }}"
                       class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs">
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <span>To</span>
                <input type="date" name="date_to" value="{{ $endDate ?? now()->toDateString() }}"
                       class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs">
            </div>
            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold transition">
                Apply
            </button>
        </form>
    </div>

    {{-- Top KPI Financial Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        {{-- Gross Product Sales --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 shadow-xs">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Gross Sales</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $currency }}{{ number_format($report['gross_sales'], 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $report['fulfilled_orders_count'] }} fulfilled orders</p>
        </div>

        {{-- Refunds (Loss) --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-rose-300 dark:border-rose-900/60 bg-rose-50/20 dark:bg-rose-950/10 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-rose-700 dark:text-rose-300 uppercase tracking-wide">Refunds (Loss)</p>
                <span class="text-[10px] px-1.5 py-0.5 rounded font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-200">-LOSS</span>
            </div>
            <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-2">-{{ $currency }}{{ number_format($report['total_refunds'], 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $report['refunded_orders_count'] }} refunded orders</p>
        </div>

        {{-- COGS (Cost of Goods Sold) --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 shadow-xs">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">COGS (Product Cost)</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $currency }}{{ number_format($report['total_cogs'], 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">Cost of fulfilled inventory</p>
        </div>

        {{-- Net Profit / Loss --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border {{ $report['net_profit'] >= 0 ? 'border-emerald-300 dark:border-emerald-800 bg-emerald-50/20 dark:bg-emerald-950/10' : 'border-red-400 dark:border-red-800 bg-red-50/20 dark:bg-red-950/10' }} shadow-xs">
            <p class="text-xs font-bold {{ $report['net_profit'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }} uppercase tracking-wide">
                {{ $report['net_profit'] >= 0 ? 'Net Profit' : 'Net Loss' }}
            </p>
            <p class="text-2xl font-bold {{ $report['net_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} mt-2">
                {{ $report['net_profit'] >= 0 ? '' : '-' }}{{ $currency }}{{ number_format(abs($report['net_profit']), 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">After deducting refunds & COGS</p>
        </div>

        {{-- Profit Margin % --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 shadow-xs">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Net Margin</p>
            <p class="text-2xl font-bold {{ $report['profit_margin'] >= 0 ? 'text-brand-700 dark:text-brand-400' : 'text-red-500' }} mt-2">
                {{ number_format($report['profit_margin'], 1) }}%
            </p>
            <p class="text-xs text-gray-400 mt-1">Profitability ratio</p>
        </div>
    </div>

    {{-- Main Financial Breakdown: Income Statement --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Formal Income Statement --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 mb-4">
                <div>
                    <h3 class="font-serif text-base font-bold text-gray-900 dark:text-white">Financial Statement Breakdown</h3>
                    <p class="text-xs text-gray-500">Period: {{ $periodLabel }}</p>
                </div>
                <span class="text-xs text-gray-400 font-mono">Currency: {{ $currency }}</span>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                {{-- Gross Revenue Section --}}
                <div class="py-3 space-y-2">
                    <div class="flex justify-between font-semibold text-gray-900 dark:text-white">
                        <span>Gross Product Sales</span>
                        <span>{{ $currency }}{{ number_format($report['gross_sales'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 pl-4">
                        <span>Less: Promotional Discounts & Coupons</span>
                        <span class="text-rose-600">-{{ $currency }}{{ number_format($report['total_discounts'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 pl-4 font-semibold">
                        <span class="flex items-center gap-1.5 text-rose-700 dark:text-rose-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            <span>Less: Refunded Goods & Customer Refunds (Loss)</span>
                        </span>
                        <span class="text-rose-600 font-bold">-{{ $currency }}{{ number_format($report['total_refunds'], 2) }}</span>
                    </div>
                </div>

                {{-- Net Revenue Subtotal --}}
                <div class="py-3 flex justify-between font-bold text-gray-900 dark:text-white bg-gray-50/50 dark:bg-gray-800/20 px-3 rounded-xl">
                    <span>Net Sales Revenue</span>
                    <span class="{{ $report['net_sales'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-red-500' }}">
                        {{ $currency }}{{ number_format($report['net_sales'], 2) }}
                    </span>
                </div>

                {{-- Cost of Sales Section --}}
                <div class="py-3 space-y-2">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Cost of Goods Sold (COGS)</span>
                        <span class="text-gray-900 dark:text-white">-{{ $currency }}{{ number_format($report['total_cogs'], 2) }}</span>
                    </div>
                    <p class="text-[11px] text-gray-400 italic">Total acquisition cost of fulfilled units based on product buying price.</p>
                </div>

                {{-- Gross Profit Subtotal --}}
                <div class="py-3 flex justify-between font-bold text-gray-900 dark:text-white bg-gray-50/50 dark:bg-gray-800/20 px-3 rounded-xl">
                    <span>Gross Profit</span>
                    <span class="{{ $report['gross_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $currency }}{{ number_format($report['gross_profit'], 2) }}
                    </span>
                </div>

                {{-- Shipping and Logistics --}}
                <div class="py-3 space-y-2">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400 text-xs">
                        <span>Shipping Fees Collected from Customers</span>
                        <span class="text-emerald-600">+{{ $currency }}{{ number_format($report['shipping_collected'], 2) }}</span>
                    </div>
                </div>

                {{-- Final Net Profit --}}
                <div class="py-4 flex justify-between font-bold text-lg {{ $report['net_profit'] >= 0 ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60' : 'text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/60' }} p-4 rounded-2xl">
                    <span>Final Net Profit / (Loss)</span>
                    <span>{{ $report['net_profit'] >= 0 ? '' : '-' }}{{ $currency }}{{ number_format(abs($report['net_profit']), 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Period Timeline Trend Chart / Summary --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm space-y-4">
            <h3 class="font-serif text-base font-bold text-gray-900 dark:text-white">Period Breakdown</h3>
            <p class="text-xs text-gray-500">Sales vs. Refund Deductions over time.</p>

            <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                @forelse($report['timeline'] as $slot)
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs space-y-1.5">
                        <div class="flex justify-between font-bold text-gray-900 dark:text-white">
                            <span>{{ $slot['label'] }}</span>
                            <span class="{{ $slot['net'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                Net: {{ $currency }}{{ number_format($slot['net'], 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-[11px] text-gray-500">
                            <span>Sales: {{ $currency }}{{ number_format($slot['sales'], 2) }}</span>
                            @if($slot['refunds'] > 0)
                                <span class="text-rose-600 font-semibold">Refund Loss: -{{ $currency }}{{ number_format($slot['refunds'], 2) }}</span>
                            @else
                                <span class="text-gray-400">Refunds: 0</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-6">No interval records for this period.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Product Profitability & Refund Loss Breakdown Table --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
            <div>
                <h3 class="font-serif text-base font-bold text-gray-900 dark:text-white">Product-by-Product Profit & Loss Breakdown</h3>
                <p class="text-xs text-gray-500">Inspect each product's unit sales, buying cost, refund losses, and net contribution.</p>
            </div>
            <span class="text-xs text-gray-400">{{ count($report['product_breakdown']) }} active products analyzed</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="py-3 px-4">Product</th>
                        <th class="py-3 px-3 text-center">Units Sold</th>
                        <th class="py-3 px-3 text-right">Avg Price</th>
                        <th class="py-3 px-3 text-right">Cost Price</th>
                        <th class="py-3 px-4 text-right">Gross Sales</th>
                        <th class="py-3 px-4 text-right">COGS</th>
                        <th class="py-3 px-3 text-center">Refunded Units</th>
                        <th class="py-3 px-4 text-right">Refund Loss</th>
                        <th class="py-3 px-4 text-right">Product Profit</th>
                        <th class="py-3 px-3 text-right">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($report['product_breakdown'] as $p)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        <td class="py-3 px-4">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $p['name'] }}</p>
                            <p class="text-[10px] text-gray-400 font-mono">SKU: {{ $p['sku'] }}</p>
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-gray-800 dark:text-gray-200">{{ $p['units_sold'] }}</td>
                        <td class="py-3 px-3 text-right font-mono">{{ $currency }}{{ number_format($p['avg_price'], 2) }}</td>
                        <td class="py-3 px-3 text-right font-mono text-gray-500">
                            {{ $p['cost_price'] > 0 ? $currency . number_format($p['cost_price'], 2) : '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-gray-900 dark:text-white">{{ $currency }}{{ number_format($p['gross_sales'], 2) }}</td>
                        <td class="py-3 px-4 text-right text-gray-600 dark:text-gray-400">{{ $currency }}{{ number_format($p['cogs'], 2) }}</td>
                        <td class="py-3 px-3 text-center">
                            @if($p['units_refunded'] > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">
                                    {{ $p['units_refunded'] }}
                                </span>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-semibold {{ $p['refund_loss'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">
                            {{ $p['refund_loss'] > 0 ? '-' . $currency . number_format($p['refund_loss'], 2) : '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold {{ $p['net_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                            {{ $currency }}{{ number_format($p['net_profit'], 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold {{ $p['margin'] >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-red-500' }}">
                            {{ number_format($p['margin'], 1) }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-8 text-gray-400">
                            No product sales or refunds recorded for this period.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
