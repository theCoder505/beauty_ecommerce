<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
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
        $visitorLogs          = $this->analytics->getVisitorLogs(15);
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
}
