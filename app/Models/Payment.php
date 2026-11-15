<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'method', 'status', 'amount',
        'transaction_id', 'gateway_response',
        'verified_by', 'verified_at', 'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

