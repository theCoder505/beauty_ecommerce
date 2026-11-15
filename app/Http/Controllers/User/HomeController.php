<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
    ) {}

    public function index(): View
    {
        $this->analyticsService->trackVisit();

        $featuredCategories = Category::where('is_active', true)
            ->withCount('products')
            ->orderByDesc('sort_order')
            ->limit(6)
            ->get();

        $featuredProducts = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->latest()
            ->limit(8)
            ->get();

        $bestSellers = Product::where('is_active', true)
            ->with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->limit(8)
            ->get();

        $newArrivals = Product::where('is_active', true)
            ->with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->latest()
            ->limit(6)
            ->get();

        $latestPosts = BlogPost::where('is_published', true)
            ->with(['category'])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('users.pages.home', compact(
            'featuredCategories',
            'featuredProducts',
            'bestSellers',
            'newArrivals',
            'latestPosts',
        ));
    }

    public function subscribe(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        NewsletterSubscriber::updateOrCreate(
            ['email' => $data['email']],
            ['is_active' => true],
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing to our newsletter!',
            ]);
        }

        return back()->with('success', 'Thank you for subscribing to our newsletter!');
    }
}
