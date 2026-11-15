<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(private readonly ImageService $imageService) {}

    public function index(Request $request): View
    {
        $query = Review::with(['user', 'product'])
            ->withCount('images');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('body', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $reviews  = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $statuses = ['pending', 'approved', 'hidden'];

        return view('admin.reviews.index', compact('reviews', 'products', 'statuses'));
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['status' => 'approved']);

        return back()->with('success', 'Review approved.');
    }

    public function hide(Review $review): RedirectResponse
    {
        $review->update(['status' => 'hidden']);

        return back()->with('success', 'Review hidden.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        // Delete associated images from storage
        foreach ($review->images as $reviewImage) {
            $this->imageService->delete($reviewImage->image);
            $reviewImage->delete();
        }

        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
    }
}
