<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = BlogComment::with(['user', 'post', 'parent.user'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->get();
        return view('admin.blog-comments.index', compact('comments'));
    }

    public function approve(BlogComment $comment)
    {
        $comment->update(['status' => 'approved']);

        // If approving a parent, re-approve its replies that were hidden with it
        $comment->allReplies()->where('status', 'hidden')->update(['status' => 'approved']);

        // If approving a reply whose parent is not approved, approve the parent as well
        if ($comment->parent_id && $comment->parent && $comment->parent->status !== 'approved') {
            $comment->parent->update(['status' => 'approved']);
        }

        return back()->with('success', 'Comment approved.');
    }

    public function hide(BlogComment $comment)
    {
        $comment->update(['status' => 'hidden']);
        $comment->allReplies()->update(['status' => 'hidden']);

        return back()->with('success', 'Comment and its replies hidden.');
    }

    public function destroy(BlogComment $comment)
    {
        $comment->allReplies()->delete();
        $comment->delete();
        return back()->with('success', 'Comment and its replies deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:blog_comments,id',
        ]);

        BlogComment::whereIn('parent_id', $request->ids)->delete();
        $count = BlogComment::whereIn('id', $request->ids)->delete();

        return back()->with('success', "{$count} " . ($count === 1 ? 'comment' : 'comments') . ' deleted successfully.');
    }
}

