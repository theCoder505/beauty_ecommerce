<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(protected CartService $cartService) {}

    /**
     * Place an order inside a DB transaction with stock locking.
     * Returns the created Order.
     */
    public function placeOrder(array $data, string $paymentMethod, ?\Illuminate\Support\Collection $customItems = null, bool $clearCart = true): Order
    {
        // Idempotency: prevent duplicate submission
        $idempotencyKey = $data['idempotency_key'] ?? null;
        if ($idempotencyKey && Order::where('idempotency_key', $idempotencyKey)->exists()) {
            return Order::where('idempotency_key', $idempotencyKey)->firstOrFail();
        }

        $orderItems = $customItems ?? $this->cartService->items();

        if ($orderItems->isEmpty()) {
            throw new \RuntimeException('No items available for checkout.');
        }

        return DB::transaction(function () use ($data, $paymentMethod, $orderItems, $idempotencyKey, $clearCart) {
            $subtotal = 0;

            // Lock product rows for stock validation
            $productIds = $orderItems->pluck('product_id')->toArray();
            $products   = Product::lockForUpdate()->whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($orderItems as $item) {
                $product = $products->get($item->product_id);
                if (!$product || !$product->is_active) {
                    $name = $item->product->name ?? 'Selected product';
                    throw new \RuntimeException("Product \"{$name}\" is no longer available.");
                }
                if ($product->stock < $item->quantity) {
                    throw new \RuntimeException(
                        "Insufficient stock for \"{$product->name}\". Only {$product->stock} left."
                    );
                }
                $subtotal += $item->quantity * ($product->current_price);
            }

            $shippingCost = isset($data['shipping_cost']) ? (float) $data['shipping_cost'] : $this->calculateShipping($subtotal);
            $shippingTitle = $data['shipping_title'] ?? null;
            $discount = isset($data['discount']) ? min($subtotal, (float) $data['discount']) : 0;
            $couponCode = $data['coupon_code'] ?? null;
            $total = max(0, $subtotal - $discount) + $shippingCost;

            // Create order
            $order = Order::create([
                'order_number'    => Order::generateOrderNumber(),
                'user_id'         => Auth::id(),
                'status'          => 'pending',
                'subtotal'        => $subtotal,
                'shipping_cost'   => $shippingCost,
                'shipping_title'  => $shippingTitle,
                'discount'        => $discount,
                'coupon_code'     => $couponCode,
                'total'           => $total,
                'shipping_name'   => $data['shipping_name'],
                'shipping_phone'  => $data['shipping_phone'],
                'shipping_email'  => $data['shipping_email'],
                'shipping_address'=> $data['shipping_address'],
                'shipping_city'   => $data['shipping_city'],
                'shipping_state'  => $data['shipping_state'] ?? null,
                'shipping_zip'    => $data['shipping_zip'] ?? null,
                'shipping_country'  => $data['shipping_country'] ?? 'BD',
                'shipping_latitude' => $data['shipping_latitude'] ?? null,
                'shipping_longitude'=> $data['shipping_longitude'] ?? null,
                'notes'             => $data['notes'] ?? null,
                'idempotency_key'   => $idempotencyKey,
            ]);

            // Create order items & decrement stock
            foreach ($orderItems as $item) {
                $product = $products->get($item->product_id);
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_sku'  => $product->sku,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $product->current_price,
                    'total'        => $item->quantity * $product->current_price,
                ]);
                $product->decrement('stock', $item->quantity);
            }

            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'method'   => $paymentMethod,
                'status'   => $paymentMethod === 'cod' ? 'pending' : 'pending',
                'amount'   => $total,
                'transaction_id' => $data['transaction_id'] ?? null,
            ]);

            // Log initial status history
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'pending',
                'changed_by' => 'system',
                'note'       => 'Order placed.',
            ]);

            // Clear cart only if regular cart checkout
            if ($clearCart) {
                $this->cartService->clear();
            }

            // Send notification to Admin and User
            \App\Services\NotificationService::notifyAdmins(
                'order_placed',
                'New Order Received',
                "Order #{$order->order_number} placed by {$order->shipping_name} (" . number_format($order->total, 2) . ").",
                route('admin.orders.show', $order->id),
                'order'
            );

            if ($order->user_id) {
                \App\Services\NotificationService::notifyUser(
                    $order->user_id,
                    'order_placed',
                    'Order Confirmation',
                    "Your order #{$order->order_number} has been placed successfully.",
                    route('user.orders.show', $order->id),
                    'order'
                );
            }

            return $order;
        });
    }

    protected function calculateShipping(float $subtotal): float
    {
        // Free shipping above 1000, else flat 60
        return $subtotal >= 1000 ? 0 : 60;
    }
}

