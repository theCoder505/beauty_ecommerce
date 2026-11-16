@extends('admin.layouts.app')
@section('title', 'Blog Comments')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Blog Comments</h1>
            <p class="text-sm text-gray-500 mt-1">Moderate reader discussions and community feedback.</p>
        </div>

        {{-- Bulk Actions --}}
        <div>
            <form id="bulk-delete-form" action="{{ route('admin.blog-comments.bulk-delete') }}" method="POST" onsubmit="return handleBulkDelete(event)">
                @csrf
                <button type="submit" id="bulk-delete-btn" disabled
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl bg-red-50 text-red-700 hover:bg-red-600 hover:text-white dark:bg-red-950/40 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white border border-red-200 dark:border-red-900 transition shadow-xs disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Bulk Delete (<span id="selected-count">0</span>)</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Comments Table -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto p-4">
            <table id="comments-datatable" class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                        <th class="w-10 px-4 py-4 text-center">
                            <input type="checkbox" id="select-all-comments" class="rounded border-gray-300 dark:border-gray-700 text-teal-600 focus:ring-teal-500 cursor-pointer" title="Select All">
                        </th>
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
                        <td class="w-10 px-4 py-4 text-center">
                            <input type="checkbox" name="ids[]" form="bulk-delete-form" value="{{ $comment->id }}" class="comment-row-checkbox rounded border-gray-300 dark:border-gray-700 text-teal-600 focus:ring-teal-500 cursor-pointer">
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900 dark:text-white block">{{ $comment->post?->title ?? 'Deleted Article' }}</span>
                            <span class="text-xs text-gray-400">{{ $comment->created_at->format('M j, Y H:i') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900 dark:text-white block">{{ $comment->user?->name ?? 'Reader' }}</span>
                            <span class="text-xs text-gray-500">{{ $comment->user?->email }}</span>
                        </td>
                        <td class="px-6 py-4 max-w-sm">
                            @if($comment->parent_id)
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-teal-700 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/40 px-2 py-0.5 rounded-md mb-1 border border-teal-200 dark:border-teal-900">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    Reply to {{ $comment->parent?->user?->name ?? 'Comment #' . $comment->parent_id }}
                                </span>
                            @endif
                            <p class="text-gray-700 dark:text-gray-300 text-xs line-clamp-3 leading-relaxed">{{ $comment->body }}</p>
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
                        <td class="px-6 py-4 text-right space-x-1.5 whitespace-nowrap">
                            @if($comment->status !== 'approved')
                            <form action="{{ route('admin.blog-comments.approve', $comment) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-green-700 hover:text-green-800 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/40 rounded-lg transition" title="Approve Comment" aria-label="Approve">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </form>
                            @endif
                            @if($comment->status !== 'hidden')
                            <form action="{{ route('admin.blog-comments.hide', $comment) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-gray-700 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-lg transition" title="Hide Comment" aria-label="Hide">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.blog-comments.destroy', $comment) }}" method="POST" class="inline" data-confirm="Delete this comment permanently?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 rounded-lg transition" title="Delete Comment" aria-label="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
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
        $('#comments-datatable tbody tr td[colspan]').closest('tr').remove();
        const table = $('#comments-datatable').DataTable({
            pageLength: 10,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [0, 3, 5] }
            ],
            language: {
                emptyTable: "No comments submitted yet.",
                zeroRecords: "No matching comments found.",
                search: "_INPUT_",
                searchPlaceholder: "Search comments...",
                paginate: { previous: "‹ Prev", next: "Next ›" }
            }
        });

        // Select all rows
        $('#select-all-comments').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.comment-row-checkbox').prop('checked', isChecked);
            updateBulkDeleteState();
        });

        // Individual row change
        $(document).on('change', '.comment-row-checkbox', function() {
            const total = $('.comment-row-checkbox').length;
            const checked = $('.comment-row-checkbox:checked').length;
            $('#select-all-comments').prop('checked', total > 0 && checked === total);
            updateBulkDeleteState();
        });

        // Keep state synchronized across pagination redraws
        table.on('draw', function() {
            const total = $('.comment-row-checkbox').length;
            const checked = $('.comment-row-checkbox:checked').length;
            $('#select-all-comments').prop('checked', total > 0 && checked === total);
            updateBulkDeleteState();
        });

        function updateBulkDeleteState() {
            const count = $('.comment-row-checkbox:checked').length;
            $('#selected-count').text(count);
            $('#bulk-delete-btn').prop('disabled', count === 0);
        }
    });

    window.handleBulkDelete = function(e) {
        e.preventDefault();
        const count = $('.comment-row-checkbox:checked').length;
        if (count === 0) return false;

        const confirmMsg = `Are you sure you want to delete ${count} selected comment${count === 1 ? '' : 's'}? All nested replies will also be deleted.`;
        if (window.Swal) {
            Swal.fire({
                title: 'Confirm Bulk Deletion',
                text: confirmMsg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('bulk-delete-form').submit();
                }
            });
        } else {
            if (confirm(confirmMsg)) {
                document.getElementById('bulk-delete-form').submit();
            }
        }
        return false;
    };
</script>
@endpush
