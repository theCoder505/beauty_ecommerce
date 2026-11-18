<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'quantity',
        'selected_size',
        'selected_color',
        'selected_shade',
        'options_data',
    ];

    protected function casts(): array
    {
        return [
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

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLineTotalAttribute(): float
    {
        return $this->quantity * ($this->product->current_price ?? 0);
    }
}

