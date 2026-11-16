@extends('users.layouts.app')
@section('title', 'My Notifications')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8">
    @include('users.partials.dashboard-sidebar')

    <main class="flex-1 min-w-0">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span>Notifications</span>
                    @if($unreadCount > 0)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-100 text-brand-800 dark:bg-brand-900/50 dark:text-brand-300">
                            {{ $unreadCount }} New
                        </span>
                    @endif
                </h1>
                <p class="text-xs text-gray-500 mt-1">Updates about your order status, replies to your comments, and account alerts.</p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-brand-700 hover:bg-brand-800 text-white transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Mark All Read
                    </button>
                </form>
                @endif

                @if($totalCount > 0)
                <form method="POST" action="{{ route('notifications.clear') }}" onsubmit="return confirm('Clear all notifications?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-cream-100 hover:bg-red-50 hover:text-red-600 dark:bg-gray-800 dark:hover:bg-red-950/40 dark:hover:text-red-400 text-gray-600 dark:text-gray-300 transition">
                        Clear All
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Filter Pills --}}
        <div class="flex items-center gap-2 mb-6">
            <a href="{{ route('user.notifications', request()->except('status')) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ empty(request('status')) ? 'bg-brand-700 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-cream-200 dark:border-gray-700 hover:bg-cream-50' }}">
               All ({{ $totalCount }})
            </a>
            <a href="{{ route('user.notifications', array_merge(request()->except('status'), ['status' => 'unread'])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'unread' ? 'bg-brand-700 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-cream-200 dark:border-gray-700 hover:bg-cream-50' }}">
               Unread ({{ $unreadCount }})
            </a>
            <a href="{{ route('user.notifications', array_merge(request()->except('status'), ['status' => 'read'])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'read' ? 'bg-brand-700 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-cream-200 dark:border-gray-700 hover:bg-cream-50' }}">
               Read
            </a>
        </div>

        {{-- Notifications List --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-cream-200 dark:border-gray-700 overflow-hidden shadow-sm divide-y divide-cream-100 dark:divide-gray-700">
            @forelse($notifications as $n)
            @php
                $isUnread = is_null($n->read_at);
                $type = $n->data['type'] ?? 'general';
                $title = $n->data['title'] ?? 'Notification';
                $message = $n->data['message'] ?? '';
                $url = $n->data['url'] ?? null;
            @endphp
            <div class="p-4 sm:p-5 hover:bg-cream-50/50 dark:hover:bg-gray-700/30 transition flex items-start gap-4 {{ $isUnread ? 'bg-brand-50/30 dark:bg-brand-950/20' : '' }}">
                {{-- Icon --}}
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 text-base shadow-sm {{ $isUnread ? 'bg-brand-700 text-white' : 'bg-cream-100 dark:bg-gray-700 text-gray-500' }}">
                    @if(str_contains($type, 'order'))
                        <span>📦</span>
                    @elseif(str_contains($type, 'like'))
                        <span>❤️</span>
                    @elseif(str_contains($type, 'reply') || str_contains($type, 'comment'))
                        <span>💬</span>
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
                                <span class="w-2 h-2 rounded-full bg-brand-700 shrink-0"></span>
                            @endif
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap">
                            {{ $n->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed mb-3">
                        {{ $message }}
                    </p>

                    <div class="flex items-center gap-3">
                        @if($url)
                            <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 dark:text-brand-400 hover:text-brand-800 transition">
                                    <span>View Details</span>
                                    <span>→</span>
                                </button>
                            </form>
                        @endif

                        @if($isUnread && !$url)
                            <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium">
                                    Mark as Read
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Delete Button --}}
                <form method="POST" action="{{ route('notifications.destroy', $n->id) }}" class="shrink-0" onsubmit="return confirm('Delete this notification?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-cream-100 dark:hover:bg-gray-700 transition" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
            @empty
            <div class="text-center py-16 px-4">
                <div class="w-14 h-14 rounded-2xl bg-cream-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <p class="font-bold text-gray-900 dark:text-white text-sm">No notifications found</p>
                <p class="text-xs text-gray-400 mt-1">You're all caught up with your latest updates.</p>
            </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
        <div class="pt-4">
            {{ $notifications->links() }}
        </div>
        @endif
    </main>
</div>
@endsection
