@extends('admin.layouts.app')
@section('title', 'Blog Comments')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Blog Comments</h1>
            <p class="text-sm text-gray-500 mt-1">Moderate reader discussions and community feedback.</p>
        </div>
    </div>

    <!-- Comments Table -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto p-4">
            <table id="comments-datatable" class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4">Article</th>
                        <th class="px-6 py-4">Author</th>
                        <th class="px-6 py-4">Comment</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($comments as $comment)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900 dark:text-white block">{{ $comment->post?->title ?? 'Deleted Article' }}</span>
                            <span class="text-xs text-gray-400">{{ $comment->created_at->format('M j, Y H:i') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900 dark:text-white block">{{ $comment->user?->name ?? 'Reader' }}</span>
                            <span class="text-xs text-gray-500">{{ $comment->user?->email }}</span>
                        </td>
                        <td class="px-6 py-4 max-w-sm">
                            <p class="text-gray-700 dark:text-gray-300 text-xs line-clamp-3 leading-relaxed">{{ $comment->content }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($comment->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400">Approved</span>
                            @elseif($comment->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">Hidden</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-1 whitespace-nowrap">
                            @if($comment->status !== 'approved')
                            <form action="{{ route('admin.blog-comments.approve', $comment) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium text-green-700 hover:text-green-800 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/40 rounded-lg transition">Approve</button>
                            </form>
                            @endif
                            @if($comment->status !== 'hidden')
                            <form action="{{ route('admin.blog-comments.hide', $comment) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium text-gray-700 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-lg transition">Hide</button>
                            </form>
                            @endif
                            <form action="{{ route('admin.blog-comments.destroy', $comment) }}" method="POST" class="inline" data-confirm="Delete this comment permanently?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 rounded-lg transition">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No comments submitted yet.
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
        $('#comments-datatable').DataTable({
            pageLength: 10,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [2, 4] }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search comments...",
                paginate: { previous: "‹ Prev", next: "Next ›" }
            }
        });
    });
</script>
@endpush

