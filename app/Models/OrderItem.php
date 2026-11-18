<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'product_sku',
        'selected_size', 'selected_color', 'selected_shade', 'options_data',
        'quantity', 'unit_price', 'total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'   => 'decimal:2',
            'total'        => 'decimal:2',
            'options_data' => 'array',
        ];
    }

    public function getOptionsSummaryAttribute(): string
    {
        $parts = [];
        if (!empty($this->selected_size)) {
            $parts[] = 'Size: ' . $this->selected_size;
        }
        if (!empty($this->selected_color)) {
            $parts[] = 'Color: ' . $this->selected_color;
        }
        if (!empty($this->selected_shade)) {
            $parts[] = 'Shade: ' . $this->selected_shade;
        }
        return implode(' · ', $parts);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}

