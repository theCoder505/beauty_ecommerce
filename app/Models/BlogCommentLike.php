<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogCommentLike extends Model
{
    protected $fillable = ['blog_comment_id', 'user_id'];

    public function comment()
    {
        return $this->belongsTo(BlogComment::class, 'blog_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
