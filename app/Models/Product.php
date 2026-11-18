<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'price', 'sale_price',
        'stock', 'description', 'thumbnail', 'is_active', 'is_featured',
        'views_count', 'meta_title', 'meta_description', 'meta_keywords', 'og_image',
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_active'  => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function productImages()
    {
        return $this->images();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function wishlistedByUsers()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class)->orderBy('sort_order')->orderBy('id');
    }

    public function sizes()
    {
        return $this->hasMany(ProductOption::class)->where('type', 'size')->orderBy('sort_order')->orderBy('id');
    }

    public function colors()
    {
        return $this->hasMany(ProductOption::class)->where('type', 'color')->orderBy('sort_order')->orderBy('id');
    }

    public function shades()
    {
        return $this->hasMany(ProductOption::class)->where('type', 'shade')->orderBy('sort_order')->orderBy('id');
    }

    public function hasSizes(): bool
    {
        return $this->sizes()->exists();
    }

    public function hasColors(): bool
    {
        return $this->colors()->exists();
    }

    public function hasShades(): bool
    {
        return $this->shades()->exists();
    }

    public function hasOptions(): bool
    {
        return $this->options()->exists();
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail) {
            if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
                return $this->thumbnail;
            }
            $clean = ltrim($this->thumbnail, '/');
            if (str_starts_with($clean, 'storage/')) {
                return asset($clean);
            }
            return asset('storage/' . $clean);
        }
        return asset('images/placeholder-product.jpg');
    }

    public function getCurrentPriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->is_on_sale) return 0;
        return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return $this->where($field, $value)->first();
        }

        if (is_numeric($value)) {
            $product = $this->where('id', $value)->first();
            if ($product) {
                return $product;
            }
        }

        return $this->where('slug', $value)->first();
    }

    public function getAverageRatingAttribute(): float
    {
        return round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }
}

