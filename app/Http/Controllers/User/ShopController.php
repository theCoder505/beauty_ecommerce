<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $products = $this->buildProductQuery($request)->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('users.pages.shop', compact('products', 'categories'));
    }

    public function category(Request $request, Category $category): View
    {
        $products = $this->buildProductQuery($request, $category->id)->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('users.pages.shop', compact('products', 'categories', 'category'));
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
