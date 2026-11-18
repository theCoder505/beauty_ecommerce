@extends('admin.layouts.app')
@section('title', 'Write Blog Post')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<style>
    .ql-toolbar.ql-snow {
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        border-color: #e5e7eb;
        background-color: #f9fafb;
    }
    .dark .ql-toolbar.ql-snow {
        border-color: #374151;
        background-color: #1f2937;
    }
    .dark .ql-toolbar .ql-stroke {
        stroke: #9ca3af;
    }
    .dark .ql-toolbar .ql-fill {
        fill: #9ca3af;
    }
    .dark .ql-toolbar .ql-picker {
        color: #9ca3af;
    }
    .dark .ql-toolbar .ql-picker-options {
        background-color: #1f2937;
        border-color: #374151;
    }
    .ql-container.ql-snow {
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
        border-color: #e5e7eb;
        font-family: inherit;
        font-size: 0.875rem;
    }
    .dark .ql-container.ql-snow {
        border-color: #374151;
        background-color: #111827;
        color: #f3f4f6;
    }
    .ql-editor {
        min-height: 280px;
    }
    .ql-editor.ql-blank::before {
        color: #9ca3af;
        font-style: normal;
    }
</style>
@endpush

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

    @if(isset($errors) && $errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl p-4 text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.blog-posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">Article Details</h3>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Post Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g. The Philosophy of Quiet Luxury Scents" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="blog_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Blog Category</label>
                    <select name="blog_category_id" id="blog_category_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('blog_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Publish Immediately</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cover Image</label>
                <div class="mb-3">
                    <img id="blog-cover-preview" class="hidden w-32 h-20 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shadow-sm preview-target mb-2" alt="Cover Preview">
                </div>
                <input type="file" name="cover_image" accept="image/*" data-preview="#blog-cover-preview" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300 cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">Recommended: 1200x630px JPG/WebP</p>
            </div>

            <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Excerpt / Summary</label>
                <textarea name="excerpt" id="excerpt" rows="2" placeholder="Brief 1-2 sentence teaser shown on blog listings..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">{{ old('excerpt') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Article Content</label>
                <div id="quill-editor" class="bg-white dark:bg-gray-900">{!! old('content') !!}</div>
                <input type="hidden" name="content" id="hidden-content" value="{{ old('content') }}">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Write your full editorial article with rich formatting, headings, and quotes...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote', 'link', 'image'],
                ['clean']
            ]
        }
    });

    // Custom Image Handler uploading to /admin/editor/upload-image
    const toolbar = quill.getModule('toolbar');
    if (toolbar) {
        toolbar.addHandler('image', function() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = async function() {
                const file = input.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('image', file);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const res = await fetch('{{ route('admin.editor.upload-image') }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    if (res.ok) {
                        const data = await res.json();
                        if (data.url) {
                            const range = quill.getSelection(true) || { index: quill.getLength() };
                            quill.insertEmbed(range.index, 'image', data.url);
                            quill.setSelection(range.index + 1);
                            return;
                        }
                    }
                    fallbackBase64(file);
                } catch (err) {
                    fallbackBase64(file);
                }
            };

            function fallbackBase64(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const range = quill.getSelection(true) || { index: quill.getLength() };
                    quill.insertEmbed(range.index, 'image', e.target.result);
                    quill.setSelection(range.index + 1);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    const hiddenInput = document.getElementById('hidden-content');
    
    quill.on('text-change', function() {
        hiddenInput.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
    });

    const form = hiddenInput.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            hiddenInput.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
        });
    }
});
</script>
@endpush
