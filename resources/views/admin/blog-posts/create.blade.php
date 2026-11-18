@extends('admin.layouts.app')
@section('title', 'Write Blog Post')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Write Blog Post</h1>
            <p class="text-sm text-gray-500 mt-1">Publish an editorial story or beauty guide</p>
        </div>
        <a href="{{ route('admin.blog-posts.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            &larr; Back to Posts
        </a>
    </div>

    @if($errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl p-4 text-sm">
        @foreach($errors->all() as $err)
            <p>{{ $err }}</p>
        @endforeach
    </div>
    @endif

    <form action="{{ route('admin.blog-posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- General Info -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-4 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Article Info</h3>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g., The Alchemy of Night Serums" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="blog_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <select name="blog_category_id" id="blog_category_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('blog_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="is_published" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Publish Status</label>
                    <select name="is_published" id="is_published" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                        <option value="1" {{ old('is_published', '1') == '1' ? 'selected' : '' }}>Published (Live immediately)</option>
                        <option value="0" {{ old('is_published') === '0' ? 'selected' : '' }}>Draft (Hidden)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cover Image</label>
                <div class="mb-3">
                    <img id="blog-cover-preview" src="#" alt="Cover Preview" class="hidden h-44 w-full sm:w-80 object-cover rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                </div>
                <input type="file" name="cover_image" accept="image/*" data-preview="#blog-cover-preview" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300 cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">Recommended: 1200x630px JPG/WebP</p>
            </div>

            <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Excerpt / Summary</label>
                <textarea name="excerpt" id="excerpt" rows="2" placeholder="Brief 1-2 sentence teaser shown on blog listings..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">{{ old('excerpt') }}</textarea>
            </div>

            <div>
                <x-rich-editor
                    name="content"
                    id="blog-post-content"
                    :value="old('content')"
                    label="Article Content"
                    placeholder="Write your full editorial article with rich formatting, headings, quotes, and images..."
                    height="380px"
                />
            </div>
        </div>

        <!-- SEO -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-4 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">SEO Optimization</h3>

            <div>
                <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title</label>
                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" placeholder="SEO Title" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
            </div>

            <div>
                <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                <textarea name="meta_description" id="meta_description" rows="2" placeholder="SEO Description" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">{{ old('meta_description') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.blog-posts.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold shadow-sm transition">Save Post</button>
        </div>
    </form>
</div>
@endsection
