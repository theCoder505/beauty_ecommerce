<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'order_id'   => ['required', 'integer', 'exists:orders,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'body'       => ['nullable', 'string', 'max:2000'],
            'images'     => ['nullable', 'array', 'max:5'],
            'images.*'   => ['image', 'max:2048'],
        ]);

        $userId    = Auth::id();
        $productId = $data['product_id'];
        $orderId   = $data['order_id'];

        // (1) Order belongs to auth user
        $order = Order::where('id', $orderId)->where('user_id', $userId)->first();

        if (! $order) {
            $error = 'This order does not belong to you.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $error], 403)
                : back()->withErrors(['order_id' => $error]);
        }

        // (2) Order status is completed (or delivered)
        if (! in_array($order->status, ['completed', 'delivered'])) {
            $error = 'You can only review products from completed orders.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $error], 422)
                : back()->withErrors(['order_id' => $error]);
        }

        // (3) Order has the product
        $hasProduct = OrderItem::where('order_id', $orderId)
            ->where('product_id', $productId)
            ->exists();

        if (! $hasProduct) {
            $error = 'This product is not part of the specified order.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $error], 422)
                : back()->withErrors(['product_id' => $error]);
        }

        // (4) User hasn't reviewed this product+order combo yet
        $alreadyReviewed = Review::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('order_id', $orderId)
            ->exists();

        if ($alreadyReviewed) {
            $error = 'You have already reviewed this product for this order.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $error], 422)
                : back()->withErrors(['product_id' => $error]);
        }

        // Create review (auto-approved for verified completed order purchasers)
        $review = Review::create([
            'user_id'    => $userId,
            'product_id' => $productId,
            'order_id'   => $orderId,
            'rating'     => $data['rating'],
            'body'       => $data['body'] ?? null,
            'status'     => 'approved',
        ]);

        // Store images
        if ($request->hasFile('images')) {
            $paths = $this->imageService->storeMany($request->file('images'), 'reviews');

            foreach ($paths as $path) {
                ReviewImage::create([
                    'review_id' => $review->id,
                    'image'     => $path,
                ]);
            }
        }

        // Send notification to admin
        $product = \App\Models\Product::find($productId);
        $productTitle = $product ? $product->name : 'a product';
        $userName = auth()->user()->name ?? 'A customer';
        \App\Services\NotificationService::notifyAdmins(
            'review_submitted',
            'New Product Review',
            "{$userName} submitted a {$data['rating']}★ review for \"{$productTitle}\".",
            route('admin.reviews.index'),
            'review'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your review has been submitted and is pending approval. Thank you!',
            ]);
        }

        return back()->with('success', 'Your review has been submitted and is pending approval. Thank you!');
    }
}
