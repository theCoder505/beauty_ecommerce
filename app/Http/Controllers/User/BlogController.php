<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogComment;
use App\Models\BlogLike;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $categories = BlogCategory::where('is_active', true)->withCount(['posts' => fn($q) => $q->where('is_published', true)])->get();

        $posts = BlogPost::where('is_published', true)
            ->with(['category'])
            ->withCount(['approvedComments', 'likes'])
            ->when($request->category, fn($q) => $q->whereHas('category', fn($q2) => $q2->where('slug', $request->category)))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('users.pages.blog', compact('posts', 'categories'));
    }

    public function show(BlogPost $post)
    {
        abort_unless($post->is_published, 404);
        $post->increment('views_count');

        $post->load(['category', 'approvedComments.user', 'likes']);
        $liked    = auth()->check() && $post->likes->where('user_id', auth()->id())->isNotEmpty();
        $related  = BlogPost::where('is_published', true)
            ->where('id', '!=', $post->id)
            ->where('blog_category_id', $post->blog_category_id)
            ->limit(3)->get();

        return view('users.pages.blog-detail', compact('post', 'liked', 'related'));
    }

    public function like(Request $request, BlogPost $post)
    {
        $user = auth()->user();
        $existing = BlogLike::where('blog_post_id', $post->id)->where('user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            BlogLike::create(['blog_post_id' => $post->id, 'user_id' => $user->id]);
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'count' => $post->likes()->count()]);
    }

    public function comment(Request $request, BlogPost $post)
    {
        $request->validate(['body' => 'required|string|max:1000']);

        BlogComment::create([
            'blog_post_id' => $post->id,
            'user_id'      => auth()->id(),
            'body'         => $request->body,
            'status'       => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Comment submitted for review.']);
        }

        return back()->with('success', 'Comment submitted. It will appear after moderation.');
    }
}

