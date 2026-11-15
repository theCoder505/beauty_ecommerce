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
        });
    }

    public function allowedTransitions(string $currentStatus): array
    {
        return match($currentStatus) {
            'pending'          => ['confirmed', 'cancelled'],
            'confirmed'        => ['processing', 'cancelled'],
            'processing'       => ['shipped', 'cancelled'],
            'shipped'          => ['out-for-delivery', 'returned'],
            'out-for-delivery' => ['delivered', 'returned'],
            'delivered'        => ['completed', 'returned'],
            'completed'        => ['refunded'],
            'cancelled'        => [],
            'returned'         => ['refunded'],
            'refunded'         => [],
            default            => [],
        };
    }
}

