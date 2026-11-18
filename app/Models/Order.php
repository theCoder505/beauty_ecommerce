<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'status',
        'return_request_type', 'return_reason', 'return_status',
        'return_requested_at', 'return_actioned_at', 'return_admin_note', 'refund_amount',
        'subtotal', 'shipping_cost', 'shipping_title', 'discount', 'coupon_code', 'total',
        'shipping_name', 'shipping_phone', 'shipping_email',
        'shipping_address', 'shipping_city', 'shipping_state',
        'shipping_zip', 'shipping_country', 'shipping_latitude', 'shipping_longitude', 'notes', 'idempotency_key',
        'cancellation_reason', 'cancelled_by', 'cancelled_at',
        'fraud_risk_score', 'fraud_risk_level', 'fraud_flags',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'            => 'decimal:2',
            'shipping_cost'       => 'decimal:2',
            'discount'            => 'decimal:2',
            'total'               => 'decimal:2',
            'refund_amount'       => 'decimal:2',
            'return_requested_at' => 'datetime',
            'return_actioned_at'  => 'datetime',
            'cancelled_at'        => 'datetime',
            'fraud_risk_score'    => 'integer',
            'fraud_flags'         => 'array',
            'shipping_latitude'   => 'float',
            'shipping_longitude'  => 'float',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelledByUser(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canRequestReturn(): bool
    {
        return $this->isCompleted() && empty($this->return_status);
    }

    public function hasReturnRequest(): bool
    {
        return !empty($this->return_status);
    }

    public function getFraudAnalysisAttribute(): array
    {
        if (!is_null($this->fraud_risk_score)) {
            return [
                'score'          => $this->fraud_risk_score,
                'level'          => $this->fraud_risk_level ?? 'low',
                'flags'          => $this->fraud_flags ?? [],
                'recommendation' => match($this->fraud_risk_level) {
                    'high'   => 'High fraud risk detected. Verify customer identity, phone number, and payment before processing.',
                    'medium' => 'Moderate risk indicators. Review customer delivery details or confirm via phone.',
                    default  => 'Standard low risk order. Ready for normal fulfillment.',
                },
            ];
        }

        return app(\App\Services\FraudDetectionService::class)->evaluateAndSave($this);
    }

    public function getEffectiveLatitudeAttribute(): ?float
    {
        if (!is_null($this->shipping_latitude)) {
            return (float) $this->shipping_latitude;
        }
        if ($this->relationLoaded('user') && $this->user) {
            $addr = $this->user->addresses->firstWhere('address_line', $this->shipping_address)
                ?? $this->user->primaryAddress;
            return $addr?->latitude;
        }
        return null;
    }

    public function getEffectiveLongitudeAttribute(): ?float
    {
        if (!is_null($this->shipping_longitude)) {
            return (float) $this->shipping_longitude;
        }
        if ($this->relationLoaded('user') && $this->user) {
            $addr = $this->user->addresses->firstWhere('address_line', $this->shipping_address)
                ?? $this->user->primaryAddress;
            return $addr?->longitude;
        }
        return null;
    }

    public function getHasCoordinatesAttribute(): bool
    {
        return !is_null($this->effective_latitude) && !is_null($this->effective_longitude);
    }

    public function getGoogleMapsUrlAttribute(): string
    {
        if ($this->has_coordinates) {
            return "https://www.google.com/maps?q={$this->effective_latitude},{$this->effective_longitude}";
        }
        $query = urlencode(trim("{$this->shipping_address}, {$this->shipping_city}, {$this->shipping_country}"));
        return "https://www.google.com/maps/search/?api=1&query={$query}";
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
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'asc');
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

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        $order = $this->where($field, $value)->first();

        if (!$order && is_numeric($value)) {
            $order = $this->where('id', $value)->first();
        }

        if (!$order) {
            abort(404);
        }

        return $order;
    }
}

