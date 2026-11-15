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
    public function placeOrder(array $data, string $paymentMethod): Order
    {
        // Idempotency: prevent duplicate submission
        $idempotencyKey = $data['idempotency_key'] ?? null;
        if ($idempotencyKey && Order::where('idempotency_key', $idempotencyKey)->exists()) {
            return Order::where('idempotency_key', $idempotencyKey)->firstOrFail();
        }

        $cartItems = $this->cartService->items();

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException('Your cart is empty.');
        }

        return DB::transaction(function () use ($data, $paymentMethod, $cartItems, $idempotencyKey) {
            $subtotal = 0;

            // Lock product rows for stock validation
            $productIds = $cartItems->pluck('product_id')->toArray();
            $products   = Product::lockForUpdate()->whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($cartItems as $item) {
                $product = $products->get($item->product_id);
                if (!$product || !$product->is_active) {
                    throw new \RuntimeException("Product \"{$item->product->name}\" is no longer available.");
                }
                if ($product->stock < $item->quantity) {
                    throw new \RuntimeException(
                        "Insufficient stock for \"{$product->name}\". Only {$product->stock} left."
                    );
                }
                $subtotal += $item->quantity * ($product->current_price);
            }

            $shippingCost = $this->calculateShipping($subtotal);
            $total        = $subtotal + $shippingCost;

            // Create order
            $order = Order::create([
                'order_number'    => Order::generateOrderNumber(),
                'user_id'         => Auth::id(),
                'status'          => 'pending',
                'subtotal'        => $subtotal,
                'shipping_cost'   => $shippingCost,
                'discount'        => 0,
                'total'           => $total,
                'shipping_name'   => $data['shipping_name'],
                'shipping_phone'  => $data['shipping_phone'],
                'shipping_email'  => $data['shipping_email'],
                'shipping_address'=> $data['shipping_address'],
                'shipping_city'   => $data['shipping_city'],
                'shipping_state'  => $data['shipping_state'] ?? null,
                'shipping_zip'    => $data['shipping_zip'] ?? null,
                'shipping_country'=> $data['shipping_country'] ?? 'BD',
                'notes'           => $data['notes'] ?? null,
                'idempotency_key' => $idempotencyKey,
            ]);

            // Create order items & decrement stock
            foreach ($cartItems as $item) {
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

            // Clear cart
            $this->cartService->clear();

            return $order;
        });
    }

    protected function calculateShipping(float $subtotal): float
    {
        // Free shipping above 1000, else flat 60
        return $subtotal >= 1000 ? 0 : 60;
    }
}

