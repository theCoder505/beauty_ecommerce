@extends('users.layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    {{-- Header --}}
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-teal-700 dark:hover:text-teal-400">Home</a><span>/</span>
        <a href="{{ route('blog.index') }}" class="hover:text-teal-700 dark:hover:text-teal-400">Blog</a><span>/</span>
        <span class="text-gray-900 dark:text-white truncate max-w-xs">{{ $post->title }}</span>
    </nav>
    @if($post->category)
        <span class="text-xs font-semibold text-teal-700 dark:text-teal-400 uppercase tracking-widest">{{ $post->category->name }}</span>
    @endif
    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mt-2 leading-tight">{{ $post->title }}</h1>
    <div class="flex items-center gap-4 mt-4 text-sm text-gray-500">
        <span>{{ $post->published_at?->format('M j, Y') }}</span>
        <span>{{ $post->views_count }} views</span>
    </div>

    @if($post->cover_image)
    <div class="mt-8 rounded-2xl overflow-hidden aspect-2/1 bg-cream-100 dark:bg-gray-800 shadow-xs">
        <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
    </div>
    @endif

    {{-- Content --}}
    <div class="mt-10 rich-content prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed font-sans">
        {!! $post->content !!}
    </div>

    {{-- Actions & Post Likes --}}
    <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-cream-200 dark:border-gray-800 pt-6">
        <div class="flex items-center gap-4">
            @auth
            <button id="post-like-btn"
                x-data="{ liked: {{ $liked ? 'true' : 'false' }}, count: {{ $post->likes->count() }} }"
                @click="fetch('{{ route('blog.like', $post->slug) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(d => { liked = d.liked; count = d.count })"
                :class="liked ? 'text-rose-500 bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900' : 'text-gray-600 dark:text-gray-400 bg-cream-50 dark:bg-gray-800 border-cream-200 dark:border-gray-700'"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-full border font-medium text-sm hover:border-rose-400 transition shadow-xs">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span x-text="count + ' ' + (count === 1 ? 'Like' : 'Likes')"></span>
            </button>
            @else
            <button type="button"
                onclick="promptGuestLogin('Sign in to like this post.')"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-cream-200 dark:border-gray-700 bg-cream-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-rose-500 transition shadow-xs text-sm">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span>{{ $post->likes->count() }} Likes</span>
            </button>
            @endauth

            <a href="#comments-section" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-teal-700 dark:hover:text-teal-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span id="post-total-comments-label">{{ $totalCommentsCount ?? $post->approvedComments->count() }} Comments</span>
            </a>
        </div>

        {{-- Share --}}
        @php $blogUrl = route('blog.show', $post->slug); @endphp
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400 font-medium">Share:</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($blogUrl) }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-blue-600 transition" title="Share on Facebook">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
            </a>
            <a href="https://twitter.com/intent/tweet?url={{ urlencode($blogUrl) }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-sky-500 transition" title="Share on Twitter/X">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5 0-.28-.03-.56-.08-.83A7.72 7.72 0 0023 3z"/></svg>
            </a>
            <a href="https://wa.me/?text={{ urlencode($post->title.' '.$blogUrl) }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-emerald-500 transition" title="Share on WhatsApp">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </a>
        </div>
    </div>

    {{-- Comments Section (Full-screen height viewport & right-oriented send input) --}}
    <div id="comments-section" class="mt-10">
        {{-- Single Viewport Card Container (Full-screen height & pinned bottom input) --}}
        <div class="rounded-2xl border border-cream-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-xs overflow-hidden flex flex-col h-screen max-h-[calc(100vh-100px)]">
            {{-- Sleek Minimal Header --}}
            <div class="bg-cream-50/70 dark:bg-gray-900/90 px-4 py-2.5 border-b border-cream-200 dark:border-gray-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-700 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <h3 class="font-serif font-bold text-sm sm:text-base text-gray-900 dark:text-white">Comments</h3>
                    <span id="comments-count-pill" class="px-1.5 py-0.2 text-[11px] font-semibold rounded-full bg-teal-100 dark:bg-teal-950 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                        {{ $totalCommentsCount ?? $post->approvedComments->count() }}
                    </span>
                </div>
                <div class="text-[10px] text-gray-400 font-medium">
                    Discussion
                </div>
            </div>

            {{-- Scrollable Viewport --}}
            <div id="comments-viewport" class="flex-1 overflow-y-auto px-3.5 sm:px-5 py-4 space-y-4 scroll-smooth divide-y-0">
                {{-- Empty Placeholder --}}
                <div id="no-comments-placeholder" class="{{ ($post->approvedTopComments->isEmpty()) ? 'flex' : 'hidden' }} flex-col items-center justify-center h-full text-center py-6 text-gray-400 dark:text-gray-500">
                    <svg class="w-9 h-9 text-gray-300 dark:text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">No comments yet</p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Be the first to share your thoughts!</p>
                </div>

                {{-- All Top-Level Comments --}}
                <div id="comments-list" class="space-y-4 sm:space-y-5">
                    @foreach($post->approvedTopComments as $comment)
                        @include('users.pages.partials.comment-item', ['comment' => $comment, 'isReply' => false, 'post' => $post])
                    @endforeach
                </div>
            </div>

            {{-- Fixed / Pinned Bottom Input Bar (Send Icon Right-Oriented) --}}
            <div class="border-t border-cream-200 dark:border-gray-800 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm p-2 sm:p-2.5 shrink-0 z-10">
                @auth
                    {{-- Replying To Notification Banner --}}
                    <div id="reply-banner" class="hidden items-center justify-between text-[11px] bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 px-2.5 py-1 rounded-lg mb-1.5 border border-teal-200 dark:border-teal-900">
                        <div class="flex items-center gap-1.5 truncate">
                            <svg class="w-3 h-3 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            <span class="truncate">Replying to <strong id="reply-to-name" class="font-semibold text-teal-950 dark:text-teal-200"></strong></span>
                        </div>
                        <button type="button" onclick="cancelReplyTarget()" class="text-[11px] font-semibold text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 transition ml-2">
                            ✕ Cancel
                        </button>
                    </div>

                    {{-- Comment / Reply Form with Right-Oriented Send Button --}}
                    <form id="comment-form" onsubmit="handleCommentSubmit(event)" class="flex items-center gap-2">
                        <input type="hidden" id="comment-parent-id" name="parent_id" value="">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover shrink-0 ring-1 ring-cream-300 dark:ring-gray-700">
                        
                        <div class="flex-1 relative flex items-center">
                            <input type="text"
                                   id="comment-input"
                                   name="body"
                                   placeholder="Write a comment or reply..."
                                   autocomplete="off"
                                   required
                                   maxlength="1000"
                                   class="w-full bg-cream-100/90 dark:bg-gray-800 border-0 rounded-full pl-3.5 pr-9 sm:pr-10 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-teal-600 focus:bg-white dark:focus:bg-gray-900 transition">

                            {{-- Right-Oriented Send Icon Button inside input --}}
                            <button type="submit"
                                    id="comment-submit-btn"
                                    class="absolute right-1 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-teal-700 hover:bg-teal-800 active:scale-90 text-white flex items-center justify-center shrink-0 transition shadow-xs disabled:opacity-40 disabled:cursor-not-allowed"
                                    title="Send comment">
                                <svg id="comment-submit-icon" class="w-3 h-3 sm:w-3.5 sm:h-3.5 translate-x-0.5 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                <svg id="comment-spinner" class="w-3 h-3 sm:w-3.5 sm:h-3.5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                @else
                    {{-- Guest Notice --}}
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2 bg-cream-100/80 dark:bg-gray-800/80 rounded-xl px-3 py-2 border border-cream-200 dark:border-gray-700">
                        <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300 text-center sm:text-left">
                            <svg class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Only signed-in members can like or comment. Join the discussion!</span>
                        </div>
                        <a href="{{ route('login') }}" class="w-full sm:w-auto text-center text-xs font-semibold px-3 py-1.5 rounded-lg bg-teal-700 hover:bg-teal-800 text-white transition shadow-xs">
                            Sign In
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    {{-- Related Posts --}}
    @if(isset($related) && $related->isNotEmpty())
    <div class="mt-14 pt-8 border-t border-cream-200 dark:border-gray-800">
        <h3 class="font-serif text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-5">Related Articles</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            @foreach($related as $relPost)
            <a href="{{ route('blog.show', $relPost->slug) }}" class="group block rounded-2xl overflow-hidden bg-white dark:bg-gray-900 border border-cream-200 dark:border-gray-800 hover:border-teal-500/50 transition shadow-xs">
                @if($relPost->cover_image)
                <div class="aspect-16/10 overflow-hidden bg-cream-100 dark:bg-gray-800">
                    <img src="{{ $relPost->cover_image_url }}" alt="{{ $relPost->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                </div>
                @endif
                <div class="p-3.5">
                    <p class="text-xs text-gray-400">{{ $relPost->published_at?->format('M j, Y') }}</p>
                    <h4 class="font-serif font-semibold text-xs sm:text-sm text-gray-900 dark:text-white group-hover:text-teal-700 dark:group-hover:text-teal-400 transition mt-1 line-clamp-2">{{ $relPost->title }}</h4>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Scripts for Comments & Facebook-style Interactions --}}
@push('scripts')
<script>
    const IS_AUTHENTICATED = @json(auth()->check());
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    const COMMENT_POST_URL = @json(route('blog.comment', $post->slug));
    const LOGIN_URL = @json(route('login'));

    // Guest prompt helper
    window.promptGuestLogin = function(customMessage) {
        if (window.Swal) {
            Swal.fire({
                title: 'Sign In Required',
                text: customMessage || 'Please sign in to like or join the discussion.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Sign In',
                confirmButtonColor: '#0f766e',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = LOGIN_URL;
                }
            });
        } else {
            if (confirm(customMessage || 'Please sign in to continue.')) {
                window.location.href = LOGIN_URL;
            }
        }
    };

    // Set reply target
    window.setReplyTarget = function(parentId, userName) {
        if (!IS_AUTHENTICATED) {
            promptGuestLogin('Sign in to reply to comments.');
            return;
        }

        const parentInput = document.getElementById('comment-parent-id');
        const replyBanner = document.getElementById('reply-banner');
        const replyToName = document.getElementById('reply-to-name');
        const commentInput = document.getElementById('comment-input');

        if (parentInput) parentInput.value = parentId;
        if (replyToName) replyToName.textContent = userName;
        if (replyBanner) replyBanner.classList.remove('hidden');
        if (replyBanner) replyBanner.classList.add('flex');

        if (commentInput) {
            commentInput.focus();
            commentInput.placeholder = `Replying to ${userName}...`;
        }
    };

    // Cancel reply target
    window.cancelReplyTarget = function() {
        const parentInput = document.getElementById('comment-parent-id');
        const replyBanner = document.getElementById('reply-banner');
        const commentInput = document.getElementById('comment-input');

        if (parentInput) parentInput.value = '';
        if (replyBanner) {
            replyBanner.classList.add('hidden');
            replyBanner.classList.remove('flex');
        }
        if (commentInput) {
            commentInput.placeholder = 'Write a comment or reply...';
        }
    };

    // Handle AJAX comment submission
    window.handleCommentSubmit = async function(e) {
        e.preventDefault();
        if (!IS_AUTHENTICATED) {
            promptGuestLogin();
            return;
        }

        const commentInput = document.getElementById('comment-input');
        const parentInput = document.getElementById('comment-parent-id');
        const submitBtn = document.getElementById('comment-submit-btn');
        const submitIcon = document.getElementById('comment-submit-icon');
        const spinner = document.getElementById('comment-spinner');

        const body = commentInput.value.trim();
        const parentId = parentInput.value ? parentInput.value : null;

        if (!body) return;

        // Loading state
        submitBtn.disabled = true;
        if (submitIcon) submitIcon.classList.add('hidden');
        if (spinner) spinner.classList.remove('hidden');

        try {
            const response = await fetch(COMMENT_POST_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    body: body,
                    parent_id: parentId
                })
            });

            if (!response.ok) {
                if (response.status === 401) {
                    promptGuestLogin();
                    return;
                }
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Failed to post comment.');
            }

            const data = await response.json();
            if (data.success) {
                // Remove empty placeholder
                const placeholder = document.getElementById('no-comments-placeholder');
                if (placeholder) {
                    placeholder.classList.add('hidden');
                    placeholder.classList.remove('flex');
                }

                // Temporary container to parse HTML string
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html.trim();
                const newCommentElement = tempDiv.firstElementChild;

                if (data.parent_id) {
                    // It's a reply: find the parent replies container
                    let repliesContainer = document.getElementById(`replies-container-${data.parent_id}`);
                    if (!repliesContainer) {
                        const parentEl = document.getElementById(`comment-${data.parent_id}`);
                        if (parentEl) {
                            const bodyWrapper = parentEl.querySelector('.flex-1');
                            repliesContainer = document.createElement('div');
                            repliesContainer.id = `replies-container-${data.parent_id}`;
                            repliesContainer.className = 'replies-wrapper mt-3 pl-3 sm:pl-4 border-l-2 border-cream-200 dark:border-gray-800 ml-2.5 sm:ml-3 space-y-3';
                            bodyWrapper.appendChild(repliesContainer);
                        }
                    }

                    if (repliesContainer) {
                        repliesContainer.appendChild(newCommentElement);
                    } else {
                        document.getElementById('comments-list')?.appendChild(newCommentElement);
                    }

                    setTimeout(() => {
                        newCommentElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        newCommentElement.classList.add('bg-teal-50/50', 'dark:bg-teal-950/20', 'rounded-xl', 'transition');
                        setTimeout(() => {
                            newCommentElement.classList.remove('bg-teal-50/50', 'dark:bg-teal-950/20');
                        }, 2000);
                    }, 100);
                } else {
                    // Top level comment: PREPEND because latest comments are shown first!
                    const commentsList = document.getElementById('comments-list');
                    if (commentsList) {
                        commentsList.prepend(newCommentElement);
                    }

                    // Scroll viewport to top to show the newest comment immediately
                    const viewport = document.getElementById('comments-viewport');
                    if (viewport) {
                        viewport.scrollTo({ top: 0, behavior: 'smooth' });
                    }

                    setTimeout(() => {
                        newCommentElement.classList.add('bg-teal-50/50', 'dark:bg-teal-950/20', 'rounded-xl', 'transition');
                        setTimeout(() => {
                            newCommentElement.classList.remove('bg-teal-50/50', 'dark:bg-teal-950/20');
                        }, 2000);
                    }, 100);
                }

                // Update comments count indicators
                if (data.total_count !== undefined) {
                    updateCommentsCounter(data.total_count);
                }

                // Reset inputs & reply state
                commentInput.value = '';
                cancelReplyTarget();

                if (window.toast) {
                    window.toast('Comment posted successfully!', 'success');
                }
            }
        } catch (err) {
            console.error(err);
            if (window.toast) {
                window.toast(err.message || 'Could not post comment.', 'error');
            } else {
                alert(err.message || 'Could not post comment.');
            }
        } finally {
            submitBtn.disabled = false;
            if (submitIcon) submitIcon.classList.remove('hidden');
            if (spinner) spinner.classList.add('hidden');
        }
    };

    // Helper to update comment like UI state
    function applyCommentLikeUI(btnElement, liked, count) {
        if (!btnElement) return;

        const counter = btnElement.querySelector('.like-counter');
        const outlineIcon = btnElement.querySelector('.like-icon-outline');
        const solidIcon = btnElement.querySelector('.like-icon-solid');

        if (liked) {
            btnElement.classList.add('is-liked', 'text-teal-600', 'dark:text-teal-400');
            btnElement.classList.remove('text-gray-400', 'text-gray-500', 'dark:text-gray-400', 'dark:text-gray-500');
            if (outlineIcon) outlineIcon.classList.add('hidden');
            if (solidIcon) {
                solidIcon.classList.remove('hidden');
                solidIcon.classList.add('scale-125');
                setTimeout(() => solidIcon.classList.remove('scale-125'), 180);
            }
        } else {
            btnElement.classList.remove('is-liked', 'text-teal-600', 'dark:text-teal-400');
            btnElement.classList.add('text-gray-400', 'dark:text-gray-500');
            if (outlineIcon) outlineIcon.classList.remove('hidden');
            if (solidIcon) solidIcon.classList.add('hidden');
        }

        if (counter) {
            counter.textContent = count > 0 ? count : '';
            if (count > 0) {
                counter.classList.remove('hidden');
            } else {
                counter.classList.add('hidden');
            }
        }
    }

    // Handle comment like toggle via AJAX
    window.handleCommentLike = async function(commentId, btnElement) {
        if (!IS_AUTHENTICATED) {
            promptGuestLogin('Sign in to like comments.');
            return;
        }

        if (btnElement._isLiking) return;
        btnElement._isLiking = true;

        const counter = btnElement.querySelector('.like-counter');
        const currentlyLiked = btnElement.classList.contains('is-liked');
        let currentCount = parseInt(counter?.textContent || '0', 10);
        if (isNaN(currentCount)) currentCount = 0;

        // Instant optimistic toggle
        const nextLiked = !currentlyLiked;
        const nextCount = nextLiked ? (currentCount + 1) : Math.max(0, currentCount - 1);
        applyCommentLikeUI(btnElement, nextLiked, nextCount);

        try {
            const response = await fetch(`/blog/comments/${commentId}/like`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            });

            if (!response.ok) {
                if (response.status === 401) {
                    applyCommentLikeUI(btnElement, currentlyLiked, currentCount);
                    promptGuestLogin();
                    return;
                }
                throw new Error('Failed to update like.');
            }

            const data = await response.json();
            if (data.success) {
                // Synchronize with server response
                applyCommentLikeUI(btnElement, data.liked, data.count);
            } else {
                applyCommentLikeUI(btnElement, currentlyLiked, currentCount);
            }
        } catch (err) {
            console.error(err);
            // Revert optimistic changes on error
            applyCommentLikeUI(btnElement, currentlyLiked, currentCount);
            if (window.toast) {
                window.toast('Could not update like. Please try again.', 'error');
            }
        } finally {
            btnElement._isLiking = false;
        }
    };

    // Handle user comment deletion
    window.handleDeleteComment = async function(commentId) {
        if (!IS_AUTHENTICATED) return;

        const performDelete = async () => {
            if (window.Swal) {
                const res = await Swal.fire({
                    title: 'Delete Comment?',
                    text: 'Are you sure you want to delete this comment? All nested replies will also be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                });
                return res.isConfirmed;
            }
            return confirm('Are you sure you want to delete this comment?');
        };

        const confirmed = await performDelete();
        if (!confirmed) return;

        try {
            const response = await fetch(`/blog/comments/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            });

            if (!response.ok) {
                throw new Error('Failed to delete comment.');
            }

            const data = await response.json();
            if (data.success) {
                const el = document.getElementById(`comment-${commentId}`);
                if (el) {
                    el.style.transition = 'all 0.25s ease-out';
                    el.style.opacity = '0';
                    el.style.transform = 'scale(0.96)';
                    setTimeout(() => {
                        el.remove();
                        const list = document.getElementById('comments-list');
                        if (list && list.children.length === 0) {
                            const placeholder = document.getElementById('no-comments-placeholder');
                            if (placeholder) {
                                placeholder.classList.remove('hidden');
                                placeholder.classList.add('flex');
                            }
                        }
                    }, 250);
                }

                if (data.total_count !== undefined) {
                    updateCommentsCounter(data.total_count);
                }

                if (window.toast) {
                    window.toast('Comment deleted successfully.', 'success');
                }
            }
        } catch (err) {
            console.error(err);
            if (window.toast) {
                window.toast(err.message || 'Could not delete comment.', 'error');
            } else {
                alert(err.message || 'Could not delete comment.');
            }
        }
    };

    // Helper to update counter labels
    function updateCommentsCounter(count) {
        const pill = document.getElementById('comments-count-pill');
        if (pill) pill.textContent = count;

        const label = document.getElementById('post-total-comments-label');
        if (label) label.textContent = `${count} Comments`;
    }
</script>
@endpush
@endsection
