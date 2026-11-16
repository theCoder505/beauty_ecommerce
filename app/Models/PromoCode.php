<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'apply_to',
        'product_ids',
        'min_spend',
        'max_discount',
        'usage_limit',
        'used_count',
        'is_active',
        'starts_at',
        'expires_at',
        'description',
    ];

    protected $casts = [
        'value'        => 'decimal:2',
        'min_spend'    => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit'  => 'integer',
        'used_count'   => 'integer',
        'is_active'    => 'boolean',
        'product_ids'  => 'array',
        'starts_at'    => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function hasStarted(): bool
    {
        return !$this->starts_at || $this->starts_at->isPast();
    }

    public function isUsageLimitReached(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    /**
     * Calculate applicable discount on items collection.
     * Items can be CartItems or BuyNow items objects with ->product_id, ->quantity, ->product (or current_price).
     *
     * @param iterable $items
     * @param float $subtotal
     * @return array [bool $valid, float $discount, string $message]
     */
    public function computeDiscount(iterable $items, float $subtotal): array
    {
        if (!$this->is_active) {
            return [false, 0, 'This promo code is inactive.'];
        }

        if (!$this->hasStarted()) {
            return [false, 0, 'This promo code is not active yet.'];
        }

        if ($this->isExpired()) {
            return [false, 0, 'This promo code has expired.'];
        }

        if ($this->isUsageLimitReached()) {
            return [false, 0, 'This promo code has reached its usage limit.'];
        }

        if ($this->min_spend !== null && $subtotal < $this->min_spend) {
            return [false, 0, "Minimum spend of {$this->min_spend} required to use this promo code."];
        }

        // Filter eligible items
        $eligibleSubtotal = 0;
        $matchedCount = 0;
        $productIds = is_array($this->product_ids) ? array_map('intval', $this->product_ids) : [];

        foreach ($items as $item) {
            $pId = (int) ($item->product_id ?? $item->product?->id ?? 0);
            $qty = (int) ($item->quantity ?? 1);
            $price = (float) ($item->current_price ?? $item->product?->current_price ?? ($item->unit_price ?? 0));

            $isEligible = ($this->apply_to === 'all') || in_array($pId, $productIds, true);

            if ($isEligible) {
                $matchedCount++;
                $eligibleSubtotal += ($price * $qty);
            }
        }

        if ($this->apply_to === 'specific' && $matchedCount === 0) {
            return [false, 0, 'This promo code is only valid for specific products that are not currently in your order.'];
        }

        if ($eligibleSubtotal <= 0) {
            return [false, 0, 'No eligible items for this promo code.'];
        }

        // Calculate discount
        if ($this->type === 'percentage') {
            $discount = ($eligibleSubtotal * $this->value) / 100;
            if ($this->max_discount !== null && $this->max_discount > 0) {
                $discount = min($discount, (float) $this->max_discount);
            }
        } else {
            // Fixed amount
            $discount = (float) $this->value;
        }

        // Promo code discount cannot exceed eligible subtotal or overall subtotal
        // e.g. if product price is 100 and promo code is 120, user gets product for free, discount capped at 100
        $discount = min($discount, $eligibleSubtotal);
        if ($subtotal > 0) {
            $discount = min($discount, (float) $subtotal);
        }
        $discount = round($discount, 2);

        return [true, $discount, 'Promo code applied successfully.'];
    }

    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
