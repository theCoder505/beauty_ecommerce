@php
    $isReply = $isReply ?? false;
    $likedByAuth = auth()->check() && $comment->isLikedBy();
    $likesCount = $comment->likes ? $comment->likes->count() : 0;
    $isOwner = auth()->check() && auth()->id() === $comment->user_id;
@endphp

<div id="comment-{{ $comment->id }}"
    class="comment-item {{ $isReply ? 'comment-reply flex items-start gap-1.5 mt-1' : 'flex items-start gap-2' }}"
    data-id="{{ $comment->id }}">
    {{-- User Avatar --}}
    <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}"
        class="{{ $isReply ? 'w-5 h-5' : 'w-6 h-6' }} rounded-full object-cover shrink-0 ring-1 ring-cream-300 dark:ring-gray-700 mt-0.5">

    <div class="flex-1 min-w-0">
        {{-- Facebook-style compact minimal bubble --}}
        <div
            class="relative inline-block max-w-[94%] sm:max-w-[88%] bg-cream-100/90 dark:bg-gray-800/90 text-gray-900 dark:text-gray-100 rounded-xl px-2.5 py-1 shadow-2xs border border-cream-200/50 dark:border-gray-700/50">
            <span class="font-bold text-[11px] text-gray-900 dark:text-white block leading-tight cursor-pointer">
                {{ $comment->user->name }}
            </span>
            <div
                class="text-[11px] sm:text-xs text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-words leading-snug mt-0.5">
                {{ $comment->body }}
            </div>

            {{-- Floating Like Badge (Facebook style) --}}
            <span id="comment-like-badge-{{ $comment->id }}"
                class="comment-like-badge absolute -bottom-1 right-1 {{ $likesCount > 0 ? 'inline-flex' : 'hidden' }} items-center gap-0.5 bg-white dark:bg-gray-700 text-teal-700 dark:text-teal-300 text-[8px] font-bold px-1 py-0 rounded-full shadow-2xs border border-cream-200 dark:border-gray-600 select-none">
                <svg class="w-2 h-2 fill-current text-teal-600 dark:text-teal-400" viewBox="0 0 20 20">
                    <path
                        d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                </svg>
                <span class="like-counter">{{ $likesCount }}</span>
            </span>
        </div>

        {{-- Action Bar --}}
        <div
            class="mt-2 flex items-center gap-2 text-[10px] font-semibold text-gray-500 dark:text-gray-400 mt-0.5 pl-1 leading-none select-none">
            @auth
                <button type="button" onclick="handleCommentLike({{ $comment->id }}, this)"
                    class="comment-like-btn transition hover:underline {{ $likedByAuth ? 'text-teal-600 dark:text-teal-400 font-bold' : 'text-gray-500 dark:text-gray-400' }}">
                    Like
                </button>
                <button type="button"
                    onclick="setReplyTarget({{ $comment->parent_id ?: $comment->id }}, '{{ addslashes($comment->user->name) }}')"
                    class="transition hover:underline hover:text-gray-800 dark:hover:text-gray-200">
                    Reply
                </button>
                @if ($isOwner)
                    <button type="button" onclick="handleDeleteComment({{ $comment->id }})"
                        class="comment-del-btn transition hover:underline text-rose-500 hover:text-rose-600 dark:text-rose-400">
                        Delete
                    </button>
                @endif
            @else
                <button type="button" onclick="promptGuestLogin()"
                    class="comment-like-btn transition hover:underline text-gray-500 dark:text-gray-400">
                    Like
                </button>
                <button type="button" onclick="promptGuestLogin()"
                    class="transition hover:underline hover:text-gray-800 dark:hover:text-gray-200">
                    Reply
                </button>
            @endauth

            <span class="text-[9px] text-gray-400 dark:text-gray-500 font-normal">
                {{ $comment->created_at ? $comment->created_at->diffForHumans(null, true) : 'just now' }}
            </span>
        </div>

        {{-- Nested Replies (If top-level comment) --}}
        @if (!$isReply)
            <div class="replies-wrapper mt-1 pl-2.5 sm:pl-3.5 border-l border-cream-300 dark:border-gray-800 ml-2 space-y-1"
                id="replies-container-{{ $comment->id }}">
                @if (isset($comment->replies) && $comment->replies->isNotEmpty())
                    @foreach ($comment->replies as $reply)
                        @include('users.pages.partials.comment-item', [
                            'comment' => $reply,
                            'isReply' => true,
                            'post' => $post,
                        ])
                    @endforeach
                @endif
            </div>
        @endif
    </div>
</div>
