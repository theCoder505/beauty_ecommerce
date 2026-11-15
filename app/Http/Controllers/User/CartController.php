<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    public function index(): View
    {
        $items = $this->cartService->items();
        $total = $this->cartService->total();
        $count = $this->cartService->count();

        return view('users.pages.cart', compact('items', 'total', 'count'));
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $this->cartService->add($data['product_id'], $data['quantity']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $this->cartService->count(),
                'message' => 'Item added to cart.',
            ]);
        }

        return back()->with('success', 'Item added to your cart.');
    }

    public function update(Request $request, CartItem $item): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $this->cartService->update($item->id, $data['quantity']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'total'   => $this->cartService->total(),
                'message' => 'Cart updated.',
            ]);
        }

        return back()->with('success', 'Cart updated.');
    }

    public function remove(CartItem $item): JsonResponse|RedirectResponse
    {
        $this->cartService->remove($item->id);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $this->cartService->count(),
                'message' => 'Item removed from cart.',
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }
}
