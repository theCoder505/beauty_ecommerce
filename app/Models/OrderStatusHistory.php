<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    protected $fillable = ['order_id', 'status', 'changed_by', 'note'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getStatusLabelAttribute(): string
    {
        $st = strtolower(trim($this->status ?? ''));
        if (in_array($st, ['refund/return requested', 'refund_return_requested', 'return_requested', 'refund_requested', 'return/refund requested'])
            || ($st === 'completed' && str_contains(strtolower($this->note ?? ''), 'customer requested'))) {
            return 'Refund/Return Requested';
        }

        return match($st) {
            'pending'          => 'Pending',
            'confirmed'        => 'Confirmed',
            'processing'       => 'Processing',
            'shipped'          => 'Shipped',
            'out-for-delivery' => 'Out for Delivery',
            'delivered'        => 'Delivered',
            'completed'        => 'Completed',
            'cancelled'        => 'Cancelled',
            'returned'         => 'Returned',
            'refunded'         => 'Refunded',
            default            => ucfirst(str_replace('-', ' ', $this->status)),
        };
    }
}

