<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'status',
        'subtotal', 'shipping_cost', 'discount', 'total',
        'shipping_name', 'shipping_phone', 'shipping_email',
        'shipping_address', 'shipping_city', 'shipping_state',
        'shipping_zip', 'shipping_country', 'notes', 'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'      => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'discount'      => 'decimal:2',
            'total'         => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderByDesc('created_at');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
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
            default            => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'          => 'yellow',
            'confirmed'        => 'blue',
            'processing'       => 'indigo',
            'shipped'          => 'purple',
            'out-for-delivery' => 'orange',
            'delivered'        => 'green',
            'completed'        => 'emerald',
            'cancelled'        => 'red',
            'returned'         => 'rose',
            'refunded'         => 'gray',
            default            => 'gray',
        };
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }
}

