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
            'size'       => ['nullable', 'string', 'max:100'],
            'color'      => ['nullable', 'string', 'max:100'],
            'shade'      => ['nullable', 'string', 'max:100'],
        ]);

        $product = \App\Models\Product::with('options')->findOrFail($data['product_id']);

        // Enforce required option selections if product has them
        if ($product->hasSizes() && empty($data['size'])) {
            $msg = 'Please select a size.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => ['size' => [$msg]]], 422);
            }
            return back()->withErrors(['size' => $msg]);
        }

        if ($product->hasColors() && empty($data['color'])) {
            $msg = 'Please select a color.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => ['color' => [$msg]]], 422);
            }
            return back()->withErrors(['color' => $msg]);
        }

        if ($product->hasShades() && empty($data['shade'])) {
            $msg = 'Please select a shade.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => ['shade' => [$msg]]], 422);
            }
            return back()->withErrors(['shade' => $msg]);
        }

        // Validate availability of selected options
        if (!empty($data['size'])) {
            $opt = $product->sizes()->where('name', $data['size'])->first();
            if ($opt && !$opt->is_available) {
                $msg = "Size '{$data['size']}' is currently unavailable.";
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['size' => [$msg]]], 422);
                }
                return back()->withErrors(['size' => $msg]);
            }
        }

        if (!empty($data['color'])) {
            $opt = $product->colors()->where('name', $data['color'])->first();
            if ($opt && !$opt->is_available) {
                $msg = "Color '{$data['color']}' is currently unavailable.";
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['color' => [$msg]]], 422);
                }
                return back()->withErrors(['color' => $msg]);
            }
        }

        if (!empty($data['shade'])) {
            $opt = $product->shades()->where('name', $data['shade'])->first();
            if ($opt && !$opt->is_available) {
                $msg = "Shade '{$data['shade']}' is currently unavailable.";
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['shade' => [$msg]]], 422);
                }
                return back()->withErrors(['shade' => $msg]);
            }
        }

        $this->cartService->add(
            $data['product_id'],
            $data['quantity'],
            $data['size'] ?? null,
            $data['color'] ?? null,
            $data['shade'] ?? null
        );

        if ($request->expectsJson() || $request->ajax()) {
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
