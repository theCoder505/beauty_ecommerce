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

    public function getCompletedAtAttribute(): ?\Carbon\Carbon
    {
        if (!$this->isCompleted()) {
            return null;
        }

        if ($this->relationLoaded('statusHistories')) {
            $history = $this->statusHistories->where('status', 'completed')->sortByDesc('created_at')->first();
            if ($history) {
                return $history->created_at;
            }
        } else {
            $history = $this->statusHistories()->where('status', 'completed')->latest()->first();
            if ($history) {
                return $history->created_at;
            }
        }

        return $this->updated_at;
    }

    public function getReturnLimitDaysAttribute(): int
    {
        $days = (int) (app(\App\Services\SettingService::class)->get('return_time_limit_days', 3));
        return $days > 0 ? $days : 3;
    }

    public function getReturnDeadlineAttribute(): ?\Carbon\Carbon
    {
        $completedAt = $this->completed_at;
        if (!$completedAt) {
            return null;
        }
        return $completedAt->copy()->addDays($this->return_limit_days);
    }

    public function isWithinReturnWindow(): bool
    {
        if (!$this->isCompleted()) {
            return false;
        }

        $deadline = $this->return_deadline;
        if (!$deadline) {
            return false;
        }

        return now()->lte($deadline);
    }

    public function canRequestReturn(): bool
    {
        return $this->isCompleted() && empty($this->return_status) && $this->isWithinReturnWindow();
    }

    public function daysLeftForReturn(): int
    {
        if (!$this->isCompleted()) {
            return 0;
        }
        $deadline = $this->return_deadline;
        if (!$deadline || now()->gt($deadline)) {
            return 0;
        }
        return max(0, (int) ceil(now()->diffInSeconds($deadline, false) / 86400));
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
        if ($this->return_status === 'requested') {
            return 'Refund/Return Requested';
        }

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
        if ($this->return_status === 'requested') {
            return 'amber';
        }

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

    public function getStatusBadgeClassesAttribute(): string
    {
        if ($this->return_status === 'requested') {
            return 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800';
        }

        return match($this->status) {
            'pending'          => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300',
            'confirmed'        => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300',
            'processing'       => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-300',
            'shipped'          => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300',
            'out-for-delivery' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/20 dark:text-cyan-300',
            'delivered'        => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-300',
            'completed'        => 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-300',
            'cancelled'        => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300',
            'returned'         => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/20 dark:text-rose-300',
            'refunded'         => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300',
            default            => 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-800 dark:text-gray-300',
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

