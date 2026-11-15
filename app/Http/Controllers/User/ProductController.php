<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
    ) {}

    public function show(Product $product): View
    {
        if (! $product->is_active) {
            abort(404);
        }

        $this->analyticsService->trackProductView($product->id);

        $product->load([
            'images',
            'approvedReviews.user',
            'approvedReviews.images',
            'category',
        ]);

        $relatedProducts = Product::where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['images'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->latest()
            ->limit(4)
            ->get();

        $canReview    = false;
        $reviewableOrderId = null;

        if (Auth::check()) {
            $userId = Auth::id();

            // Find a delivered/completed order that contains this product
            // and for which the user has not yet written a review.
            $deliveredOrders = Order::where('user_id', $userId)
                ->whereIn('status', ['delivered', 'completed'])
                ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
                ->get();

            foreach ($deliveredOrders as $order) {
                $alreadyReviewed = Review::where('user_id', $userId)
                    ->where('product_id', $product->id)
                    ->where('order_id', $order->id)
                    ->exists();

                if (! $alreadyReviewed) {
                    $canReview         = true;
                    $reviewableOrderId = $order->id;
                    break;
                }
            }
        }

        return view('users.pages.product-detail', compact(
            'product',
            'relatedProducts',
            'canReview',
            'reviewableOrderId',
        ));
    }
}
