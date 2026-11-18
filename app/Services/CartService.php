<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CartService
{
    protected function sessionId(): string
    {
        return session()->getId();
    }

    protected function query()
    {
        $query = CartItem::with('product.images');

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', $this->sessionId());
        }

        return $query;
    }

    public function items(): Collection
    {
        return $this->query()->get();
    }

    public function count(): int
    {
        try {
            return (int) $this->query()->sum('quantity');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function total(): float
    {
        return $this->items()->sum(fn($item) => $item->line_total);
    }

    public function add(int $productId, int $quantity = 1, ?string $size = null, ?string $color = null, ?string $shade = null): CartItem
    {
        $product = Product::findOrFail($productId);

        $query = $this->query()->where('product_id', $productId);
        if ($size) {
            $query->where('selected_size', $size);
        } else {
            $query->whereNull('selected_size');
        }

        if ($color) {
            $query->where('selected_color', $color);
        } else {
            $query->whereNull('selected_color');
        }

        if ($shade) {
            $query->where('selected_shade', $shade);
        } else {
            $query->whereNull('selected_shade');
        }

        $existing = $query->first();

        if ($existing) {
            $newQty = min($existing->quantity + $quantity, $product->stock);
            $existing->update(['quantity' => $newQty]);
            return $existing;
        }

        $data = [
            'product_id'     => $productId,
            'quantity'       => min($quantity, $product->stock),
            'selected_size'  => $size,
            'selected_color' => $color,
            'selected_shade' => $shade,
        ];

        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        } else {
            $data['session_id'] = $this->sessionId();
        }

        return CartItem::create($data);
    }

    public function update(int $cartItemId, int $quantity): bool
    {
        $item = $this->query()->where('id', $cartItemId)->firstOrFail();
        $max  = $item->product->stock ?? $quantity;
        $item->update(['quantity' => min($quantity, $max)]);
        return true;
    }

    public function remove(int $cartItemId): bool
    {
        return $this->query()->where('id', $cartItemId)->delete() > 0;
    }

    public function clear(): void
    {
        $this->query()->delete();
    }

    /**
     * Merge guest cart into user cart after login.
     */
    public function mergeGuestCart(int $userId): void
    {
        $guestItems = CartItem::where('session_id', $this->sessionId())->get();
        foreach ($guestItems as $guestItem) {
            $existing = CartItem::where('user_id', $userId)
                ->where('product_id', $guestItem->product_id)
                ->where('selected_size', $guestItem->selected_size)
                ->where('selected_color', $guestItem->selected_color)
                ->where('selected_shade', $guestItem->selected_shade)
                ->first();
            if ($existing) {
                $product = $guestItem->product;
                $newQty  = min($existing->quantity + $guestItem->quantity, $product->stock ?? 999);
                $existing->update(['quantity' => $newQty]);
                $guestItem->delete();
            } else {
                $guestItem->update(['user_id' => $userId, 'session_id' => null]);
            }
        }
    }
}

