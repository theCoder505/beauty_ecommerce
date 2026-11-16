<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogComment;
use App\Models\BlogCommentLike;
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

        $post->load([
            'category',
            'approvedTopComments.user',
            'approvedTopComments.likes',
            'approvedTopComments.replies.user',
            'approvedTopComments.replies.likes',
            'likes'
        ]);

        $liked   = auth()->check() && $post->likes->where('user_id', auth()->id())->isNotEmpty();
        $related = BlogPost::where('is_published', true)
            ->where('id', '!=', $post->id)
            ->where('blog_category_id', $post->blog_category_id)
            ->limit(3)->get();

        $totalCommentsCount = $post->approvedComments()->count();

        return view('users.pages.blog-detail', compact('post', 'liked', 'related', 'totalCommentsCount'));
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

            \App\Services\NotificationService::notifyAdmins(
                'blog_like',
                'New Post Like',
                "{$user->name} liked your blog post \"{$post->title}\".",
                route('blog.show', $post->slug),
                'like'
            );
        }

        return response()->json(['liked' => $liked, 'count' => $post->likes()->count()]);
    }

    public function comment(Request $request, BlogPost $post)
    {
        $request->validate([
            'body'      => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:blog_comments,id',
        ]);

        $parentId = $request->parent_id;
        $parentComment = null;
        if ($parentId) {
            $parentComment = BlogComment::find($parentId);
            if ($parentComment && $parentComment->parent_id) {
                // If replying to a reply, flatten into the parent comment's thread
                $parentId = $parentComment->parent_id;
            }
        }

        $comment = BlogComment::create([
            'blog_post_id' => $post->id,
            'parent_id'    => $parentId,
            'user_id'      => auth()->id(),
            'body'         => $request->body,
            'status'       => 'approved',
        ]);

        $comment->load(['user', 'likes']);
        $currentUser = auth()->user();

        // Send notifications
        if ($parentId && $parentComment) {
            // Reply notification to parent comment author
            if ($parentComment->user_id && $parentComment->user_id !== auth()->id()) {
                \App\Services\NotificationService::notifyUser(
                    $parentComment->user_id,
                    'comment_reply',
                    'New Reply to Your Comment',
                    "{$currentUser->name} replied to your comment on \"{$post->title}\".",
                    route('blog.show', $post->slug) . '#comments',
                    'reply'
                );
            }

            // Notification to admin
            \App\Services\NotificationService::notifyAdmins(
                'blog_reply',
                'New Comment Reply',
                "{$currentUser->name} replied to a comment on \"{$post->title}\".",
                route('admin.blog-comments.index'),
                'reply'
            );
        } else {
            // New comment notification to admin
            \App\Services\NotificationService::notifyAdmins(
                'blog_comment',
                'New Blog Comment',
                "{$currentUser->name} commented on \"{$post->title}\".",
                route('admin.blog-comments.index'),
                'comment'
            );
        }

        if ($request->expectsJson() || $request->ajax()) {
            $html = view('users.pages.partials.comment-item', [
                'comment' => $comment,
                'isReply' => !empty($parentId),
                'post'    => $post,
            ])->render();

            return response()->json([
                'success'     => true,
                'message'     => 'Comment posted successfully.',
                'comment_id'  => $comment->id,
                'parent_id'   => $parentId,
                'html'        => $html,
                'total_count' => $post->approvedComments()->count(),
            ]);
        }

        return back()->with('success', 'Comment submitted successfully.');
    }

    public function likeComment(Request $request, BlogComment $comment)
    {
        $user = auth()->user();
        $existing = BlogCommentLike::where('blog_comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            BlogCommentLike::create([
                'blog_comment_id' => $comment->id,
                'user_id'         => $user->id,
            ]);
            $liked = true;

            // Notify comment author if it's not the same person
            if ($comment->user_id && $comment->user_id !== $user->id) {
                $post = $comment->blogPost;
                $postTitle = $post ? $post->title : 'a blog post';
                $postSlug = $post ? $post->slug : '';
                \App\Services\NotificationService::notifyUser(
                    $comment->user_id,
                    'comment_like',
                    'Comment Liked',
                    "{$user->name} liked your comment on \"{$postTitle}\".",
                    $postSlug ? route('blog.show', $postSlug) . '#comments' : null,
                    'like'
                );
            }
        }

        return response()->json([
            'success' => true,
            'liked'   => $liked,
            'count'   => $comment->likes()->count(),
        ]);
    }

    public function deleteComment(Request $request, BlogComment $comment)
    {
        abort_unless(auth()->check() && $comment->user_id === auth()->id(), 403, 'Unauthorized action.');

        $post = $comment->post;
        $parentId = $comment->parent_id;

        $comment->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'     => true,
                'message'     => 'Comment deleted successfully.',
                'comment_id'  => $comment->id,
                'parent_id'   => $parentId,
                'total_count' => $post ? $post->approvedComments()->count() : 0,
            ]);
        }

        return back()->with('success', 'Comment deleted successfully.');
    }
}

