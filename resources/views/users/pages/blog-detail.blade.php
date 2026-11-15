@extends('users.layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    {{-- Header --}}
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-brand-700">Home</a><span>/</span>
        <a href="{{ route('blog.index') }}" class="hover:text-brand-700">Blog</a><span>/</span>
        <span class="text-gray-900 dark:text-white truncate max-w-xs">{{ $post->title }}</span>
    </nav>
    @if($post->category)<span class="text-xs font-semibold text-brand-700 dark:text-brand-400 uppercase tracking-widest">{{ $post->category->name }}</span>@endif
    <h1 class="font-serif text-4xl font-bold text-gray-900 dark:text-white mt-2 leading-tight">{{ $post->title }}</h1>
    <div class="flex items-center gap-4 mt-4 text-sm text-gray-500">
        <span>{{ $post->published_at?->format('M j, Y') }}</span>
        <span>{{ $post->views_count }} views</span>
    </div>
    @if($post->cover_image)
    <div class="mt-8 rounded-2xl overflow-hidden aspect-[2/1] bg-cream-100 dark:bg-gray-800"><img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover"></div>
    @endif
    {{-- Content --}}
    <div class="mt-10 prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed">{!! $post->content !!}</div>
    {{-- Actions --}}
    <div class="mt-10 flex items-center gap-6 border-t border-cream-200 dark:border-gray-700 pt-6">
        @auth
        <button x-data="{ liked: {{ $liked ? 'true' : 'false' }}, count: {{ $post->likes->count() }} }"
            @click="fetch('{{ route('blog.like', $post->slug) }}', {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'}}).then(r=>r.json()).then(d=>{liked=d.liked;count=d.count})"
            :class="liked ? 'text-red-500' : 'text-gray-400'"
            class="flex items-center gap-2 font-semibold text-sm hover:text-red-500 transition">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <span x-text="count + ' likes'"></span>
        </button>
        @endauth
        {{-- Share --}}
        @php $blogUrl = route('blog.show', $post->slug); @endphp
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($blogUrl) }}" target="_blank" class="text-gray-400 hover:text-blue-600 transition"><svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></a>
        <a href="https://twitter.com/intent/tweet?url={{ urlencode($blogUrl) }}" target="_blank" class="text-gray-400 hover:text-sky-500 transition"><svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5 0-.28-.03-.56-.08-.83A7.72 7.72 0 0023 3z"/></svg></a>
        <a href="https://wa.me/?text={{ urlencode($post->title.' '.$blogUrl) }}" target="_blank" class="text-gray-400 hover:text-green-500 transition"><svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></a>
    </div>
    {{-- Comments --}}
    <div class="mt-12">
        <h2 class="font-serif text-2xl font-bold text-gray-900 dark:text-white mb-6">Comments ({{ $post->approvedComments->count() }})</h2>
        @auth
        <form action="{{ route('blog.comment', $post->slug) }}" method="POST" class="mb-8">@csrf
            <textarea name="body" rows="3" required placeholder="Write a comment…" class="w-full px-4 py-3 rounded-xl border border-cream-200 dark:border-gray-600 bg-cream-50 dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"></textarea>
            <button type="submit" class="mt-3 bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-brand-800 transition text-sm">Post Comment</button>
        </form>
        @else
        <p class="text-sm text-gray-500 mb-6"><a href="{{ route('login') }}" class="text-brand-700 font-semibold hover:underline">Sign in</a> to leave a comment.</p>
        @endauth
        <div class="space-y-4">
            @foreach($post->approvedComments as $comment)
            <div class="bg-cream-50 dark:bg-gray-800 rounded-xl p-4 border border-cream-200 dark:border-gray-700">
                <div class="flex items-center gap-3 mb-2">
                    <img src="{{ $comment->user->avatar_url }}" class="w-8 h-8 rounded-full object-cover">
                    <div><p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $comment->user->name }}</p><p class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</p></div>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $comment->body }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

