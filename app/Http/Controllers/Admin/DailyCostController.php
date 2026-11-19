<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyCost;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DailyCostController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', 'monthly'); // 'today', 'monthly', 'yearly', 'alltime', 'custom'
        $customFrom = $request->get('date_from');
        $customTo = $request->get('date_to');
        $categoryFilter = $request->get('category');
        $search = $request->get('search');

        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($period, $customFrom, $customTo);

        // 1. Calculate Sales in Date Range (Delivered & Completed orders)
        $salesQuery = Order::whereIn('status', ['delivered', 'completed']);
        if ($startDate && $endDate) {
            $salesQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $fulfilledOrders = $salesQuery->get();

        $grossSales = (float) $fulfilledOrders->sum('subtotal');
        $totalDiscounts = (float) $fulfilledOrders->sum('discount');
        $shippingCollected = (float) $fulfilledOrders->sum('shipping_cost');
        $fulfilledCount = $fulfilledOrders->count();

        // 2. Calculate Refunds in Date Range
        $refundsQuery = Order::where('status', 'refunded');
        if ($startDate && $endDate) {
            $refundsQuery->whereBetween('updated_at', [$startDate, $endDate]);
        }
        $refundedOrders = $refundsQuery->get();
        $totalRefunds = (float) $refundedOrders->sum(function ($o) {
            return $o->refund_amount > 0 ? $o->refund_amount : $o->total;
        });
        $refundedCount = $refundedOrders->count();

        // 3. Calculate Daily Costs in Date Range
        $costMetricsQuery = DailyCost::query();
        if ($startDate && $endDate) {
            $costMetricsQuery->whereDate('cost_date', '>=', $startDate->format('Y-m-d'))
                             ->whereDate('cost_date', '<=', $endDate->format('Y-m-d'));
        }
        $totalCosts = (float) (clone $costMetricsQuery)->sum('amount');
        $inventoryCosts = (float) (clone $costMetricsQuery)->where('category', 'inventory')->sum('amount');
        $packagingCosts = (float) (clone $costMetricsQuery)->where('category', 'packaging')->sum('amount');
        $shippingLogisticsCosts = (float) (clone $costMetricsQuery)->where('category', 'shipping_logistics')->sum('amount');
        $operationalCosts = (float) (clone $costMetricsQuery)->where('category', 'operational')->sum('amount');
        $otherCosts = (float) (clone $costMetricsQuery)->whereNotIn('category', ['inventory', 'packaging', 'shipping_logistics', 'operational'])->sum('amount');
        $costsCount = (clone $costMetricsQuery)->count();

        // 4. Simple Profit & Loss Calculations
        // Net Sales = Gross Sales - Discounts - Refunds
        $netSales = max(0, $grossSales - $totalDiscounts - $totalRefunds);
        // Net Profit = (Net Sales + Shipping Collected) - Total Daily Costs
        $netProfit = ($netSales + $shippingCollected) - $totalCosts;
        $effectiveBase = ($grossSales > 0) ? $grossSales : abs($netProfit);
        $profitMargin = $effectiveBase > 0 ? ($netProfit / $effectiveBase) * 100 : 0.0;

        // 5. Query Daily Costs List for the Table
        $tableQuery = DailyCost::with('product');
        if ($startDate && $endDate) {
            $tableQuery->whereDate('cost_date', '>=', $startDate->format('Y-m-d'))
                       ->whereDate('cost_date', '<=', $endDate->format('Y-m-d'));
        }
        if ($categoryFilter) {
            $tableQuery->where('category', $categoryFilter);
        }
        if ($search) {
            $tableQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }
        $dailyCosts = $tableQuery->orderByDesc('cost_date')->orderByDesc('id')->paginate(15)->withQueryString();

        // 6. Day-by-Day Simple Timeline for the Period
        $dailyTimeline = $this->buildDailyTimeline($startDate, $endDate, $fulfilledOrders, $refundedOrders, $costMetricsQuery->get());

        // Products for dropdown in cost entry modal
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'cost_price', 'price']);

        $summary = [
            'gross_sales'        => $grossSales,
            'total_discounts'    => $totalDiscounts,
            'shipping_collected' => $shippingCollected,
            'total_refunds'      => $totalRefunds,
            'net_sales'          => $netSales,
            'total_costs'        => $totalCosts,
            'inventory_costs'    => $inventoryCosts,
            'packaging_costs'    => $packagingCosts,
            'shipping_costs'     => $shippingLogisticsCosts,
            'operational_costs'  => $operationalCosts,
            'other_costs'        => $otherCosts,
            'net_profit'         => $netProfit,
            'profit_margin'      => $profitMargin,
            'fulfilled_count'    => $fulfilledCount,
            'refunded_count'     => $refundedCount,
            'costs_count'        => $costsCount,
        ];

        return view('admin.daily-costs.index', compact(
            'period',
            'periodLabel',
            'startDate',
            'endDate',
            'summary',
            'dailyCosts',
            'dailyTimeline',
            'products',
            'categoryFilter',
            'search'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cost_date'    => ['required', 'date'],
            'title'        => ['required', 'string', 'max:255'],
            'category'     => ['required', 'string', 'in:inventory,packaging,shipping_logistics,operational,marketing,other'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'product_id'   => ['nullable', 'exists:products,id'],
            'quantity'     => ['nullable', 'integer', 'min:1'],
            'unit_cost'    => ['nullable', 'numeric', 'min:0'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes'        => ['nullable', 'string', 'max:2000'],
        ]);

        $data['created_by'] = auth('admin')->check() ? auth('admin')->user()->name : 'Admin';

        // Auto-calculate amount if quantity & unit cost supplied and amount was 0
        if (!empty($data['quantity']) && !empty($data['unit_cost']) && ((float) $data['amount'] <= 0)) {
            $data['amount'] = (float) $data['quantity'] * (float) $data['unit_cost'];
        }

        DailyCost::create($data);

        return redirect()->route('admin.daily-costs.index', $request->only(['period', 'date_from', 'date_to']))
            ->with('success', 'Daily cost entry recorded successfully.');
    }

    public function update(Request $request, DailyCost $dailyCost): RedirectResponse
    {
        $data = $request->validate([
            'cost_date'    => ['required', 'date'],
            'title'        => ['required', 'string', 'max:255'],
            'category'     => ['required', 'string', 'in:inventory,packaging,shipping_logistics,operational,marketing,other'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'product_id'   => ['nullable', 'exists:products,id'],
            'quantity'     => ['nullable', 'integer', 'min:1'],
            'unit_cost'    => ['nullable', 'numeric', 'min:0'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes'        => ['nullable', 'string', 'max:2000'],
        ]);

        if (!empty($data['quantity']) && !empty($data['unit_cost']) && ((float) $data['amount'] <= 0)) {
            $data['amount'] = (float) $data['quantity'] * (float) $data['unit_cost'];
        }

        $dailyCost->update($data);

        return redirect()->route('admin.daily-costs.index', $request->only(['period', 'date_from', 'date_to']))
            ->with('success', 'Daily cost entry updated successfully.');
    }

    public function destroy(DailyCost $dailyCost): RedirectResponse
    {
        $dailyCost->delete();

        return back()->with('success', 'Cost entry deleted successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $period = $request->get('period', 'monthly');
        $customFrom = $request->get('date_from');
        $customTo = $request->get('date_to');

        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($period, $customFrom, $customTo);

        // Fetch costs
        $costsQuery = DailyCost::with('product');
        if ($startDate && $endDate) {
            $costsQuery->whereDate('cost_date', '>=', $startDate->format('Y-m-d'))
                       ->whereDate('cost_date', '<=', $endDate->format('Y-m-d'));
        }
        $costs = $costsQuery->orderBy('cost_date')->get();

        // Calculate sales & refunds
        $salesQuery = Order::whereIn('status', ['delivered', 'completed']);
        if ($startDate && $endDate) {
            $salesQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $grossSales = (float) $salesQuery->sum('subtotal');
        $discounts = (float) $salesQuery->sum('discount');

        $refundsQuery = Order::where('status', 'refunded');
        if ($startDate && $endDate) {
            $refundsQuery->whereBetween('updated_at', [$startDate, $endDate]);
        }
        $totalRefunds = (float) $refundsQuery->get()->sum(fn($o) => $o->refund_amount > 0 ? $o->refund_amount : $o->total);

        $totalCosts = (float) $costs->sum('amount');
        $netSales = max(0, $grossSales - $discounts - $totalRefunds);
        $netProfit = $netSales - $totalCosts;

        $filename = 'daily-costs-pl-report-' . str_replace(' ', '-', strtolower($periodLabel)) . '-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($costs, $periodLabel, $grossSales, $totalRefunds, $totalCosts, $netProfit) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ["DAILY COSTS & PROFIT/LOSS REPORT - {$periodLabel}"]);
            fputcsv($handle, ['Exported at: ' . now()->toDateTimeString()]);
            fputcsv($handle, []);

            // Summary Block
            fputcsv($handle, ['FINANCIAL SUMMARY', 'AMOUNT']);
            fputcsv($handle, ['Gross Product Sales', number_format($grossSales, 2)]);
            fputcsv($handle, ['Refunds & Returns Loss', '-' . number_format($totalRefunds, 2)]);
            fputcsv($handle, ['Total Recorded Costs', '-' . number_format($totalCosts, 2)]);
            fputcsv($handle, ['Net Profit / (Loss)', number_format($netProfit, 2)]);
            fputcsv($handle, []);

            // Details Table
            fputcsv($handle, ['DATE', 'TITLE', 'CATEGORY', 'PRODUCT', 'QUANTITY', 'UNIT COST', 'AMOUNT', 'REFERENCE NO', 'NOTES', 'RECORDED BY']);

            foreach ($costs as $c) {
                fputcsv($handle, [
                    $c->cost_date->format('Y-m-d'),
                    $c->title,
                    $c->category_label,
                    $c->product ? $c->product->name . ' (' . $c->product->sku . ')' : 'General',
                    $c->quantity ?? '',
                    $c->unit_cost ? number_format($c->unit_cost, 2) : '',
                    number_format($c->amount, 2),
                    $c->reference_no ?? '',
                    $c->notes ?? '',
                    $c->created_by ?? 'Admin',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
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
                $label = 'This Year (' . $now->format('Y') . ')';
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
                $label = 'This Month (' . $now->format('F Y') . ')';
                break;
        }

        return [$start, $end, $label];
    }

    private function buildDailyTimeline(?Carbon $startDate, ?Carbon $endDate, $fulfilledOrders, $refundedOrders, $costs): array
    {
        $days = [];

        // Group sales by date (Y-m-d)
        foreach ($fulfilledOrders as $o) {
            $d = $o->created_at->format('Y-m-d');
            $days[$d]['sales'] = ($days[$d]['sales'] ?? 0) + (float) $o->subtotal;
        }

        // Group refunds by date (Y-m-d)
        foreach ($refundedOrders as $r) {
            $d = $r->updated_at->format('Y-m-d');
            $refAmt = $r->refund_amount > 0 ? (float) $r->refund_amount : (float) $r->total;
            $days[$d]['refunds'] = ($days[$d]['refunds'] ?? 0) + $refAmt;
        }

        // Group daily costs by date (Y-m-d)
        foreach ($costs as $c) {
            $d = $c->cost_date->format('Y-m-d');
            $days[$d]['costs'] = ($days[$d]['costs'] ?? 0) + (float) $c->amount;
        }

        krsort($days);

        $timeline = [];
        foreach ($days as $dateStr => $val) {
            $s = $val['sales'] ?? 0.0;
            $r = $val['refunds'] ?? 0.0;
            $c = $val['costs'] ?? 0.0;
            $net = ($s - $r) - $c;

            $timeline[] = [
                'date'    => Carbon::parse($dateStr)->format('M d, Y (D)'),
                'raw_date'=> $dateStr,
                'sales'   => $s,
                'refunds' => $r,
                'costs'   => $c,
                'net'     => $net,
            ];
        }

        return $timeline;
    }
}
