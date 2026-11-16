@extends('admin.layouts.app')
@section('title', 'Admin Notifications')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>Notifications Center</span>
                @if($unreadCount > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-100 text-brand-800 dark:bg-brand-900/50 dark:text-brand-300">
                        {{ $unreadCount }} Unread
                    </span>
                @endif
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Real-time activity log for new orders, reviews, comments, replies, likes, and subscribers.
            </p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            @if($unreadCount > 0)
            <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-brand-700 hover:bg-brand-800 text-white transition flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Mark All Read
                </button>
            </form>
            @endif

            @if($totalCount > 0)
            <form method="POST" action="{{ route('admin.notifications.clear') }}" onsubmit="return confirm('Clear all notifications?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-red-50 hover:text-red-600 dark:bg-gray-800 dark:hover:bg-red-950/40 dark:hover:text-red-400 text-gray-600 dark:text-gray-300 transition">
                    Clear All
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
        {{-- Status Filter Pills --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.notifications.index', request()->except('status')) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ empty(request('status')) ? 'bg-brand-700 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200' }}">
               All ({{ $totalCount }})
            </a>
            <a href="{{ route('admin.notifications.index', array_merge(request()->except('status'), ['status' => 'unread'])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'unread' ? 'bg-brand-700 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200' }}">
               Unread ({{ $unreadCount }})
            </a>
            <a href="{{ route('admin.notifications.index', array_merge(request()->except('status'), ['status' => 'read'])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'read' ? 'bg-brand-700 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200' }}">
               Read
            </a>
        </div>

        {{-- Type Filter Dropdown --}}
        <form method="GET" action="{{ route('admin.notifications.index') }}" class="flex items-center gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <select name="type" onchange="this.form.submit()"
                class="px-3 py-1.5 text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">All Activity Types</option>
                <option value="order" {{ request('type') === 'order' ? 'selected' : '' }}>Orders</option>
                <option value="review" {{ request('type') === 'review' ? 'selected' : '' }}>Product Reviews</option>
                <option value="comment" {{ request('type') === 'comment' ? 'selected' : '' }}>Comments & Replies</option>
                <option value="like" {{ request('type') === 'like' ? 'selected' : '' }}>Likes</option>
                <option value="subscriber" {{ request('type') === 'subscriber' ? 'selected' : '' }}>Newsletter Subscribers</option>
                <option value="contact" {{ request('type') === 'contact' ? 'selected' : '' }}>Contact Messages</option>
            </select>
            @if(request()->anyFilled(['type', 'status']))
                <a href="{{ route('admin.notifications.index') }}" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">Reset</a>
            @endif
        </form>
    </div>

    {{-- Notifications List --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($notifications as $n)
        @php
            $isUnread = is_null($n->read_at);
            $type = $n->data['type'] ?? 'general';
            $title = $n->data['title'] ?? 'Notification';
            $message = $n->data['message'] ?? '';
            $url = $n->data['url'] ?? null;
        @endphp
        <div class="p-4 sm:p-5 hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition flex items-start gap-4 {{ $isUnread ? 'bg-brand-50/20 dark:bg-brand-950/10' : '' }}">
            {{-- Icon --}}
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 text-base shadow-sm {{ $isUnread ? 'bg-brand-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }}">
                @if(str_contains($type, 'order'))
                    <span>📦</span>
                @elseif(str_contains($type, 'review'))
                    <span>⭐</span>
                @elseif(str_contains($type, 'like'))
                    <span>❤️</span>
                @elseif(str_contains($type, 'reply') || str_contains($type, 'comment'))
                    <span>💬</span>
                @elseif(str_contains($type, 'subscribe'))
                    <span>✉️</span>
                @elseif(str_contains($type, 'contact'))
                    <span>📩</span>
                @else
                    <span>🔔</span>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $title }}</h3>
                        @if($isUnread)
                            <span class="w-2 h-2 rounded-full bg-brand-600 shrink-0"></span>
                            <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-brand-100 text-brand-800 dark:bg-brand-900/50 dark:text-brand-300">NEW</span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 whitespace-nowrap">
                        {{ $n->created_at->format('M d, Y h:i A') }} ({{ $n->created_at->diffForHumans() }})
                    </span>
                </div>

                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed mb-3">
                    {{ $message }}
                </p>

                <div class="flex items-center gap-3">
                    @if($url)
                        <form method="POST" action="{{ route('admin.notifications.read', $n->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition">
                                <span>View Details</span>
                                <span>→</span>
                            </button>
                        </form>
                    @endif

                    @if($isUnread && !$url)
                        <form method="POST" action="{{ route('admin.notifications.read', $n->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium">
                                Mark as Read
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Delete Button --}}
            <form method="POST" action="{{ route('admin.notifications.destroy', $n->id) }}" class="shrink-0" onsubmit="return confirm('Delete this notification?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </form>
        </div>
        @empty
        <div class="text-center py-16 px-4">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <p class="font-bold text-gray-900 dark:text-white text-sm">No notifications found</p>
            <p class="text-xs text-gray-400 mt-1">You are all caught up!</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="pt-2">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection
