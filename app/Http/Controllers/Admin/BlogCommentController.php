<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = BlogComment::with(['user', 'post'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(20);
        return view('admin.blog-comments.index', compact('comments'));
    }

    public function approve(BlogComment $comment)
    {
        $comment->update(['status' => 'approved']);
        return back()->with('success', 'Comment approved.');
    }

    public function hide(BlogComment $comment)
    {
        $comment->update(['status' => 'hidden']);
        return back()->with('success', 'Comment hidden.');
    }

    public function destroy(BlogComment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Comment deleted.');
    }
}

