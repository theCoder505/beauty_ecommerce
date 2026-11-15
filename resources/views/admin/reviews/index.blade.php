@extends('admin.layouts.app')
@section('title', 'Reviews')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Product Reviews</h1>
            <p class="text-sm text-gray-500 mt-1">Moderate customer ratings, testimonials, and verified buyer reviews.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search review text or reviewer..." class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
            </div>
            <div>
                <select name="status" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="rating" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <option value="">All Ratings</option>
                    @for($r = 5; $r >= 1; $r--)
                        <option value="{{ $r }}" {{ request('rating') == $r ? 'selected' : '' }}>{{ $r }} Stars</option>
                    @endfor
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">Filter</button>
                @if(request()->anyFilled(['search', 'status', 'rating', 'product_id']))
                    <a href="{{ route('admin.reviews.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm transition flex items-center justify-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Reviews Table -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4">Product</th>
                        <th class="px-6 py-4">Reviewer</th>
                        <th class="px-6 py-4">Rating</th>
                        <th class="px-6 py-4">Review & Photos</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($reviews as $rev)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900 dark:text-white block">{{ $rev->product?->name ?? 'Deleted Product' }}</span>
                            <span class="text-xs text-gray-400">{{ $rev->created_at->format('M j, Y') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900 dark:text-white block">{{ $rev->user?->name ?? 'Guest User' }}</span>
                            <span class="text-xs text-gray-500">{{ $rev->user?->email }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center text-amber-500 text-xs">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= $rev->rating ? '★' : '☆' }}</span>
                                @endfor
                                <span class="ml-1.5 font-bold text-gray-900 dark:text-white">({{ $rev->rating }})</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 max-w-sm">
                            <p class="text-gray-700 dark:text-gray-300 text-xs line-clamp-3 leading-relaxed">{{ $rev->body }}</p>
                            @if($rev->images_count > 0)
                                <span class="inline-flex items-center gap-1 text-[11px] text-brand-600 mt-1 font-medium">
                                    📷 {{ $rev->images_count }} customer photo(s)
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($rev->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400">Approved</span>
                            @elseif($rev->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">Hidden</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-1 whitespace-nowrap">
                            @if($rev->status !== 'approved')
                            <form action="{{ route('admin.reviews.approve', $rev) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium text-green-700 hover:text-green-800 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/40 rounded-lg transition">Approve</button>
                            </form>
                            @endif
                            @if($rev->status !== 'hidden')
                            <form action="{{ route('admin.reviews.hide', $rev) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium text-gray-700 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-lg transition">Hide</button>
                            </form>
                            @endif
                            <form action="{{ route('admin.reviews.destroy', $rev) }}" method="POST" class="inline" onsubmit="return confirm('Delete this review permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 rounded-lg transition">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No reviews found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

