<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $products = $this->buildProductQuery($request)->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        $minPossiblePrice = (float) (Product::where('is_active', true)->min('price') ?? 0);
        $maxPossiblePrice = (float) (Product::where('is_active', true)->max('price') ?? 1000);
        if ($maxPossiblePrice <= $minPossiblePrice) {
            $maxPossiblePrice = $minPossiblePrice + 1000;
        }

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳');
            $html = view('users.partials.shop-product-list', compact('products', 'currency'))->render();
            return response()->json([
                'html'       => $html,
                'total'      => $products->total(),
                'pagination' => (string) $products->links(),
                'title'      => 'All Products',
            ]);
        }

        return view('users.pages.shop', compact('products', 'categories', 'minPossiblePrice', 'maxPossiblePrice'));
    }

    public function category(Request $request, Category $category)
    {
        $products = $this->buildProductQuery($request, $category->id)->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        $minPossiblePrice = (float) (Product::where('is_active', true)->min('price') ?? 0);
        $maxPossiblePrice = (float) (Product::where('is_active', true)->max('price') ?? 1000);
        if ($maxPossiblePrice <= $minPossiblePrice) {
            $maxPossiblePrice = $minPossiblePrice + 1000;
        }

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳');
            $html = view('users.partials.shop-product-list', compact('products', 'currency'))->render();
            return response()->json([
                'html'       => $html,
                'total'      => $products->total(),
                'pagination' => (string) $products->links(),
                'title'      => $category->name,
                'category_id'=> $category->id,
            ]);
        }

        return view('users.pages.shop', compact('products', 'categories', 'category', 'minPossiblePrice', 'maxPossiblePrice'));
    }

    private function buildProductQuery(Request $request, ?int $forceCategoryId = null)
    {
        $query = Product::where('is_active', true)
            ->with(['category', 'images'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->withCount('orderItems');

        // Category filter
        $categoryId = $forceCategoryId ?? $request->input('category');
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Price range filter
        if ($request->filled('price_min')) {
            $query->where(function ($q) use ($request) {
                $q->where('sale_price', '>=', $request->price_min)
                    ->orWhere(function ($q2) use ($request) {
                        $q2->whereNull('sale_price')->where('price', '>=', $request->price_min);
                    });
            });
        }

        if ($request->filled('price_max')) {
            $query->where(function ($q) use ($request) {
                $q->where('sale_price', '<=', $request->price_max)
                    ->orWhere(function ($q2) use ($request) {
                        $q2->whereNull('sale_price')->where('price', '<=', $request->price_max);
                    });
            });
        }

        // Search filter
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', $term)
                    ->orWhere('description', 'LIKE', $term);
            });
        }

        // Rating filter (minimum average rating)
        if ($request->filled('rating')) {
            $minRating = (float) $request->rating;
            $query->having('approved_reviews_avg_rating', '>=', $minRating);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');

        match ($sort) {
            'price_asc'  => $query->orderByRaw('COALESCE(sale_price, price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) DESC'),
            'rating'     => $query->orderByDesc('approved_reviews_avg_rating'),
            'popular'    => $query->orderByDesc('order_items_count'),
            default      => $query->latest(),
        };

        return $query;
    }
}
