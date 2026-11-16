<?php

namespace App\Services;

use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistService
{
    protected ?array $userWishlistIdsCache = null;

    public function userWishlistProductIds(): array
    {
        if (!Auth::check()) return [];
        if ($this->userWishlistIdsCache === null) {
            try {
                $this->userWishlistIdsCache = Wishlist::where('user_id', Auth::id())
                    ->pluck('product_id')
                    ->map(fn($id) => (int)$id)
                    ->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        }
        return $this->userWishlistIdsCache;
    }

    public function toggle(int $productId): bool
    {
        $this->userWishlistIdsCache = null;

        $existing = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false; // removed
        }

        Wishlist::create(['user_id' => Auth::id(), 'product_id' => $productId]);
        return true; // added
    }

    public function isWishlisted(int $productId): bool
    {
        if (!Auth::check()) return false;
        return in_array((int)$productId, $this->userWishlistProductIds(), true);
    }

    public function getUserWishlist()
    {
        return Wishlist::with('product.images')
            ->where('user_id', Auth::id())
            ->get();
    }

    public function count(): int
    {
        try {
            if (!Auth::check()) return 0;
            return Wishlist::where('user_id', Auth::id())->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}

