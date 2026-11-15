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

    public function toggle(Request $request, Product $product): JsonResponse
    {
        $wishlisted = $this->wishlistService->toggle($product->id);

        return response()->json([
            'wishlisted' => $wishlisted,
            'count'      => $this->wishlistService->count(),
        ]);
    }

    public function remove(Product $product): RedirectResponse
    {
        Wishlist::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->delete();

        return back()->with('success', 'Product removed from wishlist.');
    }
}
