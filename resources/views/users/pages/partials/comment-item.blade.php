@php
    $isReply = $isReply ?? false;
    $likedByAuth = auth()->check() && $comment->isLikedBy();
    $likesCount = $comment->likes ? $comment->likes->count() : 0;
    $isOwner = auth()->check() && auth()->id() === $comment->user_id;
@endphp

<div id="comment-{{ $comment->id }}"
    class="comment-item {{ $isReply ? 'comment-reply flex items-start gap-2 mt-2.5' : 'flex items-start gap-2.5 sm:gap-3' }}"
    data-id="{{ $comment->id }}">
    {{-- User Avatar --}}
    <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}"
        class="{{ $isReply ? 'w-6 h-6' : 'w-7 h-7 sm:w-8 sm:h-8' }} rounded-full object-cover shrink-0 ring-1 ring-cream-300 dark:ring-gray-700 mt-0.5">

    <div class="flex-1 min-w-0">
        {{-- Facebook-style compact bubble --}}
        <div
            class="relative inline-block max-w-[94%] sm:max-w-[88%] bg-cream-100/90 dark:bg-gray-800/90 text-gray-900 dark:text-gray-100 rounded-lg px-3 py-2 shadow-2xs border border-cream-200/50 dark:border-gray-700/50">
            <span class="font-bold text-xs text-gray-900 dark:text-white block leading-tight">
                {{ $comment->user->name }}
            </span>
            <div
                class="text-xs text-gray-800 dark:text-gray-200 break-words leading-relaxed mt-1">
                {{ $comment->body }}
            </div>
        </div>

        {{-- Action Bar --}}
        <div
            class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mt-1.5 pl-1 leading-none select-none">
            @auth
                <button type="button" onclick="handleCommentLike({{ $comment->id }}, this)"
                    class="comment-like-btn {{ $likedByAuth ? 'is-liked text-teal-600 dark:text-teal-400' : 'text-gray-400 dark:text-gray-500' }} hover:text-teal-600 dark:hover:text-teal-400 inline-flex items-center gap-1 transition-colors py-0.5"
                    title="Like" aria-label="Like">
                    <svg class="like-icon-outline w-3.5 h-3.5 {{ $likedByAuth ? 'hidden' : 'block' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                    </svg>
                    <svg class="like-icon-solid w-3.5 h-3.5 {{ $likedByAuth ? 'block' : 'hidden' }} fill-current transition-transform duration-150" viewBox="0 0 20 20">
                        <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                    </svg>
                    <span class="like-counter text-[11px] font-semibold leading-none {{ $likesCount > 0 ? '' : 'hidden' }}">{{ $likesCount > 0 ? $likesCount : '' }}</span>
                </button>

                <button type="button"
                    onclick="setReplyTarget({{ $comment->parent_id ?: $comment->id }}, '{{ addslashes($comment->user->name) }}')"
                    class="comment-reply-btn hover:text-teal-600 dark:hover:text-teal-400 text-gray-400 dark:text-gray-500 inline-flex items-center transition-colors py-0.5"
                    title="Reply to {{ $comment->user->name }}" aria-label="Reply">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </button>

                @if ($isOwner)
                    <button type="button" onclick="handleDeleteComment({{ $comment->id }})"
                        class="comment-del-btn hover:text-rose-600 dark:hover:text-rose-400 text-gray-400 dark:text-gray-500 inline-flex items-center transition-colors py-0.5"
                        title="Delete comment" aria-label="Delete">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                @endif
            @else
                <button type="button" onclick="promptGuestLogin('Sign in to like comments.')"
                    class="comment-like-btn hover:text-teal-600 dark:hover:text-teal-400 text-gray-400 dark:text-gray-500 inline-flex items-center gap-1 transition-colors py-0.5"
                    title="Like" aria-label="Like">
                    <svg class="like-icon-outline w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                    </svg>
                    <span class="like-counter text-[11px] font-semibold leading-none {{ $likesCount > 0 ? '' : 'hidden' }}">{{ $likesCount > 0 ? $likesCount : '' }}</span>
                </button>

                <button type="button" onclick="promptGuestLogin('Sign in to reply to comments.')"
                    class="comment-reply-btn hover:text-teal-600 dark:hover:text-teal-400 text-gray-400 dark:text-gray-500 inline-flex items-center transition-colors py-0.5"
                    title="Reply" aria-label="Reply">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </button>
            @endauth

            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-normal ml-0.5">
                {{ $comment->created_at ? $comment->created_at->diffForHumans(null, true) : 'just now' }}
            </span>
        </div>

        {{-- Nested Replies (If top-level comment) --}}
        @if (!$isReply)
            <div class="replies-wrapper mt-3 pl-3 sm:pl-4 border-l-2 border-cream-200 dark:border-gray-800 ml-2.5 sm:ml-3 space-y-3"
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
