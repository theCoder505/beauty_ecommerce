<?php

namespace App\Services;

use App\Models\ProductView;
use App\Models\SiteVisit;
use App\Models\Product;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function trackProductView(int $productId): void
    {
        $sessionId = session()->getId();
        $today     = now()->toDateString();

        try {
            $inserted = DB::table('product_views')->insertOrIgnore([
                'product_id'  => $productId,
                'session_id'  => $sessionId,
                'ip_address'  => request()->ip(),
                'viewed_date' => $today,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            if ($inserted) {
                Product::where('id', $productId)->increment('views_count');
            }
        } catch (\Exception) {
            // Non-fatal
        }
    }

    public function trackVisit(): void
    {
        $sessionId = session()->getId();
        $today     = now()->toDateString();

        try {
            DB::table('site_visits')->insertOrIgnore([
                'session_id'   => $sessionId,
                'ip_address'   => request()->ip(),
                'url'          => request()->url(),
                'visited_date' => $today,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } catch (\Exception) {
            // Non-fatal
        }
    }

    public function getDashboardStats(): array
    {
        $today     = now()->toDateString();
        $thisMonth = now()->startOfMonth();

        return [
            'total_revenue'    => Order::whereIn('status', ['delivered', 'completed'])->sum('total'),
            'today_revenue'    => Order::whereIn('status', ['delivered', 'completed'])->whereDate('created_at', $today)->sum('total'),
            'total_orders'     => Order::count(),
            'pending_orders'   => Order::where('status', 'pending')->count(),
            'total_customers'  => \App\Models\User::count(),
            'total_products'   => Product::where('is_active', true)->count(),
            'today_visits'     => DB::table('site_visits')->where('visited_date', $today)->count(),
            'month_visits'     => DB::table('site_visits')->where('visited_date', '>=', $thisMonth->toDateString())->count(),
            'total_wishlists'  => Wishlist::count(),
            'positive_ratings' => Review::where('status', 'approved')->where('rating', '>=', 4)->count(),
            'low_stock'        => Product::where('is_active', true)->where('stock', '<=', 5)->count(),
        ];
    }

    public function topSellingProducts(int $limit = 10)
    {
        return \App\Models\OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->with('product.category')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    public function topSellingByCategory()
    {
        return \App\Models\Category::with(['products' => function ($q) {
            $q->withCount(['orderItems as total_sold' => function ($q) {
                $q->select(DB::raw('SUM(quantity)'));
            }])->orderByDesc('total_sold')->limit(10);
        }])->where('is_active', true)->get();
    }

    public function mostReviewedProducts(int $limit = 10)
    {
        return Product::withCount(['approvedReviews as reviews_count'])
            ->orderByDesc('reviews_count')
            ->limit($limit)
            ->get();
    }

    public function mostViewedProducts(int $limit = 10)
    {
        return Product::where('is_active', true)
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get();
    }

    public function recentOrders(int $limit = 10)
    {
        return Order::with(['user', 'payment'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function lowStockProducts(int $threshold = 10)
    {
        return Product::where('is_active', true)
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->get();
    }
}

