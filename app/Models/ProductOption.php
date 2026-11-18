<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOption extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'name',
        'code',
        'price_modifier',
        'is_available',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_modifier' => 'decimal:2',
            'is_available'   => 'boolean',
            'sort_order'     => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}
