@extends('users.layouts.app')
@section('title', 'My Notifications')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8">
    @include('users.partials.dashboard-sidebar')

    <main class="flex-1 min-w-0"
          x-data="{
              unreadCount: {{ $unreadCount }},
              readCount: {{ $readCount }},
              totalCount: {{ $totalCount }},
              filterStatus: '{{ request('status', '') }}',
              markedAllRead: false,
              markAllPageRead() {
                  this.unreadCount = 0;
                  this.readCount = this.totalCount;
                  this.markedAllRead = true;
              }
          }"
          @notifications-marked-all-read.window="markAllPageRead()">
        {{-- Flash Success Alert --}}
        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-200">&times;</button>
            </div>
        @endif

        {{-- Dynamic marked all read alert (when triggered from header) --}}
        <div x-show="markedAllRead" x-cloak x-transition
             class="mb-5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>All notifications marked as read.</span>
            </div>
            <button type="button" @click="markedAllRead = false" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-200">&times;</button>
        </div>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="font-serif text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span>Notifications</span>
                    <span x-show="unreadCount > 0"
                          x-text="unreadCount + ' Unread'"
                          class="notification-page-unread-badge px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-100 text-brand-800 dark:bg-slate-800 dark:text-slate-200 dark:border dark:border-slate-700 {{ $unreadCount > 0 ? '' : 'hidden' }}">
                        {{ $unreadCount }} Unread
                    </span>
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Manage all your order status alerts, interactions, and updates in one place.</p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                <div x-show="unreadCount > 0" class="notification-mark-all-read-form {{ $unreadCount > 0 ? '' : 'hidden' }}">
                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-brand-700 hover:bg-brand-800 text-white transition flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Mark All Read</span>
                        </button>
                    </form>
                </div>

                <div x-show="readCount > 0" class="notification-mark-all-unread-form {{ $readCount > 0 ? '' : 'hidden' }}">
                    <form method="POST" action="{{ route('notifications.mark-all-unread') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-stone-100 hover:bg-stone-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-stone-200 dark:border-gray-700 transition flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Mark All Unread</span>
                        </button>
                    </form>
                </div>

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
            <a href="{{ route('notifications.index', request()->except('status')) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ empty(request('status')) ? 'bg-brand-700 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-cream-200 dark:border-gray-700 hover:bg-cream-50 dark:hover:bg-gray-700' }}">
               <span x-text="'All (' + totalCount + ')'">All ({{ $totalCount }})</span>
            </a>
            <a href="{{ route('notifications.index', array_merge(request()->except('status'), ['status' => 'unread'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'unread' ? 'bg-brand-700 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-cream-200 dark:border-gray-700 hover:bg-cream-50 dark:hover:bg-gray-700' }}">
               <span class="notification-pill-unread" x-text="'Unread (' + unreadCount + ')'">Unread ({{ $unreadCount }})</span>
            </a>
            <a href="{{ route('notifications.index', array_merge(request()->except('status'), ['status' => 'read'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('status') === 'read' ? 'bg-brand-700 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-cream-200 dark:border-gray-700 hover:bg-cream-50 dark:hover:bg-gray-700' }}">
               <span class="notification-pill-read" x-text="'Read (' + readCount + ')'">Read ({{ $readCount }})</span>
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
            <div data-notification-id="{{ $n->id }}"
                 x-data="{ isUnread: {{ $isUnread ? 'true' : 'false' }} }"
                 @notifications-marked-all-read.window="isUnread = false"
                 @notification-marked-read.window="if ($event.detail && $event.detail.id == '{{ $n->id }}') isUnread = false"
                 x-show="!(filterStatus === 'unread' && !isUnread)"
                 x-transition
                 class="notification-card p-4 sm:p-5 transition flex items-start gap-4 {{ $isUnread ? 'bg-brand-50/40 dark:bg-slate-800/90 border-l-4 border-l-brand-600 dark:border-l-slate-400 hover:bg-brand-50/60 dark:hover:bg-slate-800' : 'border-l-4 border-l-transparent hover:bg-cream-50/50 dark:hover:bg-gray-700/30' }}"
                 :class="isUnread ? 'bg-brand-50/40 dark:bg-slate-800/90 border-l-4 border-l-brand-600 dark:border-l-slate-400 hover:bg-brand-50/60 dark:hover:bg-slate-800' : 'border-l-4 border-l-transparent hover:bg-cream-50/50 dark:hover:bg-gray-700/30'">
                {{-- Icon --}}
                <div class="notification-icon-box w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 text-base shadow-2xs {{ $isUnread ? 'bg-brand-700 dark:bg-slate-700 text-white dark:text-slate-100' : 'bg-cream-100 dark:bg-gray-700 text-gray-500' }}"
                     :class="isUnread ? 'bg-brand-700 dark:bg-slate-700 text-white dark:text-slate-100' : 'bg-cream-100 dark:bg-gray-700 text-gray-500'">
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
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $title }}</h3>
                            <span x-show="isUnread"
                                  class="notification-unread-badge inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-100 text-brand-800 dark:bg-slate-700 dark:text-slate-200 dark:border dark:border-slate-600 {{ $isUnread ? '' : 'hidden' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-600 dark:bg-slate-300 animate-pulse"></span>
                                Unread
                            </span>
                            <span x-show="!isUnread"
                                  class="notification-read-badge inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 {{ $isUnread ? 'hidden' : '' }}">
                                Read
                            </span>
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap">
                            {{ $n->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        @if($url)
                            <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="inline">
                                @csrf
                                <input type="hidden" name="redirect" value="1">
                                <button type="submit" class="notification-message-btn inline-flex items-center gap-1 text-xs text-brand-700 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 transition {{ $isUnread ? 'font-semibold' : '' }}"
                                        :class="isUnread ? 'font-semibold' : ''">
                                    <span>{{ $message }}</span>
                                </button>
                            </form>
                        @else
                            <span class="notification-message-text {{ $isUnread ? 'font-semibold' : '' }}" :class="isUnread ? 'font-semibold' : ''">{{ $message }}</span>
                        @endif
                    </p>
                </div>

                {{-- Delete Button --}}
                <form method="POST" action="{{ route('notifications.destroy', $n->id) }}" class="shrink-0" onsubmit="return confirm('Delete this notification?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-cream-100 dark:hover:bg-gray-700 transition" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
            @empty
            <div class="text-center py-16 px-4">
                <div class="w-14 h-14 rounded-2xl bg-cream-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <p class="font-bold text-gray-900 dark:text-white text-sm">
                    @if(request('status') === 'unread')
                        No unread notifications
                    @elseif(request('status') === 'read')
                        No read notifications
                    @else
                        No notifications found
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    @if(request('status') === 'unread')
                        You are all caught up! There are no unread notifications right now.
                    @elseif(request('status') === 'read')
                        You don't have any read notifications yet.
                    @else
                        You're all caught up with your latest updates.
                    @endif
                </p>
            </div>
            @endforelse

            {{-- Dynamic empty state for unread filter when marked all read --}}
            <div x-show="filterStatus === 'unread' && unreadCount === 0" x-cloak class="text-center py-16 px-4">
                <div class="w-14 h-14 rounded-2xl bg-cream-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <p class="font-bold text-gray-900 dark:text-white text-sm">No unread notifications</p>
                <p class="text-xs text-gray-400 mt-1">You are all caught up! There are no unread notifications right now.</p>
            </div>
        </div>

        @if($notifications->hasPages())
        <div class="pt-4" x-show="!(filterStatus === 'unread' && unreadCount === 0)">
            {{ $notifications->links() }}
        </div>
        @endif
    </main>
</div>

<script>
    window.addEventListener('notifications-marked-all-read', function() {
        document.querySelectorAll('.notification-card').forEach(function(card) {
            card.classList.remove('bg-brand-50/40', 'dark:bg-slate-800/90', 'border-l-brand-600', 'dark:border-l-slate-400', 'hover:bg-brand-50/60', 'dark:hover:bg-slate-800');
            card.classList.add('border-l-transparent', 'hover:bg-cream-50/50', 'dark:hover:bg-gray-700/30');
        });
        document.querySelectorAll('.notification-icon-box').forEach(function(icon) {
            icon.classList.remove('bg-brand-700', 'dark:bg-slate-700', 'text-white', 'dark:text-slate-100');
            icon.classList.add('bg-cream-100', 'dark:bg-gray-700', 'text-gray-500');
        });
        document.querySelectorAll('.notification-unread-badge').forEach(function(badge) {
            badge.style.display = 'none';
        });
        document.querySelectorAll('.notification-read-badge').forEach(function(badge) {
            badge.style.display = 'inline-flex';
            badge.classList.remove('hidden');
        });
        document.querySelectorAll('.notification-page-unread-badge').forEach(function(badge) {
            badge.style.display = 'none';
        });
        document.querySelectorAll('.notification-mark-all-read-form').forEach(function(f) {
            f.style.display = 'none';
        });
        document.querySelectorAll('.notification-mark-all-unread-form').forEach(function(f) {
            f.style.display = 'block';
            f.classList.remove('hidden');
        });
        document.querySelectorAll('.notification-message-btn, .notification-message-text').forEach(function(el) {
            el.classList.remove('font-semibold');
        });
    });
</script>
@endsection
