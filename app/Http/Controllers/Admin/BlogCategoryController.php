<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Services\ImageService;
use App\Services\OtpService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

// ─── BlogCategoryController ────────────────────────────────────────────────────
class BlogCategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::withCount('posts')->latest()->paginate(20);
        return view('admin.blog-categories.index', compact('categories'));
    }
    public function create()
    {
        return view('admin.blog-categories.create');
    }
    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150']);
        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        BlogCategory::create($data);
        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category created.');
    }
    public function edit(BlogCategory $blogCategory)
    {
        return view('admin.blog-categories.edit', compact('blogCategory'));
    }
    public function update(Request $request, BlogCategory $blogCategory)
    {
        $data = $request->validate(['name' => 'required|string|max:150']);
        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $blogCategory->update($data);
        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category updated.');
    }
    public function destroy(BlogCategory $blogCategory)
    {
        $blogCategory->delete();
        return redirect()->route('admin.blog-categories.index')->with('success', 'Deleted.');
    }
}

