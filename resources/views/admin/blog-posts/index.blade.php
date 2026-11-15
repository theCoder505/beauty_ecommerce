@extends('admin.layouts.app')
@section('title', 'Blog Posts')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Blog Articles & Stories</h1>
            <p class="text-sm text-gray-500 mt-1">Manage fragrance guides, skincare routines, and editorial articles.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.blog-categories.index') }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold px-4 py-2.5 rounded-xl transition">
                Categories
            </a>
            <a href="{{ route('admin.blog-posts.create') }}" class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Write New Post
            </a>
        </div>
    </div>

    <!-- Posts Table -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto p-4">
            <table id="posts-datatable" class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4">Article</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Published Date</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($posts as $post)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 bg-gray-50 shrink-0">
                                <div class="min-w-0">
                                    <span class="font-semibold text-gray-900 dark:text-white truncate block max-w-sm">{{ $post->title }}</span>
                                    <span class="text-xs text-gray-400 font-mono">{{ $post->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                            {{ $post->category?->name ?? 'Uncategorized' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($post->is_published)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Published
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500">
                            {{ $post->published_at ? $post->published_at->format('M j, Y') : '—' }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 rounded-lg transition">View ↗</a>
                            <a href="{{ route('admin.blog-posts.edit', $post) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-brand-700 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 dark:bg-brand-900/20 dark:hover:bg-brand-900/40 rounded-lg transition">Edit</a>
                            <form action="{{ route('admin.blog-posts.destroy', $post) }}" method="POST" class="inline" data-confirm="Delete this blog post?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 rounded-lg transition">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No articles found. Click "Write New Post" to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#posts-datatable').DataTable({
            pageLength: 10,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [0, 4] }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search posts...",
                paginate: { previous: "‹ Prev", next: "Next ›" }
            }
        });
    });
</script>
@endpush

