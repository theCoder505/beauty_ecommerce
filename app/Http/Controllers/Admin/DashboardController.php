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
        $stats               = $this->analytics->getDashboardStats();
        $recentOrders        = $this->analytics->recentOrders();
        $lowStockProducts    = $this->analytics->lowStockProducts();
        $topSellingProducts  = $this->analytics->topSellingProducts();
        $mostViewedProducts  = $this->analytics->mostViewedProducts();
        $mostReviewedProducts = $this->analytics->mostReviewedProducts();

        // Aliases for view backwards-compatibility
        $lowStock     = $lowStockProducts;
        $topSelling   = $topSellingProducts;
        $mostViewed   = $mostViewedProducts;
        $mostReviewed = $mostReviewedProducts;

        return view('admin.dashboard', compact(
            'stats',
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
