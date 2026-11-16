<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteVisit;
use App\Services\AnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function index(): View
    {
        $stats                = $this->analytics->getDashboardStats();
        $orderCharts          = $this->analytics->getOrderChartData();
        $countryStats         = $this->analytics->getCountryVisitorStats();
        $deviceStats          = $this->analytics->getDeviceStats();
        $visitorLogs          = $this->analytics->getVisitorLogs(); // All logs for client-side jQuery DataTable pagination
        $recentOrders         = $this->analytics->recentOrders(8);
        $lowStockProducts     = $this->analytics->lowStockProducts(10);
        $topSellingProducts   = $this->analytics->topSellingProducts(6);
        $mostViewedProducts   = $this->analytics->mostViewedProducts(6);
        $mostReviewedProducts = $this->analytics->mostReviewedProducts(6);

        // Aliases for view backwards-compatibility
        $lowStock     = $lowStockProducts;
        $topSelling   = $topSellingProducts;
        $mostViewed   = $mostViewedProducts;
        $mostReviewed = $mostReviewedProducts;

        return view('admin.dashboard', compact(
            'stats',
            'orderCharts',
            'countryStats',
            'deviceStats',
            'visitorLogs',
            'recentOrders',
            'lowStockProducts',
            'topSellingProducts',
            'mostViewedProducts',
            'mostReviewedProducts',
            'lowStock',
            'topSelling',
            'mostViewed',
            'mostReviewed',
        ));
    }

    public function destroyVisitorLog(Request $request, SiteVisit $visit): mixed
    {
        $visit->delete();

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Visitor log removed successfully.',
            ]);
        }

        return back()->with('success', 'Visitor log removed successfully.');
    }

    public function bulkDeleteVisitorLogs(Request $request): mixed
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:site_visits,id',
        ]);

        $count = SiteVisit::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'count'   => $count,
                'message' => "{$count} " . ($count === 1 ? 'visitor log' : 'visitor logs') . ' deleted successfully.',
            ]);
        }

        return back()->with('success', "{$count} " . ($count === 1 ? 'visitor log' : 'visitor logs') . ' deleted successfully.');
    }
}
