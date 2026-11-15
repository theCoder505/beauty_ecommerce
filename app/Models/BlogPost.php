<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'blog_category_id', 'title', 'slug', 'cover_image', 'content',
        'excerpt', 'is_published', 'published_at',
        'meta_title', 'meta_description', 'meta_keywords', 'og_image', 'views_count',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(BlogComment::class)->where('status', 'approved');
    }

    public function likes()
    {
        return $this->hasMany(BlogLike::class);
    }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('images/placeholder-blog.jpg');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

