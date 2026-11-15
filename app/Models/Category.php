<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'image', 'description', 'is_active',
        'meta_title', 'meta_description', 'meta_keywords', 'og_image', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
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
        return asset('images/placeholder-category.jpg');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

