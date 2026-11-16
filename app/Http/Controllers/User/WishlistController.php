<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlistService,
    ) {}

    public function index(): View
    {
        $items = $this->wishlistService->getUserWishlist();

        return view('users.pages.wishlist', compact('items'));
    }

    public function toggle(Request $request, $product = null): JsonResponse|RedirectResponse
    {
        $targetProduct = null;
        if ($product instanceof Product) {
            $targetProduct = $product;
        } elseif (is_numeric($product)) {
            $targetProduct = Product::find($product);
        } elseif (is_string($product)) {
            $targetProduct = Product::where('slug', $product)->orWhere('id', $product)->first();
        }

        if (!$targetProduct && $request->filled('product_id')) {
            $targetProduct = Product::find($request->input('product_id'));
        }

        if (!$targetProduct) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
            }
            return back()->with('error', 'Product not found.');
        }

        $wishlisted = $this->wishlistService->toggle($targetProduct->id);
        $count = $this->wishlistService->count();
        $message = $wishlisted ? 'Saved to your wishlist!' : 'Removed from wishlist.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'wishlisted' => $wishlisted,
                'count'      => $count,
                'message'    => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function remove(Product $product): RedirectResponse
    {
        Wishlist::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->delete();

        return back()->with('success', 'Product removed from wishlist.');
    }
}
