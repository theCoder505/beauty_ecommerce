<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function profitLoss(Request $request): View
    {
        $period = $request->get('period', 'monthly'); // 'today', 'monthly', 'yearly', 'alltime', 'custom'
        $customFrom = $request->get('date_from');
        $customTo = $request->get('date_to');

        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($period, $customFrom, $customTo);

        $report = $this->calculateProfitLoss($startDate, $endDate);

        return view('admin.reports.profit-loss', [
            'period'      => $period,
            'periodLabel' => $periodLabel,
            'startDate'   => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate'     => $endDate ? $endDate->format('Y-m-d') : null,
            'report'      => $report,
        ]);
    }

    public function exportProfitLoss(Request $request): StreamedResponse
    {
        $period = $request->get('period', 'monthly');
        $customFrom = $request->get('date_from');
        $customTo = $request->get('date_to');

        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($period, $customFrom, $customTo);
        $report = $this->calculateProfitLoss($startDate, $endDate);

        $filename = 'profit-loss-report-' . str_replace(' ', '-', strtolower($periodLabel)) . '-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($report, $periodLabel) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ["PROFIT & LOSS FINANCIAL REPORT - {$periodLabel}"]);
            fputcsv($handle, ['Generated on: ' . now()->toDateTimeString()]);
            fputcsv($handle, []);

            fputcsv($handle, ['METRIC SUMMARY', 'AMOUNT']);
            fputcsv($handle, ['Gross Product Sales', number_format($report['gross_sales'], 2)]);
            fputcsv($handle, ['Promotional Discounts (Coupons)', '-' . number_format($report['total_discounts'], 2)]);
            fputcsv($handle, ['Refunds Payout (Customer Loss)', '-' . number_format($report['total_refunds'], 2)]);
            fputcsv($handle, ['Net Sales Revenue', number_format($report['net_sales'], 2)]);
            fputcsv($handle, ['Cost of Goods Sold (COGS)', '-' . number_format($report['total_cogs'], 2)]);
            fputcsv($handle, ['Gross Profit', number_format($report['gross_profit'], 2)]);
            fputcsv($handle, ['Shipping Fees Collected', number_format($report['shipping_collected'], 2)]);
            fputcsv($handle, ['Net Profit / (Loss)', number_format($report['net_profit'], 2)]);
            fputcsv($handle, ['Net Profit Margin %', number_format($report['profit_margin'], 2) . '%']);
            fputcsv($handle, []);

            fputcsv($handle, ['PRODUCT PROFITABILITY BREAKDOWN']);
            fputcsv($handle, ['Product Name', 'SKU', 'Units Sold', 'Avg Selling Price', 'Unit Cost Price', 'Gross Sales', 'COGS', 'Units Refunded', 'Refund Loss', 'Net Profit', 'Margin %']);

            foreach ($report['product_breakdown'] as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['sku'],
                    $row['units_sold'],
                    number_format($row['avg_price'], 2),
                    number_format($row['cost_price'], 2),
                    number_format($row['gross_sales'], 2),
                    number_format($row['cogs'], 2),
                    $row['units_refunded'],
                    number_format($row['refund_loss'], 2),
                    number_format($row['net_profit'], 2),
                    number_format($row['margin'], 2) . '%',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function resolveDateRange(string $period, ?string $customFrom = null, ?string $customTo = null): array
    {
        $now = now();

        switch ($period) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = 'Today (' . $now->format('M j, Y') . ')';
                break;

            case 'yearly':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $label = 'Year ' . $now->format('Y');
                break;

            case 'alltime':
                $start = null;
                $end = null;
                $label = 'All Time';
                break;

            case 'custom':
                $start = $customFrom ? Carbon::parse($customFrom)->startOfDay() : $now->copy()->startOfMonth();
                $end = $customTo ? Carbon::parse($customTo)->endOfDay() : $now->copy()->endOfDay();
                $label = $start->format('M j, Y') . ' — ' . $end->format('M j, Y');
                break;

            case 'monthly':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $label = $now->format('F Y');
                break;
        }

        return [$start, $end, $label];
    }

    private function calculateProfitLoss(?Carbon $startDate, ?Carbon $endDate): array
    {
        // 1. Fulfilled orders (delivered, completed)
        $fulfilledQuery = Order::whereIn('status', ['delivered', 'completed']);
        if ($startDate && $endDate) {
            $fulfilledQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $fulfilledOrders = $fulfilledQuery->with(['items.product'])->get();

        $fulfilledCount = $fulfilledOrders->count();
        $grossSales = (float) $fulfilledOrders->sum('subtotal');
        $totalDiscounts = (float) $fulfilledOrders->sum('discount');
        $shippingCollected = (float) $fulfilledOrders->sum('shipping_cost');

        // Calculate COGS for fulfilled items
        $totalCogs = 0.0;
        $productStats = [];

        foreach ($fulfilledOrders as $order) {
            foreach ($order->items as $item) {
                $pid = $item->product_id;
                $costPrice = (float) ($item->product?->cost_price ?? 0);
                $itemTotalCost = $item->quantity * $costPrice;
                $totalCogs += $itemTotalCost;

                if (!isset($productStats[$pid])) {
                    $productStats[$pid] = [
                        'name'           => $item->product_name,
                        'sku'            => $item->product_sku ?? ($item->product?->sku ?? 'N/A'),
                        'cost_price'     => $costPrice,
                        'units_sold'     => 0,
                        'gross_sales'    => 0.0,
                        'cogs'           => 0.0,
                        'units_refunded' => 0,
                        'refund_loss'    => 0.0,
                    ];
                }

                $productStats[$pid]['units_sold'] += $item->quantity;
                $productStats[$pid]['gross_sales'] += (float) $item->total;
                $productStats[$pid]['cogs'] += $itemTotalCost;
            }
        }

        // 2. Refunded Orders & Refund Losses (Req 4: "there will be the refunded goods a loss")
        $refundedQuery = Order::where('status', 'refunded');
        if ($startDate && $endDate) {
            $refundedQuery->whereBetween('updated_at', [$startDate, $endDate]);
        }
        $refundedOrders = $refundedQuery->with(['items.product'])->get();

        $refundedCount = $refundedOrders->count();
        $totalRefunds = 0.0;
        $refundedGoodsCostLoss = 0.0;

        foreach ($refundedOrders as $refundOrder) {
            $refundAmount = (float) $refundOrder->refund_amount > 0 ? (float) $refundOrder->refund_amount : (float) $refundOrder->total;
            $totalRefunds += $refundAmount;

            foreach ($refundOrder->items as $rItem) {
                $pid = $rItem->product_id;
                $rCost = (float) ($rItem->product?->cost_price ?? 0);
                $rItemLoss = (float) $rItem->total;
                $refundedGoodsCostLoss += ($rItem->quantity * $rCost);

                if (!isset($productStats[$pid])) {
                    $productStats[$pid] = [
                        'name'           => $rItem->product_name,
                        'sku'            => $rItem->product_sku ?? ($rItem->product?->sku ?? 'N/A'),
                        'cost_price'     => $rCost,
                        'units_sold'     => 0,
                        'gross_sales'    => 0.0,
                        'cogs'           => 0.0,
                        'units_refunded' => 0,
                        'refund_loss'    => 0.0,
                    ];
                }

                $productStats[$pid]['units_refunded'] += $rItem->quantity;
                $productStats[$pid]['refund_loss'] += $rItemLoss;
            }
        }

        // 3. Returned Orders (physical returns)
        $returnedQuery = Order::where('status', 'returned');
        if ($startDate && $endDate) {
            $returnedQuery->whereBetween('updated_at', [$startDate, $endDate]);
        }
        $returnedCount = $returnedQuery->count();

        // 4. Financial Calculations
        // Net Sales = Gross Sales - Discounts - Refunds
        $netSales = max(-999999, $grossSales - $totalDiscounts - $totalRefunds);

        // Gross Profit = Net Sales - COGS
        $grossProfit = $netSales - $totalCogs;

        // Net Profit = Gross Profit + Shipping balance
        $netProfit = $grossProfit + $shippingCollected;

        $effectiveBase = ($grossSales > 0) ? $grossSales : abs($netProfit);
        $profitMargin = $effectiveBase > 0 ? ($netProfit / $effectiveBase) * 100 : 0.0;

        // Compute Product Breakdown Net Profit & Margins
        $productBreakdown = [];
        foreach ($productStats as $pid => $data) {
            $prodNetSales = $data['gross_sales'] - $data['refund_loss'];
            $prodProfit = $prodNetSales - $data['cogs'];
            $avgPrice = $data['units_sold'] > 0 ? ($data['gross_sales'] / $data['units_sold']) : 0.0;
            $margin = $data['gross_sales'] > 0 ? ($prodProfit / $data['gross_sales']) * 100 : 0.0;

            $productBreakdown[] = array_merge($data, [
                'id'         => $pid,
                'avg_price'  => $avgPrice,
                'net_sales'  => $prodNetSales,
                'net_profit' => $prodProfit,
                'margin'     => $margin,
            ]);
        }

        // Sort products by net profit descending
        usort($productBreakdown, fn($a, $b) => $b['net_profit'] <=> $a['net_profit']);

        // Timeline intervals for chart/table
        $timeline = $this->buildTimeline($startDate, $endDate, $fulfilledOrders, $refundedOrders);

        // Daily Recorded Costs for this period
        $dailyCostsQuery = \App\Models\DailyCost::query();
        if ($startDate && $endDate) {
            $dailyCostsQuery->whereBetween('cost_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }
        $totalDailyCosts = (float) (clone $dailyCostsQuery)->sum('amount');
        $inventoryProcurementCosts = (float) (clone $dailyCostsQuery)->where('category', 'inventory')->sum('amount');

        return [
            'gross_sales'                => $grossSales,
            'total_discounts'            => $totalDiscounts,
            'shipping_collected'         => $shippingCollected,
            'total_cogs'                 => $totalCogs,
            'total_refunds'              => $totalRefunds,
            'refunded_goods_cost_loss'   => $refundedGoodsCostLoss,
            'total_daily_costs'          => $totalDailyCosts,
            'inventory_procurement_costs'=> $inventoryProcurementCosts,
            'net_sales'                  => $netSales,
            'gross_profit'               => $grossProfit,
            'net_profit'                 => $netProfit,
            'profit_margin'              => $profitMargin,
            'fulfilled_orders_count'     => $fulfilledCount,
            'refunded_orders_count'      => $refundedCount,
            'returned_orders_count'      => $returnedCount,
            'product_breakdown'          => $productBreakdown,
            'timeline'                   => $timeline,
        ];
    }

    private function buildTimeline(?Carbon $startDate, ?Carbon $endDate, $fulfilledOrders, $refundedOrders): array
    {
        $timeline = [];

        if (!$startDate || !$endDate) {
            // All-time: group by year
            $years = [];
            foreach ($fulfilledOrders as $o) {
                $y = $o->created_at->format('Y');
                $years[$y]['sales'] = ($years[$y]['sales'] ?? 0) + (float) $o->subtotal;
            }
            foreach ($refundedOrders as $r) {
                $y = $r->updated_at->format('Y');
                $years[$y]['refunds'] = ($years[$y]['refunds'] ?? 0) + (float) ($r->refund_amount ?: $r->total);
            }
            ksort($years);
            foreach ($years as $label => $data) {
                $sales = $data['sales'] ?? 0;
                $refunds = $data['refunds'] ?? 0;
                $timeline[] = [
                    'label'   => $label,
                    'sales'   => $sales,
                    'refunds' => $refunds,
                    'net'     => $sales - $refunds,
                ];
            }
            return $timeline;
        }

        $diffDays = $startDate->diffInDays($endDate);

        if ($diffDays <= 1) {
            // Hourly for Today
            for ($h = 0; $h < 24; $h++) {
                $hLabel = sprintf('%02d:00', $h);
                $sales = $fulfilledOrders->filter(fn($o) => (int)$o->created_at->format('H') === $h)->sum('subtotal');
                $refunds = $refundedOrders->filter(fn($r) => (int)$r->updated_at->format('H') === $h)->sum(fn($r) => $r->refund_amount ?: $r->total);
                $timeline[] = [
                    'label'   => $hLabel,
                    'sales'   => (float) $sales,
                    'refunds' => (float) $refunds,
                    'net'     => (float) ($sales - $refunds),
                ];
            }
        } elseif ($diffDays <= 31) {
            // Daily for Current Month
            $cursor = $startDate->copy();
            while ($cursor <= $endDate) {
                $dKey = $cursor->format('Y-m-d');
                $dLabel = $cursor->format('M j');
                $sales = $fulfilledOrders->filter(fn($o) => $o->created_at->format('Y-m-d') === $dKey)->sum('subtotal');
                $refunds = $refundedOrders->filter(fn($r) => $r->updated_at->format('Y-m-d') === $dKey)->sum(fn($r) => $r->refund_amount ?: $r->total);
                $timeline[] = [
                    'label'   => $dLabel,
                    'sales'   => (float) $sales,
                    'refunds' => (float) $refunds,
                    'net'     => (float) ($sales - $refunds),
                ];
                $cursor->addDay();
            }
        } else {
            // Monthly for Current Year
            $cursor = $startDate->copy()->startOfMonth();
            while ($cursor <= $endDate) {
                $mKey = $cursor->format('Y-m');
                $mLabel = $cursor->format('M Y');
                $sales = $fulfilledOrders->filter(fn($o) => $o->created_at->format('Y-m') === $mKey)->sum('subtotal');
                $refunds = $refundedOrders->filter(fn($r) => $r->updated_at->format('Y-m') === $mKey)->sum(fn($r) => $r->refund_amount ?: $r->total);
                $timeline[] = [
                    'label'   => $mLabel,
                    'sales'   => (float) $sales,
                    'refunds' => (float) $refunds,
                    'net'     => (float) ($sales - $refunds),
                ];
                $cursor->addMonth();
            }
        }

        return $timeline;
    }
}
