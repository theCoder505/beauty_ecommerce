@extends('admin.layouts.app')
@section('title', 'Newsletter Subscribers')
@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Newsletter Audience</h1>
                <p class="text-sm text-gray-500 mt-1">Customers subscribed to product drops, secret sales, and VIP events.</p>
            </div>

            {{-- Bulk Actions --}}
            <div>
                <form id="bulk-delete-form" action="{{ route('admin.subscribers.bulk-delete') }}" method="POST" onsubmit="return handleBulkDelete(event)">
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

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto p-4">
                <table id="subscribers-datatable" class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/60 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                            <th class="w-10 px-4 py-4 text-center">
                                <input type="checkbox" id="select-all-subscribers" class="rounded border-gray-300 dark:border-gray-700 text-teal-600 focus:ring-teal-500 cursor-pointer" title="Select All">
                            </th>
                            <th class="px-6 py-4">Subscriber Email</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Subscribed Date</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($subscribers as $sub)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                                <td class="w-10 px-4 py-4 text-center">
                                    <input type="checkbox" name="ids[]" form="bulk-delete-form" value="{{ $sub->id }}" class="subscriber-row-checkbox rounded border-gray-300 dark:border-gray-700 text-teal-600 focus:ring-teal-500 cursor-pointer">
                                </td>
                                <td class="px-6 py-4 font-mono font-medium text-gray-900 dark:text-white">
                                    {{ $sub->email }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($sub->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Subscribed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            Unsubscribed
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    {{ $sub->created_at->format('M j, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <form action="{{ route('admin.subscribers.destroy', $sub) }}" method="POST" class="inline" data-confirm="Delete this subscriber from the newsletter audience?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 rounded-lg transition" title="Delete Subscriber" aria-label="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
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

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#subscribers-datatable tbody tr td[colspan]').closest('tr').remove();
                const table = $('#subscribers-datatable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    order: [
                        [3, 'desc']
                    ],
                    columnDefs: [
                        { orderable: false, targets: [0, 4] }
                    ],
                    language: {
                        emptyTable: "No newsletter subscribers recorded yet.",
                        zeroRecords: "No matching subscribers found.",
                        search: "_INPUT_",
                        searchPlaceholder: "Search subscribers...",
                        paginate: {
                            previous: "‹ Prev",
                            next: "Next ›"
                        }
                    }
                });

                // Select all rows across the DataTable
                $('#select-all-subscribers').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    table.$('.subscriber-row-checkbox').prop('checked', isChecked);
                    updateBulkDeleteState();
                });

                // Individual row checkbox change
                $('#subscribers-datatable').on('change', '.subscriber-row-checkbox', function() {
                    const total = table.$('.subscriber-row-checkbox').length;
                    const checked = table.$('.subscriber-row-checkbox:checked').length;
                    $('#select-all-subscribers').prop('checked', total > 0 && checked === total);
                    updateBulkDeleteState();
                });

                // Synchronize checkbox state on table redraw (pagination, search, etc.)
                table.on('draw', function() {
                    const total = table.$('.subscriber-row-checkbox').length;
                    const checked = table.$('.subscriber-row-checkbox:checked').length;
                    $('#select-all-subscribers').prop('checked', total > 0 && checked === total);
                    updateBulkDeleteState();
                });

                function updateBulkDeleteState() {
                    const count = table.$('.subscriber-row-checkbox:checked').length;
                    $('#selected-count').text(count);
                    $('#bulk-delete-btn').prop('disabled', count === 0);
                }

                window.handleBulkDelete = function(e) {
                    e.preventDefault();
                    const checkedBoxes = table.$('.subscriber-row-checkbox:checked');
                    const count = checkedBoxes.length;
                    if (count === 0) return false;

                    const confirmMsg = `Are you sure you want to delete ${count} selected subscriber${count === 1 ? '' : 's'} from the audience?`;

                    const proceed = () => {
                        const form = document.getElementById('bulk-delete-form');
                        $(form).find('input[name="ids[]"]').remove();
                        checkedBoxes.each(function() {
                            $(form).append($('<input>').attr({
                                type: 'hidden',
                                name: 'ids[]',
                                value: $(this).val()
                            }));
                        });
                        form.submit();
                    };

                    if (window.Swal) {
                        const isDark = document.documentElement.classList.contains('dark');
                        Swal.fire({
                            title: 'Confirm Bulk Deletion',
                            text: confirmMsg,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc2626',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Yes, delete',
                            cancelButtonText: 'Cancel',
                            background: isDark ? '#111827' : '#ffffff',
                            color: isDark ? '#f3f4f6' : '#111827',
                            customClass: {
                                popup: 'rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                proceed();
                            }
                        });
                    } else {
                        if (confirm(confirmMsg)) {
                            proceed();
                        }
                    }
                    return false;
                };
            });
        </script>
    @endpush
@endsection
