<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class BlogPostController extends Controller
{
    public function __construct(protected ImageService $imageService) {}

    public function index()
    {
        $posts = BlogPost::with('category')->latest()->get();
        return view('admin.blog-posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = BlogCategory::where('is_active', true)->get();
        return view('admin.blog-posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'cover_image'      => 'nullable|image|max:5120',
            'content'          => 'nullable|string',
            'excerpt'          => 'nullable|string|max:500',
            'is_published'     => 'nullable|boolean',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:300',
        ]);

        $data['slug']         = Str::slug($data['title']);
        $data['content']      = isset($data['content']) ? Purifier::clean($data['content']) : null;
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->imageService->store($request->file('cover_image'), 'blog');
        }

        BlogPost::create($data);
        return redirect()->route('admin.blog-posts.index')->with('success', 'Blog post created.');
    }

    public function edit(BlogPost $blogPost)
    {
        $categories = BlogCategory::where('is_active', true)->get();
        return view('admin.blog-posts.edit', compact('blogPost', 'categories'));
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'cover_image'      => 'nullable|image|max:5120',
            'content'          => 'nullable|string',
            'excerpt'          => 'nullable|string|max:500',
            'is_published'     => 'nullable|boolean',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:300',
        ]);

        $data['content']      = isset($data['content']) ? Purifier::clean($data['content']) : null;
        $data['is_published'] = $request->boolean('is_published');
        if ($data['is_published'] && !$blogPost->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('cover_image')) {
            $this->imageService->delete($blogPost->cover_image);
            $data['cover_image'] = $this->imageService->store($request->file('cover_image'), 'blog');
        }

        $blogPost->update($data);
        return redirect()->route('admin.blog-posts.index')->with('success', 'Blog post updated.');
    }

    public function destroy(BlogPost $blogPost)
    {
        $this->imageService->delete($blogPost->cover_image);
        $blogPost->delete();
        return redirect()->route('admin.blog-posts.index')->with('success', 'Blog post deleted.');
    }
}

