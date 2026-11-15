@extends('admin.layouts.app')
@section('title', 'Newsletter Subscribers')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Newsletter Audience</h1>
            <p class="text-sm text-gray-500 mt-1">Customers subscribed to product drops, secret sales, and VIP events.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto p-4">
            <table id="subscribers-datatable" class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4">Subscriber Email</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Subscribed Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($subscribers as $sub)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        <td class="px-6 py-4 font-mono font-medium text-gray-900 dark:text-white">
                            {{ $sub->email }}
                        </td>
                        <td class="px-6 py-4">
                            @if($sub->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Subscribed
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    Unsubscribed
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-xs text-gray-500">
                            {{ $sub->created_at->format('M j, Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                            No newsletter subscribers recorded yet.
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
        $('#subscribers-datatable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[2, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search subscribers...",
                paginate: { previous: "‹ Prev", next: "Next ›" }
            }
        });
    });
</script>
@endpush
@endsection

