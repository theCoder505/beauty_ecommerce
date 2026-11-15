@extends('users.layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col lg:flex-row gap-8">
        {{-- Sidebar --}}
        <aside class="lg:w-64 shrink-0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-cream-200 dark:border-gray-700">
                <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-4">Blog Categories</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('blog.index') }}" class="text-sm {{ !request('category') ? 'text-brand-700 font-semibold' : 'text-gray-600 hover:text-brand-700' }} transition">All Posts</a></li>
                    @foreach($categories as $cat)
                    <li><a href="{{ route('blog.index', ['category' => $cat->slug]) }}" class="text-sm {{ request('category') === $cat->slug ? 'text-brand-700 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-brand-700' }} transition flex justify-between">{{ $cat->name }} <span class="text-xs text-gray-400">({{ $cat->posts_count }})</span></a></li>
                    @endforeach
                </ul>
            </div>
        </aside>
        {{-- Posts --}}
        <main class="flex-1 min-w-0">
            <h1 class="font-serif text-3xl font-bold text-gray-900 dark:text-white mb-8">Beauty Blog</h1>
            @if($posts->isEmpty())
            <p class="text-gray-500">No blog posts yet.</p>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posts as $post)
                <article class="group bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm border border-cream-200 dark:border-gray-700">
                    <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/10] overflow-hidden bg-cream-100 dark:bg-gray-700">
                        <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    </a>
                    <div class="p-5">
                        @if($post->category)<span class="text-xs font-semibold text-brand-700 dark:text-brand-400 uppercase tracking-widest">{{ $post->category->name }}</span>@endif
                        <h2 class="font-serif text-lg font-bold text-gray-900 dark:text-white mt-2 leading-tight group-hover:text-brand-700 transition line-clamp-2"><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
                        <p class="text-gray-500 text-sm mt-2 line-clamp-2">{{ $post->excerpt }}</p>
                        <div class="flex items-center gap-4 mt-4 text-xs text-gray-400">
                            <span>{{ $post->published_at?->format('M j, Y') }}</span>
                            <span>{{ $post->approved_comments_count ?? 0 }} comments</span>
                            <span>{{ $post->likes_count ?? 0 }} likes</span>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            <div class="mt-10">{{ $posts->withQueryString()->links() }}</div>
            @endif
        </main>
    </div>
</div>
@endsection

