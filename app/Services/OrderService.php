<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected const RESTOCK_ON = ['cancelled', 'returned', 'refunded'];

    public function changeStatus(Order $order, string $newStatus, string $changedBy, ?string $note = null): void
    {
        $oldStatus = $order->status;

        DB::transaction(function () use ($order, $newStatus, $changedBy, $note, $oldStatus) {
            $order->update(['status' => $newStatus]);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $newStatus,
                'changed_by' => $changedBy,
                'note'       => $note,
            ]);

            // Restock if applicable
            if (in_array($newStatus, self::RESTOCK_ON) && !in_array($oldStatus, self::RESTOCK_ON)) {
                foreach ($order->items as $item) {
                    Product::withTrashed()->where('id', $item->product_id)
                        ->increment('stock', $item->quantity);
                }
            }

            // Decrement stock if transitioning back from a restocked status
            if (!in_array($newStatus, self::RESTOCK_ON) && in_array($oldStatus, self::RESTOCK_ON)) {
                foreach ($order->items as $item) {
                    Product::withTrashed()->where('id', $item->product_id)
                        ->decrement('stock', $item->quantity);
                }
            }

            // Notify user about order status change
            if ($order->user_id && $oldStatus !== $newStatus) {
                $statusLabel = self::allStatuses()[$newStatus] ?? ucfirst(str_replace('-', ' ', $newStatus));
                \App\Services\NotificationService::notifyUser(
                    $order->user_id,
                    'order_status',
                    'Order Status Updated',
                    "Your order #{$order->order_number} status has been updated to {$statusLabel}.",
                    route('user.orders.show', $order->id),
                    'status'
                );
            }
        });
    }

    public static function allStatuses(): array
    {
        return [
            'pending'          => 'Pending',
            'confirmed'        => 'Confirmed',
            'processing'       => 'Processing',
            'shipped'          => 'Shipped',
            'out-for-delivery' => 'Out for delivery',
            'delivered'        => 'Delivered',
            'completed'        => 'Completed',
            'cancelled'        => 'Cancelled',
            'returned'         => 'Returned',
            'refunded'         => 'Refunded',
        ];
    }

    public function allowedTransitions(string $currentStatus): array
    {
        // For standard e-commerce business operations, admins can transition to any valid status
        $all = array_keys(self::allStatuses());
        return array_values(array_filter($all, fn($s) => $s !== $currentStatus));
    }
}

