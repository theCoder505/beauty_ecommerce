<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\HeroSlide;
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

        $heroSlides = HeroSlide::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

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

        $latestProducts = Product::where('is_active', true)
            ->with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->latest()
            ->limit(24)
            ->get();

        $newArrivals = $latestProducts->take(6);

        $latestPosts = BlogPost::where('is_published', true)
            ->with(['category'])
            ->orderByDesc('published_at')
            ->limit(16)
            ->get();

        return view('users.pages.home', compact(
            'heroSlides',
            'featuredCategories',
            'featuredProducts',
            'bestSellers',
            'newArrivals',
            'latestProducts',
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

        \App\Services\NotificationService::notifyAdmins(
            'newsletter_subscribe',
            'New Subscriber',
            "{$data['email']} subscribed to the newsletter.",
            route('admin.subscribers.index'),
            'subscribe'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing to our newsletter!',
            ]);
        }

        return back()->with('success', 'Thank you for subscribing to our newsletter!');
    }

    public function terms(): View
    {
        $this->analyticsService->trackVisit();
        $settings = app(\App\Services\SettingService::class)->all();
        $content = $settings['terms_and_conditions'] ?? null;
        $siteName = $settings['site_name'] ?? config('app.name');

        return view('users.pages.terms', compact('content', 'siteName'));
    }

    public function privacy(): View
    {
        $this->analyticsService->trackVisit();
        $settings = app(\App\Services\SettingService::class)->all();
        $content = $settings['privacy_policy'] ?? null;
        $siteName = $settings['site_name'] ?? config('app.name');

        return view('users.pages.privacy', compact('content', 'siteName'));
    }
}
