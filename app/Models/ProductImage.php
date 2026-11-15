<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image', 'sort_order'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
                return $this->image;
            }
            $clean = ltrim($this->image, '/');
            if (str_starts_with($clean, 'storage/')) {
                return asset($clean);
            }
            return asset('storage/' . $clean);
        }
        return asset('images/placeholder-product.jpg');
    }
}

